<?php
/**
 * Litus is a project by a group of students from the KU Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Niels Avonds <niels.avonds@litus.cc>
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Koen Certyn <koen.certyn@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Dario Incalza <dario.incalza@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 * @author Lars Vierbergen <lars.vierbergen@litus.cc>
 * @author Daan Wendelen <daan.wendelen@litus.cc>
 *
 * @license http://litus.cc/LICENSE
 */

namespace CommonBundle\Controller;

use CommonBundle\Component\PassKit\Pass\Membership,
    CommonBundle\Component\Util\File\TmpFile,
    CommonBundle\Entity\User\Credential,
    CommonBundle\Entity\User\Person,
    CommonBundle\Entity\User\Status\Organization as OrganizationStatus,
    CommonBundle\Form\Account\FileServer\ChangePassword as ChangePasswordForm,
    CommonBundle\Form\Account\FileServer\CreateAccount as CreateAccountForm,
    CommonBundle\Form\Account\Profile as ProfileForm,
    CudiBundle\Entity\Sale\Booking,
    Imagick,
    SecretaryBundle\Entity\Organization\MetaData,
    SecretaryBundle\Entity\Registration,
    Zend\Http\Headers,
    Zend\Ldap\Attribute,
    Zend\Ldap\Ldap,
    Zend\View\Model\ViewModel;

/**
 * Handles account page.
 *
 * @author Kristof Mariën <kristof.marien@litus.cc>
 */
class AccountController extends \SecretaryBundle\Component\Controller\RegistrationController
{
    public function indexAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->error(
                'Error',
                'Please login first!'
            );

            $this->redirect()->toRoute(
                'common_index'
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
        foreach ($studies as $enrollment) {
            $mappings[] = array(
                'enrollment' => $enrollment,
                'subjects' => $this->getEntityManager()
                    ->getRepository('SyllabusBundle\Entity\StudySubjectMap')
                    ->findAllByStudyAndAcademicYear($enrollment->getStudy(), $this->getCurrentAcademicYear()),
            );
        }

        $subjects = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Syllabus\SubjectEnrollment')
            ->findAllByAcademicAndAcademicYear($this->getAuthentication()->getPersonObject(), $this->getCurrentAcademicYear());

        $subjectIds = array();
        foreach ($subjects as $enrollment) {
            $subjectIds[] = $enrollment->getSubject()->getId();
        }

        $profileForm = $this->getForm('common_account_profile');
        $profileForm->setAttribute(
            'action',
            $this->url()->fromRoute(
                'common_account',
                array(
                    'action' => 'uploadProfileImage',
                )
            )
        );

        return new ViewModel(
            array(
                'academicYear' => $this->getCurrentAcademicYear(),
                'metaData' => $metaData,
                'studies' => $mappings,
                'subjects' => $subjectIds,
                'profilePath' => $this->getEntityManager()
                    ->getRepository('CommonBundle\Entity\General\Config')
                    ->getConfigValue('common.profile_path'),
                'profileForm' => $profileForm,
            )
        );
    }

    public function editAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->error(
                'Error',
                'Please login first!'
            );

            $this->redirect()->toRoute(
                'common_index'
            );

            return new ViewModel();
        }

        $enableRegistration = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('secretary.enable_registration');

        $academic = $this->getAuthentication()->getPersonObject();

        $studentDomain = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('student_email_domain');

        $metaData = $this->getEntityManager()
            ->getRepository('SecretaryBundle\Entity\Organization\MetaData')
            ->findOneByAcademicAndAcademicYear($academic, $this->getCurrentAcademicYear());

        $enableOtherOrganization = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('secretary.enable_other_organization');

        $termsAndConditions = $this->_getTermsAndConditions();

        if (null !== $metaData) {
            $form = $this->getForm('common_account_edit', array(
                'meta_data' => $metaData,
            ));
        } else {
            $form = $this->getForm('common_account_edit', array(
                'academic' => $academic,
            ));
        }

        $ids = unserialize(
            $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('secretary.membership_article')
        );

        $membershipArticles = array();
        foreach ($ids as $organization => $id) {
            echo $id;
            $membershipArticles[$organization] = $this->getEntityManager()
                ->getRepository('CudiBundle\Entity\Sale\Article')
                ->findOneById($id);
        }

        $tshirts = unserialize(
            $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('cudi.tshirt_article')
        );

        $oldTshirtBooking = null;
        $oldTshirtSize = null;
        if (null !== $metaData) {
            if ($enableRegistration) {
                if (null !== $metaData->getTshirtSize()) {
                    $oldTshirtBooking = $this->getEntityManager()
                        ->getRepository('CudiBundle\Entity\Sale\Booking')
                        ->findOneAssignedByArticleAndPersonInAcademicYear(
                            $this->getEntityManager()
                                ->getRepository('CudiBundle\Entity\Sale\Article')
                                ->findOneById($tshirts[$metaData->getTshirtSize()]),
                            $academic,
                            $this->getCurrentAcademicYear()
                        );
                }
            }
            $oldTshirtSize = $metaData->getTshirtSize();
        }

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost()->toArray();
            $formData['academic']['university_identification'] = $academic->getUniversityIdentification();

            if ($metaData && $metaData->becomeMember()) {
                $formData['organization_info']['become_member'] = true;
            } else {
                $formData['organization_info']['become_member'] = isset($formData['organization_info']['become_member'])
                    ? $formData['organization_info']['become_member']
                    : false;
            }

            $organizationData = $formData['organization_info'];

            if (isset($organizationData['organization'])) {
                if (0 == $organizationData['organization'] && $enableOtherOrganization) {
                    $selectedOrganization = null;
                } else {
                    $selectedOrganization = $this->getEntityManager()
                        ->getRepository('CommonBundle\Entity\General\Organization')
                        ->findOneById($organizationData['organization']);
                }
            } else {
                $selectedOrganization = current(
                    $this->getEntityManager()
                        ->getRepository('CommonBundle\Entity\General\Organization')
                        ->findAll()
                );
            }

            $form->setData($formData);

            if ($form->isValid()) {
                $formData = $form->getData();
                $organizationData = $formData['organization_info'];

                if (null === $metaData) {
                    $metaData = $form->hydrateObject();

                    $this->getEntityManager()->persist($metaData);
                }

                if ($academic->canHaveOrganizationStatus($this->getCurrentAcademicYear())) {
                    $academic->addOrganizationStatus(
                        new OrganizationStatus(
                            $academic,
                            'non_member',
                            $this->getCurrentAcademicYear()
                        )
                    );
                }

                if (null !== $selectedOrganization) {
                    $this->_setOrganization(
                        $academic,
                        $this->getCurrentAcademicYear(),
                        $selectedOrganization
                    );
                }

                if ($enableRegistration) {
                    if (null !== $oldTshirtBooking && $oldTshirtSize != $metaData->getTshirtSize()) {
                        $this->getEntityManager()->remove($oldTshirtBooking);
                    }

                    $membershipArticles = array();
                    $ids = unserialize(
                        $this->getEntityManager()
                            ->getRepository('CommonBundle\Entity\General\Config')
                            ->getConfigValue('secretary.membership_article')
                    );

                    foreach ($ids as $organizationId => $articleId) {
                        $membershipArticles[$organizationId] = $this->getEntityManager()
                            ->getRepository('CudiBundle\Entity\Sale\Article')
                            ->findOneById($articleId);
                    }

                    if ($metaData->becomeMember() && null !== $selectedOrganization) {
                        $this->_bookRegistrationArticles($academic, $organizationData['tshirt_size'], $selectedOrganization, $this->getCurrentAcademicYear());
                    } else {
                        foreach ($membershipArticles as $membershipArticle) {
                            $booking = $this->getEntityManager()
                                ->getRepository('CudiBundle\Entity\Sale\Booking')
                                ->findOneSoldOrAssignedOrBookedByArticleAndPersonInAcademicYear(
                                    $membershipArticle,
                                    $academic,
                                    $this->getCurrentAcademicYear()
                                );

                            if (null !== $booking) {
                                $this->getEntityManager()->remove($booking);
                            }
                        }
                    }
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

                $this->flashMessenger()->success(
                    'SUCCESS',
                    'Your data was succesfully updated!'
                );

                $this->_doRedirect();

                return new ViewModel();
            }
        }

        return new ViewModel(
            array(
                'form' => $form,
                'metaData' => $metaData,
                'membershipArticles' => $membershipArticles,
                'termsAndConditions' => $termsAndConditions,
                'studentDomain' => $studentDomain,
                'membershipArticles' => $membershipArticles,
            )
        );
    }

    public function studiesAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->error(
                'Error',
                'Please login first!'
            );

            $this->redirect()->toRoute(
                'common_index'
            );

            return new ViewModel();
        }

        return $this->_studiesAction(
            $this->getAuthentication()->getPersonObject(),
            $this->getCurrentAcademicYear()
        );
    }

    public function saveStudiesAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->error(
                'Error',
                'Please login first!'
            );

            $this->redirect()->toRoute(
                'common_index'
            );

            return new ViewModel();
        }

        $this->initAjax();

        return $this->_saveStudiesAction(
            $this->getAuthentication()->getPersonObject(),
            $this->getCurrentAcademicYear(),
            $this->getRequest()->getPost()->toArray()
        );
    }

    public function subjectsAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->error(
                'Error',
                'Please login first!'
            );

            $this->redirect()->toRoute(
                'common_index'
            );

            return new ViewModel();
        }

        return $this->_subjectAction(
            $this->getAuthentication()->getPersonObject(),
            $this->getCurrentAcademicYear(),
            $this->getForm('secretary_registration_subject_add')
        );
    }

    public function saveSubjectsAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->error(
                'Error',
                'Please login first!'
            );

            $this->redirect()->toRoute(
                'common_index'
            );

            return new ViewModel();
        }

        $this->initAjax();

        return $this->_saveSubjectAction(
            $this->getAuthentication()->getPersonObject(),
            $this->getCurrentAcademicYear(),
            $this->getRequest()->getPost()->toArray()
        );
    }

    public function activateAction()
    {
        if (!($user = $this->_getUser())) {
            return new ViewModel();
        }

        $form = $this->getForm('common_account_activate');

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            $form->setData($formData);

            if ($form->isValid()) {
                $formData = $form->getData();

                $user->setCode(null)
                    ->setCredential(
                        new Credential(
                            $formData['credential']
                        )
                    );

                $this->getEntityManager()->flush();

                $this->flashMessenger()->success(
                    'Success',
                    'Your account was succesfully activated!'
                );

                $this->redirect()->toRoute(
                    'common_index'
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

    public function fileServerAction()
    {
        if (null === $this->getAuthentication()->getPersonObject()) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'Please login first!'
                )
            );

            $this->redirect()->toRoute(
                'common_index'
            );

            return new ViewModel();
        }

        if ('' == $this->getAuthentication()->getPersonObject()->getUniversityIdentification()) {
            return new ViewModel(
                array(
                    'noUniversityIdentification' => true,
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
                                'uid=' . $this->getAuthentication()->getPersonObject()->getUniversityIdentification() . ',' . $studentsOu, $entry
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
                            'common_account',
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
                                'uidNumber',
                            )
                        );

                        $maxUidNumber = 0;
                        foreach ($uidNumbers as $uidNumber) {
                            if ($uidNumber['uidnumber'][0] == 65534) {
                                continue;
                            }

                            if ($uidNumber['uidnumber'][0] > $maxUidNumber) {
                                $maxUidNumber = $uidNumber['uidnumber'][0];
                            }
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
                                'person',
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
                                'Your account was successfully created! Please note that it may take a few minutes before your account is accessible.'
                            )
                        );

                        $this->redirect()->toRoute(
                            'common_account',
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
                    'form' => $form,
                )
            );
        } else {
            return new ViewModel(
                array(
                    'hasPayed' => false,
                )
            );
        }
    }

    public function passbookAction()
    {
        $pass = new TmpFile();
        $membership = new Membership(
            $this->getEntityManager(),
            $this->getAuthentication()->getPersonObject(),
            $this->getCurrentAcademicYear(),
            $pass,
            'data/images/pass_kit'
        );
        $membership->createPass();

        $headers = new Headers();
        $headers->addHeaders(array(
            'Content-Disposition' => 'inline; filename="membership.pkpass"',
            'Content-Type'        => 'application/vnd.apple.pkpass',
            'Content-Length'      => filesize($pass->getFileName()),
        ));
        $this->getResponse()->setHeaders($headers);

        return new ViewModel(
            array(
                'data' => $pass->getContent(),
            )
        );
    }

    public function uploadProfileImageAction()
    {
        $form = $this->getForm('common_account_profile');

        if ($this->getRequest()->isPost()) {
            $form->setData(array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            ));

            $academic = $this->getAuthentication()->getPersonObject();
            $filePath = 'public' . $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('common.profile_path');

            if ($form->isValid()) {
                $formData = $form->getData();

                if ($formData['profile']) {
                    $image = new Imagick($formData['profile']['tmp_name']);
                } else {
                    $image = new Imagick($filePath . '/' . $academic->getPhotoPath());
                }

                if ($formData['x'] == 0 && $formData['y'] == 0 && $formData['x2'] == 0 && $formData['y2'] == 0 && $formData['w'] == 0 && $formData['h'] == 0) {
                    $image->cropThumbnailImage(320, 240);
                } else {
                    $ratio = $image->getImageWidth()/320;
                    $x = $formData['x'] * $ratio;
                    $y = $formData['y'] * $ratio;
                    $w = $formData['w'] * $ratio;
                    $h = $formData['h'] * $ratio;

                    $image->cropImage($w, $h, $x, $y);
                    $image->cropThumbnailImage(320, 240);
                }

                if ($academic->getPhotoPath() != '' || $academic->getPhotoPath() !== null) {
                    $fileName = $academic->getPhotoPath();
                } else {
                    do {
                        $fileName = sha1(uniqid());
                    } while (file_exists($filePath . '/' . $fileName));
                }
                $image->writeImage($filePath . '/' . $fileName);
                $academic->setPhotoPath($fileName);

                $this->getEntityManager()->flush();

                return new ViewModel(
                    array(
                        'result' => array(
                            'status' => 'success',
                            'profile' => $this->getEntityManager()
                                ->getRepository('CommonBundle\Entity\General\Config')
                                ->getConfigValue('common.profile_path') . '/' . $fileName,
                        ),
                    )
                );
            } else {
                return new ViewModel(
                    array(
                        'result' => array(
                            'status' => 'error',
                            'form' => array(
                                'errors' => $form->getMessages(),
                            ),
                        ),
                    )
                );
            }
        }
    }

    /**
     * @return Person|null
     */
    private function _getUser()
    {
        if (null === $this->getParam('code')) {
            $this->flashMessenger()->error(
                'Error',
                'No code was given to identify the user!'
            );

            $this->redirect()->toRoute(
                'common_index'
            );

            return;
        }

        $user = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\User\Code')
            ->findOnePersonByCode($this->getParam('code'));

        if (null === $user) {
            $this->flashMessenger()->error(
                'Error',
                'The given code is not valid!'
            );

            $this->redirect()->toRoute(
                'common_index'
            );

            return;
        }

        return $user;
    }

    /**
     * @return null
     */
    private function _doRedirect()
    {
        if (null === $this->getParam('return')) {
            $this->redirect()->toRoute(
                'common_account'
            );
        } else {
            $this->redirect()->toRoute(
                $this->getParam('return')
            );
        }
    }
}
