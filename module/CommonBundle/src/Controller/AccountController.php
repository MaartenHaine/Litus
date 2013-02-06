<?php
/**
 * Litus is a project by a group of students from the K.U.Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Niels Avonds <niels.avonds@litus.cc>
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 *
 * @license http://litus.cc/LICENSE
 */

namespace CommonBundle\Controller;

use CommonBundle\Component\FlashMessenger\FlashMessage,
    CommonBundle\Entity\General\Address,
    CommonBundle\Entity\Users\Credential,
    CommonBundle\Entity\Users\Statuses\Organization as OrganizationStatus,
    CommonBundle\Entity\Users\Statuses\University as UniversityStatus,
    CommonBundle\Form\Account\Activate as ActivateForm,
    CommonBundle\Form\Account\FileServer\CreateAccount as CreateAccountForm,
    CommonBundle\Form\Account\FileServer\ChangePassword as ChangePasswordForm,
    CudiBundle\Entity\Sales\Booking,
    DateTime,
    Imagick,
    SecretaryBundle\Entity\Organization\MetaData,
    SecretaryBundle\Entity\Registration,
    SecretaryBundle\Entity\Syllabus\StudyEnrollment,
    SecretaryBundle\Entity\Syllabus\SubjectEnrollment,
    SecretaryBundle\Form\Registration\Edit as EditForm,
    SecretaryBundle\Form\Registration\Subject\Add as SubjectForm,
    Zend\File\Transfer\Adapter\Http as FileUpload,
    Zend\Http\Headers,
    Zend\Ldap\Attribute,
    Zend\Ldap\Ldap,
    Zend\Validator\File\Size as SizeValidator,
    Zend\Validator\File\IsImage as ImageValidator,
    Zend\View\Model\ViewModel;

/**
 * Handles account page.
 *
 * @author Kristof Mariën <kristof.marien@litus.cc>
 */
class AccountController extends \CommonBundle\Component\Controller\ActionController\SiteController
{
    public function indexAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'ERROR',
                    'Please login first!'
                )
            );

            $this->redirect()->toRoute(
                'index'
            );

            return new ViewModel();
        }

        $metaData = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Organization\MetaData')
            ->findOneByAcademicAndAcademicYear($this->getAuthentication()->getPersonObject(), $this->getCurrentAcademicYear());

        $studies = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Syllabus\StudyEnrollment')
            ->findAllByAcademicAndAcademicYear($this->getAuthentication()->getPersonObject(), $this->getCurrentAcademicYear());

        $mappings = array();
        foreach($studies as $enrollment) {
            $mappings[] = array(
                'enrollment' => $enrollment,
                'subjects' => $this->getEntityManager()
                    ->getRepository('SyllabusBundle\Entity\StudySubjectMap')
                    ->findAllByStudyAndAcademicYear($enrollment->getStudy(), $this->getCurrentAcademicYear())
            );
        }

        $subjects = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Syllabus\SubjectEnrollment')
            ->findAllByAcademicAndAcademicYear($this->getAuthentication()->getPersonObject(), $this->getCurrentAcademicYear());

        $subjectIds = array();
        foreach($subjects as $enrollment)
            $subjectIds[] = $enrollment->getSubject()->getId();

        return new ViewModel(
            array(
                'academicYear' => $this->getCurrentAcademicYear(),
                'metaData' => $metaData,
                'studies' => $mappings,
                'subjects' => $subjectIds,
            )
        );
    }

    public function editAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'ERROR',
                    'Please login first!'
                )
            );

            $this->redirect()->toRoute(
                'index'
            );

            return new ViewModel();
        }

        $academic = $this->getAuthentication()->getPersonObject();

        $metaData = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Organization\MetaData')
            ->findOneByAcademicAndAcademicYear($academic, $this->getCurrentAcademicYear());

        try {
            $terms_and_conditions = $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('secretary.terms_and_conditions_' . $this->getLanguage()->getAbbrev());
        } catch(\Exception $e) {
            $terms_and_conditions = $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('secretary.terms_and_conditions_' . \Locale::getDefault());
        }

        $form = new EditForm(
            $academic,
            $this->getCurrentAcademicYear(),
            $metaData,
            $this->getCache(),
            $this->getEntityManager(),
            $this->getParam('identification')
        );

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();

            $formData['university_identification'] = $this->getParam('identification');
            if ($metaData && $metaData->becomeMember())
                $formData['become_member'] = true;
            else
                $formData['become_member'] = isset($formData['become_member']) ? $formData['become_member'] : false;
            $form->setData($formData);

            if ($form->isValid()) {
                $formData = $form->getFormData($formData);

                $universityEmail = preg_replace('/[^a-z0-9\.@]/i', '', iconv("UTF-8", "US-ASCII//TRANSLIT", $formData['university_email'])) . '@student.kuleuven.be';

                $academic->setFirstName($formData['first_name'])
                    ->setLastName($formData['last_name'])
                    ->setEmail($formData['primary_email'] ? $formData['personal_email'] : $universityEmail)
                    ->setPhoneNumber($formData['phone_number'])
                    ->setSex($formData['sex'])
                    ->setBirthday(DateTime::createFromFormat('d/m/Y H:i', $formData['birthday'] . ' 00:00'))
                    ->setPersonalEmail($formData['personal_email'])
                    ->setUniversityEmail($universityEmail);

                if ($formData['primary_address_address_city'] != 'other') {
                    $primaryCity = $this->getEntityManager()
                        ->getRepository('CommonBundle\Entity\General\Address\City')
                        ->findOneById($formData['primary_address_address_city']);
                    $primaryPostal = $primaryCity->getPostal();
                    $primaryCity = $primaryCity->getName();
                    $primaryStreet = $this->getEntityManager()
                        ->getRepository('CommonBundle\Entity\General\Address\Street')
                        ->findOneById($formData['primary_address_address_street_' . $formData['primary_address_address_city']])
                        ->getName();
                } else {
                    $primaryCity = $formData['primary_address_address_city_other'];
                    $primaryStreet = $formData['primary_address_address_street_other'];
                    $primaryPostal = $formData['primary_address_address_postal_other'];
                }

                if (null !== $academic->getPrimaryAddress()) {
                    $academic->getPrimaryAddress()
                        ->setStreet($primaryStreet)
                        ->setNumber($formData['primary_address_address_number'])
                        ->setMailbox($formData['primary_address_address_mailbox'])
                        ->setPostal($primaryPostal)
                        ->setCity($primaryCity)
                        ->setCountry('BE');
                } else {
                    $academic->setPrimaryAddress(
                        new Address(
                            $primaryStreet,
                            $formData['primary_address_address_number'],
                            $formData['primary_address_address_mailbox'],
                            $primaryPostal,
                            $primaryCity,
                            'BE'
                        )
                    );
                }

                if (null !== $academic->getSecondaryAddress()) {
                    $academic->getSecondaryAddress()
                        ->setStreet($formData['secondary_address_address_street'])
                        ->setNumber($formData['secondary_address_address_number'])
                        ->setMailbox($formData['secondary_address_address_mailbox'])
                        ->setPostal($formData['secondary_address_address_postal'])
                        ->setCity($formData['secondary_address_address_city'])
                        ->setCountry($formData['secondary_address_address_country']);
                } else {
                    $academic->setSecondaryAddress(
                        new Address(
                            $formData['secondary_address_address_street'],
                            $formData['secondary_address_address_number'],
                            $formData['primary_address_address_mailbox'],
                            $formData['secondary_address_address_postal'],
                            $formData['secondary_address_address_city'],
                            $formData['secondary_address_address_country']
                        )
                    );
                }

                if ($academic->canHaveUniversityStatus($this->getCurrentAcademicYear())) {
                    $status = new UniversityStatus(
                        $academic,
                        'student',
                        $this->getCurrentAcademicYear()
                    );
                    $academic->addUniversityStatus($status);
                }

                $filePath = $this->getEntityManager()
                    ->getRepository('CommonBundle\Entity\General\Config')
                    ->getConfigValue('common.profile_path');

                $upload = new FileUpload();
                $upload->addValidator(new SizeValidator(array('max' => '3MB')));
                $upload->addValidator(new ImageValidator());

                if ($upload->isValid()) {
                    $upload->receive();

                    $image = new Imagick($upload->getFileName());
                    unlink($upload->getFileName());
                    $image->cropThumbnailImage(320, 240);

                    if ($academic->getPhotoPath() != '' || $academic->getPhotoPath() !== null) {
                        $fileName = $academic->getPhotoPath();
                    } else {
                        $fileName = '';
                        do{
                            $fileName = sha1(uniqid());
                        } while (file_exists($filePath . '/' . $fileName));
                    }
                    $image->writeImage($filePath . '/' . $fileName);
                    $academic->setPhotoPath($fileName);
                }

                $tshirts = unserialize(
                    $this->getEntityManager()
                        ->getRepository('CommonBundle\Entity\General\Config')
                        ->getConfigValue('cudi.tshirt_article')
                );

                if (null !== $metaData) {
                    if ($metaData->getTshirtSize() !== null) {
                        $booking = $this->getEntityManager()
                            ->getRepository('CudiBundle\Entity\Sales\Booking')
                            ->findOneAssignedByArticleAndPerson(
                                $this->getEntityManager()
                                    ->getRepository('CudiBundle\Entity\Sales\Article')
                                    ->findOneById($tshirts[$metaData->getTshirtSize()]),
                                $academic
                            );

                        if ($booking !== null)
                            $this->getEntityManager()->remove($booking);
                    }

                    $becomeMember = $metaData->becomeMember() ? true : $formData['become_member'];

                    if ($becomeMember) {
                        $metaData->setBecomeMember($becomeMember)
                            ->setReceiveIrReeelAtCudi($formData['irreeel'])
                            ->setBakskeByMail($formData['bakske'])
                            ->setTshirtSize($formData['tshirt_size']);
                    } else {
                        $metaData->setBakskeByMail($formData['bakske']);
                    }
                } else {
                    if ($formData['become_member']) {
                        $metaData = new MetaData(
                            $academic,
                            $this->getCurrentAcademicYear(),
                            $formData['become_member'],
                            $formData['irreeel'],
                            $formData['bakske'],
                            $formData['tshirt_size']
                        );
                    } else {
                        $metaData = new MetaData(
                            $academic,
                            $this->getCurrentAcademicYear(),
                            $formData['become_member'],
                            false,
                            $formData['bakske'],
                            null
                        );
                    }

                    $this->getEntityManager()->persist($metaData);
                }

                $membershipArticle = $this->getEntityManager()
                    ->getRepository('CudiBundle\Entity\Sales\Article')
                    ->findOneById($this->getEntityManager()
                        ->getRepository('CommonBundle\Entity\General\Config')
                        ->getConfigValue('secretary.membership_article')
                    );

                if ($metaData->becomeMember()) {
                    $booking = $this->getEntityManager()
                        ->getRepository('CudiBundle\Entity\Sales\Booking')
                        ->findOneSoldOrAssignedOrBookedByArticleAndPerson(
                            $membershipArticle,
                            $academic
                        );
                    if (null === $booking) {
                        $booking = new Booking(
                            $this->getEntityManager(),
                            $academic,
                            $membershipArticle,
                            'assigned',
                            1,
                            true
                        );

                        $this->getEntityManager()->persist($booking);
                    }

                    $hasShirt = false;
                    foreach ($tshirts as $tshirt) {
                        $booking = $this->getEntityManager()
                            ->getRepository('CudiBundle\Entity\Sales\Booking')
                            ->findOneSoldOrAssignedOrBookedByArticleAndPerson(
                                $this->getEntityManager()
                                    ->getRepository('CudiBundle\Entity\Sales\Article')
                                    ->findOneById($tshirt),
                                $academic
                            );

                        if (null !== $booking) {
                            $hasShirt = true;
                            break;
                        }
                    }

                    $enableAssignment = $this->getEntityManager()
                        ->getRepository('CommonBundle\Entity\General\Config')
                        ->getConfigValue('cudi.enable_automatic_assignment');
                    $currentPeriod = $this->getEntityManager()
                        ->getRepository('CudiBundle\Entity\Stock\Period')
                        ->findOneActive();
                    $currentPeriod->setEntityManager($this->getEntityManager());

                    // Only make a new booking if no tshirt has been sold before
                    if (!$hasShirt) {
                        $booking = new Booking(
                            $this->getEntityManager(),
                            $academic,
                            $this->getEntityManager()
                                ->getRepository('CudiBundle\Entity\Sales\Article')
                                ->findOneById($tshirts[$formData['tshirt_size']]),
                            'booked',
                            1,
                            true
                        );

                        $this->getEntityManager()->persist($booking);

                        if ($enableAssignment == '1') {
                            $available = $booking->getArticle()->getStockValue() - $currentPeriod->getNbAssigned($booking->getArticle());
                            if ($available > 0) {
                                if ($available >= $booking->getNumber()) {
                                    $booking->setStatus('assigned', $this->getEntityManager());
                                }
                            }
                        }
                    }

                    // Book the other articles that should be booked on registration
                    $registrationArticles = unserialize(
                        $this->getEntityManager()
                            ->getRepository('CommonBundle\Entity\General\Config')
                            ->getConfigValue('cudi.registration_articles')
                    );

                    foreach ($registrationArticles as $registrationArticle) {
                        $booking = $this->getEntityManager()
                            ->getRepository('CudiBundle\Entity\Sales\Booking')
                            ->findOneSoldOrAssignedOrBookedByArticleAndPerson(
                                $this->getEntityManager()
                                    ->getRepository('CudiBundle\Entity\Sales\Article')
                                    ->findOneById($registrationArticle),
                                $academic
                            );

                        // Already got this article, continue
                        if (null !== $booking)
                            continue;

                        $booking = new Booking(
                            $this->getEntityManager(),
                            $academic,
                            $this->getEntityManager()
                                ->getRepository('CudiBundle\Entity\Sales\Article')
                                ->findOneById($registrationArticle),
                            'booked',
                            1,
                            true
                        );
                        $this->getEntityManager()->persist($booking);

                        if ($enableAssignment == '1') {
                            $available = $booking->getArticle()->getStockValue() - $currentPeriod->getNbAssigned($booking->getArticle());
                            if ($available > 0) {
                                if ($available >= $booking->getNumber()) {
                                    $booking->setStatus('assigned', $this->getEntityManager());
                                }
                            }
                        }
                    }
                } else {
                    $booking = $this->getEntityManager()
                        ->getRepository('CudiBundle\Entity\Sales\Booking')
                        ->findOneSoldOrAssignedOrBookedByArticleAndPerson(
                            $membershipArticle,
                            $academic
                        );

                    if (null !== $booking)
                        $this->getEntityManager()->remove($booking);
                }

                $academic->activate(
                    $this->getEntityManager(),
                    $this->getMailTransport()
                );

                $registration = $this->getEntityManager()
                    ->getRepository('SecretaryBundle\Entity\Registration')
                    ->findOneByAcademicAndAcademicYear($academic, $this->getCurrentAcademicYear());
                if (null === $registration) {
                    $registration = new Registration(
                        $academic,
                        $this->getCurrentAcademicYear()
                    );
                    $this->getEntityManager()->persist($registration);
                }

                $this->getEntityManager()->flush();

                $this->flashMessenger()->addMessage(
                    new FlashMessage(
                        FlashMessage::SUCCESS,
                        'SUCCESS',
                        'Your data was succesfully updated!'
                    )
                );

                $this->_doRedirect();

                return new ViewModel();
            }
        }

        return new ViewModel(
            array(
                'form' => $form,
                'terms_and_conditions' => $terms_and_conditions,
            )
        );
    }

    public function studiesAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'ERROR',
                    'Please login first!'
                )
            );

            $this->redirect()->toRoute(
                'index'
            );

            return new ViewModel();
        }

        $studies = $this->getEntityManager()
            ->getRepository('SyllabusBundle\Entity\Study')
            ->findAllParentsByAcademicYear($this->getCurrentAcademicYear());

        $enrollments = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Syllabus\StudyEnrollment')
            ->findAllByAcademicAndAcademicYear($this->getAuthentication()->getPersonObject(), $this->getCurrentAcademicYear());

        $studyIds = array();
        foreach($enrollments as $enrollment)
            $studyIds[] = $enrollment->getStudy()->getId();

        return new ViewModel(
            array(
                'studies' => $studies,
                'enrollments' => $studyIds,
            )
        );
    }

    public function saveStudiesAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'ERROR',
                    'Please login first!'
                )
            );

            $this->redirect()->toRoute(
                'index'
            );

            return new ViewModel();
        }

        $this->initAjax();

        $data = $this->getRequest()->getPost();
        $academic = $this->getAuthentication()->getPersonObject();

        $enrollments = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Syllabus\StudyEnrollment')
            ->findAllByAcademicAndAcademicYear($academic, $this->getCurrentAcademicYear());

        foreach($enrollments as $enrollment)
            $this->getEntityManager()->remove($enrollment);

        $enrollments = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Syllabus\SubjectEnrollment')
            ->findAllByAcademicAndAcademicYear($academic, $this->getCurrentAcademicYear());

        foreach($enrollments as $enrollment)
            $this->getEntityManager()->remove($enrollment);

        if (!empty($data['studies'])) {
            foreach($data['studies'] as $id) {
                $study = $this->getEntityManager()
                    ->getRepository('SyllabusBundle\Entity\Study')
                    ->findOneById($id);
                $this->getEntityManager()->persist(new StudyEnrollment($academic, $this->getCurrentAcademicYear(), $study));

                $subjects = $this->getEntityManager()
                    ->getRepository('SyllabusBundle\Entity\StudySubjectMap')
                    ->findAllByStudyAndAcademicYear($study, $this->getCurrentAcademicYear());

                foreach($subjects as $subject) {
                    if ($subject->isMandatory())
                        $this->getEntityManager()->persist(new SubjectEnrollment($academic, $this->getCurrentAcademicYear(), $subject->getSubject()));
                }
            }
        }
        $this->getEntityManager()->flush();

        return new ViewModel(
            array(
                'result' => (object) array('status' => 'success'),
            )
        );
    }

    public function subjectsAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'ERROR',
                    'Please login first!'
                )
            );

            $this->redirect()->toRoute(
                'index'
            );

            return new ViewModel();
        }

        $academic = $this->getAuthentication()->getPersonObject();

        $form = new SubjectForm();

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            $form->setData($formData);

            if ($form->isValid()) {
                $formData = $form->getFormData($formData);

                $this->getEntityManager()->persist(
                    new SubjectEnrollment(
                        $academic,
                        $this->getCurrentAcademicYear(),
                        $this->getEntityManager()
                            ->getRepository('SyllabusBundle\Entity\Subject')
                            ->findOneById($formData['subject_id'])
                    )
                );

                $this->getEntityManager()->flush();

                $this->flashMessenger()->addMessage(
                    new FlashMessage(
                        FlashMessage::SUCCESS,
                        'SUCCESS',
                        'The subject was succesfully added!'
                    )
                );

                $this->redirect()->toRoute(
                    'account',
                    array(
                        'action' => 'subjects',
                    )
                );

                return new ViewModel(
                    array(
                        'currentAcademicYear' => $this->getCurrentAcademicYear(),
                    )
                );
            }
        }

        $enrollments = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Syllabus\StudyEnrollment')
            ->findAllByAcademicAndAcademicYear($academic, $this->getCurrentAcademicYear());

        $mappings = array();
        $studySubjects = array();
        foreach($enrollments as $enrollment) {
            $subjects = $this->getEntityManager()
                ->getRepository('SyllabusBundle\Entity\StudySubjectMap')
                ->findAllByStudyAndAcademicYear($enrollment->getStudy(), $this->getCurrentAcademicYear());
            $mappings[] = array(
                'enrollment' => $enrollment,
                'subjects' => $subjects,
            );
            foreach($subjects as $subject)
                $studySubjects[] = $subject->getSubject()->getId();
        }

        $enrollments = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Syllabus\SubjectEnrollment')
            ->findAllByAcademicAndAcademicYear($academic, $this->getCurrentAcademicYear());

        $subjectIds = array();
        $otherSubjects = array();
        foreach($enrollments as $enrollment) {
            $subjectIds[] = $enrollment->getSubject()->getId();

            if (!in_array($enrollment->getSubject()->getId(), $studySubjects))
                $otherSubjects[] = $enrollment->getSubject();
        }

        return new ViewModel(
            array(
                'form' => $form,
                'mappings' => $mappings,
                'enrollments' => $subjectIds,
                'currentAcademicYear' => $this->getCurrentAcademicYear(),
                'otherSubjects' => $otherSubjects,
            )
        );
    }

    public function saveSubjectsAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'ERROR',
                    'Please login first!'
                )
            );

            $this->redirect()->toRoute(
                'index'
            );

            return new ViewModel();
        }

        $this->initAjax();

        $data = $this->getRequest()->getPost();
        $academic = $this->getAuthentication()->getPersonObject();

        $enrollments = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Syllabus\SubjectEnrollment')
            ->findAllByAcademicAndAcademicYear($academic, $this->getCurrentAcademicYear());

        foreach($enrollments as $enrollment)
            $this->getEntityManager()->remove($enrollment);

        if (!empty($data['subjects'])) {
            foreach($data['subjects'] as $id) {
                $subject = $this->getEntityManager()
                    ->getRepository('SyllabusBundle\Entity\Subject')
                    ->findOneById($id);
                $this->getEntityManager()->persist(new SubjectEnrollment($academic, $this->getCurrentAcademicYear(), $subject));
            }
        }
        $this->getEntityManager()->flush();

        return new ViewModel(
            array(
                'result' => (object) array('status' => 'success'),
            )
        );
    }

    public function activateAction()
    {
        if (!($user = $this->_getUser()))
            return new ViewModel();

        $form = new ActivateForm();

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            $form->setData($formData);

            if ($form->isValid()) {
                $formData = $form->getFormData($formData);

                $user->setCode(null)
                    ->setCredential(
                        new Credential(
                            'sha512',
                            $formData['credential']
                        )
                    );

                $this->getEntityManager()->flush();

                $this->flashMessenger()->addMessage(
                    new FlashMessage(
                        FlashMessage::SUCCESS,
                        'Success',
                        'Your account was succesfully activated!'
                    )
                );

                $this->redirect()->toRoute(
                    'index'
                );

                return new ViewModel();
            }
        }

        return new ViewModel(
            array(
                'form' => $form,
            )
        );
    }

    public function photoAction() {
        $imagePath = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('common.profile_path') . '/' . $this->getParam('image');

        $headers = new Headers();
        $headers->addHeaders(array(
            'Content-Disposition' => 'inline; filename="' . $this->getParam('image') . '"',
            'Content-Type' => mime_content_type($imagePath),
            'Content-Length' => filesize($imagePath),
        ));
        $this->getResponse()->setHeaders($headers);

        $handle = fopen($imagePath, 'r');
        $data = fread($handle, filesize($imagePath));
        fclose($handle);

        return new ViewModel(
            array(
                'data' => $data,
            )
        );
    }

    public function fileServerAction() {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'ERROR',
                    'Please login first!'
                )
            );

            $this->redirect()->toRoute(
                'index'
            );

            return new ViewModel();
        }

        if ('' == $this->getAuthentication()->getPersonObject()->getUniversityIdentification()) {
            return new ViewModel(
                array(
                    'noUniversityIdentification' => true
                )
            );
        }

        $registration = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Registration')
            ->findOneByAcademicAndAcademicYear($this->getAuthentication()->getPersonObject(), $this->getCurrentAcademicYear());

        if (null !== $registration && $registration->hasPayed()) {
            $this->getLdap()->bind();

            $peopleOu = $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('common.ldap_people_ou');
            $studentsOu = $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('common.ldap_students_ou');
            $studentsCn = $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('common.ldap_students_cn');
            $usersCn = $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('common.ldap_users_cn');

            if ($accountExists = $this->getLdap()->exists('uid=' . $this->getAuthentication()->getPersonObject()->getUniversityIdentification() . ',' . $studentsOu)) {
                $form = new ChangePasswordForm();

                if ($this->getRequest()->isPost()) {
                    $formData = $this->getRequest()->getPost();
                    $form->setData($formData);

                    if ($form->isValid()) {
                        $entry = $this->getLdap()->getEntry('uid=' . $this->getAuthentication()->getPersonObject()->getUniversityIdentification() . ',' . $studentsOu);

                        $salt = mcrypt_create_iv(8, MCRYPT_DEV_URANDOM);
                        Attribute::setAttribute(
                            $entry, 'userPassword', '{SSHA}' . base64_encode(sha1($formData['password'] . $salt, true) . $salt)
                        );

                        if ('production' == getenv('APPLICATION_ENV')) {
                            $this->getLdap()->update(
                                'uid=s0213526,' . $studentsOu, $studentsGroup
                            );
                        }

                        $this->flashMessenger()->addMessage(
                            new FlashMessage(
                                FlashMessage::SUCCESS,
                                'Success',
                                'Your password was succesfully changed!'
                            )
                        );

                        $this->redirect()->toRoute(
                            'account',
                            array(
                                'action' => 'fileServer',
                            )
                        );

                        return new ViewModel();
                    }
                }
            } else {
                $form = new CreateAccountForm();

                if ($this->getRequest()->isPost()) {
                    $formData = $this->getRequest()->getPost();
                    $form->setData($formData);

                    if ($form->isValid()) {
                        $uidNumbers = $this->getLdap()->search(
                            'uidNumber=*',
                            $peopleOu,
                            Ldap::SEARCH_SCOPE_SUB,
                            array(
                                'uidNumber'
                            )
                        );

                        $maxUidNumber = 0;
                        foreach ($uidNumbers as $uidNumber) {
                            if ($uidNumber['uidnumber'][0] == 65534)
                                continue;

                            if ($uidNumber['uidnumber'][0] > $maxUidNumber)
                                $maxUidNumber = $uidNumber['uidnumber'][0];
                        }

                        $studentsGroup = $this->getLdap()->getEntry($studentsCn);
                        $usersGroup = $this->getLdap()->getEntry($usersCn);

                        // Creating our new user
                        $newEntry = array();

                        Attribute::setAttribute(
                            $newEntry,
                            'objectClass',
                            array(
                                'posixAccount',
                                'inetOrgPerson',
                                'organizationalPerson',
                                'person'
                            )
                        );

                        Attribute::setAttribute(
                            $newEntry, 'cn', $this->getAuthentication()->getPersonObject()->getFullName()
                        );
                        Attribute::setAttribute(
                            $newEntry, 'gidNumber', $usersGroup['gidnumber'][0]
                        );
                        Attribute::setAttribute(
                            $newEntry, 'givenName', $this->getAuthentication()->getPersonObject()->getFirstName()
                        );
                        Attribute::setAttribute(
                            $newEntry, 'homeDirectory', '/vtk/students/' . $this->getAuthentication()->getPersonObject()->getUniversityIdentification()
                        );
                        Attribute::setAttribute(
                            $newEntry, 'loginShell', '/bin/false'
                        );

                        Attribute::setAttribute(
                            $newEntry, 'sn', $this->getAuthentication()->getPersonObject()->getLastName()
                        );

                        Attribute::setAttribute(
                            $newEntry, 'uid', $this->getAuthentication()->getPersonObject()->getUniversityIdentification()
                        );
                        Attribute::setAttribute(
                            $newEntry, 'uidNumber', ++$maxUidNumber
                        );

                        $salt = mcrypt_create_iv(8, MCRYPT_DEV_URANDOM);
                        Attribute::setAttribute(
                            $newEntry, 'userPassword', '{SSHA}' . base64_encode(sha1($formData['password'] . $salt, true) . $salt)
                        );

                        if ('production' == getenv('APPLICATION_ENV')) {
                            $this->getLdap()->add(
                                'uid=' . $this->getAuthentication()->getPersonObject()->getUniversityIdentification() . ',' . $studentsOu, $newEntry
                            );
                        }

                        // Add the user to the group
                        $memberUidArray = $studentsGroup['memberuid'];

                        $memberUidArray[] = $this->getAuthentication()->getPersonObject()->getUniversityIdentification();
                        Attribute::setAttribute(
                            $studentsGroup, 'memberUid', $memberUidArray
                        );

                        if ('production' == getenv('APPLICATION_ENV')) {
                            $this->getLdap()->update(
                                $studentsCn, $studentsGroup
                            );
                        }

                        $this->flashMessenger()->addMessage(
                            new FlashMessage(
                                FlashMessage::SUCCESS,
                                'Success',
                                'Your account was successfully created! However, please note that it may take a few minutes before your account is accessible.'
                            )
                        );

                        $this->redirect()->toRoute(
                            'account',
                            array(
                                'action' => 'fileServer',
                            )
                        );

                        return new ViewModel();
                    }
                }
            }

            return new ViewModel(
                array(
                    'hasPayed' => true,
                    'accountExists' => $accountExists,
                    'form' => $form
                )
            );
        } else {
            return new ViewModel(
                array(
                    'hasPayed' => false
                )
            );
        }
    }

    private function _getUser()
    {
        if (null === $this->getParam('code')) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No code was given to identify the user!'
                )
            );

            $this->redirect()->toRoute(
                'index'
            );

            return;
        }

        $user = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\Users\Code')
            ->findOnePersonByCode($this->getParam('code'));

        if (null === $user) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'The given code is not valid!'
                )
            );

            $this->redirect()->toRoute(
                'index'
            );

            return;
        }

        return $user;
    }

    private function _doRedirect() {
        if (null === $this->getParam('return')) {
            $this->redirect()->toRoute(
                'account'
            );
        } else {
            $this->redirect()->toRoute(
                $this->getParam('return')
            );
        }
    }
}
