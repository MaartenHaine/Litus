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

namespace ShiftBundle\Controller\Admin;

use CommonBundle\Component\Util\File\TmpFile,
    DateTime,
    ShiftBundle\Component\Document\Generator\Event\Pdf as PdfGenerator,
    ShiftBundle\Entity\Shift,
    ShiftBundle\Form\Admin\Shift\Add as AddForm,
    ShiftBundle\Form\Admin\Shift\Edit as EditForm,
    ShiftBundle\Form\Admin\Shift\Export as ExportForm,
    Zend\Http\Headers,
    Zend\Mail\Message,
    Zend\View\Model\ViewModel;

/**
 * ShiftController
 *
 * @author Pieter Maene <pieter.maene@litus.cc>
 */
class ShiftController extends \CommonBundle\Component\Controller\ActionController\AdminController
{
    public function manageAction()
    {
        $paginator = $this->paginator()->createFromQuery(
            $this->getEntityManager()
                ->getRepository('ShiftBundle\Entity\Shift')
                ->findAllActiveQuery(),
            $this->getParam('page')
        );

        return new ViewModel(
            array(
                'paginator' => $paginator,
                'paginationControl' => $this->paginator()->createControl(true),
            )
        );
    }

    public function oldAction()
    {
        $paginator = $this->paginator()->createFromQuery(
            $this->getEntityManager()
                ->getRepository('ShiftBundle\Entity\Shift')
                ->findAllOldQuery(),
            $this->getParam('page')
        );

        return new ViewModel(
            array(
                'paginator' => $paginator,
                'paginationControl' => $this->paginator()->createControl(true),
            )
        );
    }

    public function addAction()
    {
        $form = new AddForm($this->getEntityManager());

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            $form->setData($formData);

            $startDate = self::_loadDate($formData['start_date']);
            $endDate = self::_loadDate($formData['end_date']);

            if ($form->isValid() && $startDate && $endDate) {
                $formData = $form->getFormData($formData);

                $repository = $this->getEntityManager()
                    ->getRepository('CommonBundle\Entity\User\Person\Academic');

                $manager = ('' == $formData['manager_id'])
                    ? $repository->findOneByUsername($formData['manager'])
                    : $repository->findOneById($formData['manager_id']);

                $editRoles = array();
                if (isset($formData['edit_roles'])) {
                    foreach ($formData['edit_roles'] as $editRole) {
                        $editRoles[] = $this->getEntityManager()
                            ->getRepository('CommonBundle\Entity\Acl\Role')
                            ->findOneByName($editRole);
                    }
                }

                $interval = $startDate->diff($endDate);

                for ($i = 0; $i < $formData['duplicate_days']; $i++) {
                    for ($j = 0; $j < $formData['duplicate_hours']; $j++) {
                        $shift = new Shift(
                            $this->getAuthentication()->getPersonObject(),
                            $this->getCurrentAcademicYear(),
                            $this->addInterval(clone $startDate, $interval, $j),
                            $this->addInterval(clone $startDate, $interval, $j+1),
                            $manager,
                            $formData['nb_responsibles'],
                            $formData['nb_volunteers'],
                            $this->getEntityManager()
                                ->getRepository('CommonBundle\Entity\General\Organization\Unit')
                                ->findOneById($formData['unit']),
                            $this->getEntityManager()
                                ->getRepository('CommonBundle\Entity\General\Location')
                                ->findOneById($formData['location']),
                            $formData['name'],
                            $formData['description'],
                            $editRoles,
                            $formData['reward'],
                            $formData['handled_on_event']
                        );

                        if ('' != $formData['event']) {
                            $shift->setEvent(
                                $this->getEntityManager()
                                    ->getRepository('CalendarBundle\Entity\Node\Event')
                                    ->findOneById($formData['event'])
                            );
                        }

                        $this->getEntityManager()->persist($shift);
                    }

                    $startDate = $startDate->modify('+1 day');
                }

                $this->getEntityManager()->flush();

                $this->flashMessenger()->success(
                    'Succes',
                    'The shift was successfully created!'
                );

                $this->redirect()->toRoute(
                    'shift_admin_shift',
                    array(
                        'action' => 'add',
                    )
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

    /**
     * @param integer $duplicate
     */
    private function addInterval(DateTime $time, $interval, $duplicate)
    {
        for ($i = 0; $i < $duplicate; $i++) {
            $time = $time->add($interval);
        }

        return clone $time;
    }

    public function editAction()
    {
        if (!($shift = $this->_getShift())) {
            return new ViewModel();
        }

        $form = new EditForm($this->getEntityManager(), $shift);

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            $form->setData($formData);

            $startDate = self::_loadDate($formData['start_date']);
            $endDate = self::_loadDate($formData['end_date']);

            if ($form->isValid() && $startDate && $endDate) {
                $formData = $form->getFormData($formData);

                if ($shift->canEditDates()) {
                    $shift->setStartDate($startDate)
                        ->setEndDate($endDate);
                }

                $repository = $this->getEntityManager()
                    ->getRepository('CommonBundle\Entity\User\Person\Academic');

                $manager = ('' == $formData['manager_id'])
                    ? $repository->findOneByUsername($formData['manager']) : $repository->findOneById($formData['manager_id']);

                $editRoles = array();
                if (isset($formData['edit_roles'])) {
                    foreach ($formData['edit_roles'] as $editRole) {
                        $editRoles[] = $this->getEntityManager()
                            ->getRepository('CommonBundle\Entity\Acl\Role')
                            ->findOneByName($editRole);
                    }
                }

                $shift->setManager($manager)
                    ->setNbResponsibles($formData['nb_responsibles'])
                    ->setNbVolunteers($formData['nb_volunteers'])
                    ->setUnit(
                        $this->getEntityManager()
                            ->getRepository('CommonBundle\Entity\General\Organization\Unit')
                            ->findOneById($formData['unit'])
                    )
                    ->setLocation(
                        $this->getEntityManager()
                            ->getRepository('CommonBundle\Entity\General\Location')
                            ->findOneById($formData['location'])
                    )
                    ->setName($formData['name'])
                    ->setDescription($formData['description'])
                    ->setEditRoles($editRoles)
                    ->setReward($formData['reward'])
                    ->setHandledOnEvent($formData['handled_on_event']);

                if ('' != $formData['event']) {
                    $shift->setEvent(
                        $this->getEntityManager()
                            ->getRepository('CalendarBundle\Entity\Node\Event')
                            ->findOneById($formData['event'])
                    );
                } else {
                    $shift->setEvent(null);
                }

                $this->getEntityManager()->flush();

                $this->flashMessenger()->success(
                    'Succes',
                    'The shift was successfully edited!'
                );

                $this->redirect()->toRoute(
                    'shift_admin_shift',
                    array(
                        'action' => 'manage',
                    )
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

    public function deleteAction()
    {
        $this->initAjax();

        if (!($shift = $this->_getShift())) {
            return new ViewModel();
        }

        $mailAddress = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('shift.mail');

        $mailName = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('shift.mail_name');

        $language = $this->getEntityManager()->getRepository('CommonBundle\Entity\General\Language')
            ->findOneByAbbrev('en');

        $mailData = unserialize(
            $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('shift.shift_deleted_mail')
        );

        $message = $mailData[$language->getAbbrev()]['content'];
        $subject = $mailData[$language->getAbbrev()]['subject'];

        $shiftString = $shift->getName() . ' from ' . $shift->getStartDate()->format('d/m/Y h:i') . ' to ' . $shift->getEndDate()->format('d/m/Y h:i');

        $mail = new Message();
        $mail->setBody(str_replace('{{ shift }}', $shiftString, $message))
            ->setFrom($mailAddress, $mailName)
            ->setSubject($subject);

        $mail->addTo($mailAddress, $mailName);

        foreach ($shift->getVolunteers() as $volunteer) {
            $mail->addBcc($volunteer->getPerson()->getEmail(), $volunteer->getPerson()->getFullName());
        }

        foreach ($shift->getResponsibles() as $responsible) {
            $mail->addBcc($responsible->getPerson()->getEmail(), $responsible->getPerson()->getFullName());
        }

        if ('development' != getenv('APPLICATION_ENV')) {
            $this->getMailTransport()->send($mail);
        }

        $this->getEntityManager()->remove(
            $shift->prepareRemove()
        );

        $this->getEntityManager()->flush();

        return new ViewModel(
            array(
                'result' => array(
                    'status' => 'success',
                ),
            )
        );
    }

    public function searchAction()
    {
        $this->initAjax();

        $numResults = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('search_max_results');

        $shifts = $this->_search()
            ->setMaxResults($numResults)
            ->getResult();

        $result = array();
        foreach ($shifts as $shift) {
            $item = (object) array();
            $item->id = $shift->getId();
            $item->name = $shift->getName();
            $item->event = $shift->getEvent()->getTitle($this->getLanguage());
            $item->startDate = $shift->getStartDate()->format('d/m/Y H:i');
            $item->endDate = $shift->getEndDate()->format('d/m/Y H:i');

            $result[] = $item;
        }

        return new ViewModel(
            array(
                'result' => $result,
            )
        );
    }

    public function exportAction()
    {
        $form = new ExportForm($this->getEntityManager());

        return new ViewModel(
            array(
                'form' => $form,
            )
        );
    }

    public function pdfAction()
    {
        if (!($event = $this->_getEvent())) {
            return new ViewModel();
        }

        $file = new TmpFile();
        $document = new PdfGenerator($this->getEntityManager(), $event, $file);
        $document->generate();

        $headers = new Headers();
        $headers->addHeaders(array(
            'Content-Disposition' => 'attachment; filename="shift_list.pdf"',
            'Content-Type'        => 'application/pdf',
        ));
        $this->getResponse()->setHeaders($headers);

        return new ViewModel(
            array(
                'data' => $file->getContent(),
            )
        );
    }

    /**
    *   @return \Doctrine\ORM\Query
    */
    private function _search()
    {
        switch ($this->getParam('field')) {
            case 'name':
                return $this->getEntityManager()
                    ->getRepository('ShiftBundle\Entity\Shift')
                    ->findAllActiveByNameQuery($this->getParam('string'));
        }
    }

    /**
     * @return Shift|null
     */
    private function _getShift()
    {
        if (null === $this->getParam('id')) {
            $this->flashMessenger()->error(
                'Error',
                'No ID was given to identify the shift!'
            );

            $this->redirect()->toRoute(
                'shift_admin_shift',
                array(
                    'action' => 'manage',
                )
            );

            return;
        }

        $shift = $this->getEntityManager()
            ->getRepository('ShiftBundle\Entity\Shift')
            ->findOneById($this->getParam('id'));

        if (null === $shift) {
            $this->flashMessenger()->error(
                'Error',
                'No shift with the given ID was found!'
            );

            $this->redirect()->toRoute(
                'shift_admin_shift',
                array(
                    'action' => 'manage',
                )
            );

            return;
        }

        return $shift;
    }

    private function _getEvent()
    {
        if (null === $this->getParam('id')) {
            $this->flashMessenger()->error(
                'Error',
                'No ID was given to identify the event!'
            );

            $this->redirect()->toRoute(
                'calendar_admin_calendar',
                array(
                    'action' => 'manage',
                )
            );

            return;
        }

        $event = $this->getEntityManager()
            ->getRepository('CalendarBundle\Entity\Node\Event')
            ->findOneById($this->getParam('id'));

        if (null === $event) {
            $this->flashMessenger()->error(
                'Error',
                'No event with the given ID was found!'
            );

            $this->redirect()->toRoute(
                'calendar_admin_calendar',
                array(
                    'action' => 'manage',
                )
            );

            return;
        }

        return $event;
    }

    /**
     * @param  string        $date
     * @return DateTime|null
     */
    private static function _loadDate($date)
    {
        return DateTime::createFromFormat('d#m#Y H#i', $date) ?: null;
    }
}
