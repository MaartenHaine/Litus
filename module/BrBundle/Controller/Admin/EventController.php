<?php

namespace BrBundle\Controller\Admin;

use BrBundle\Entity\Event;
use BrBundle\Entity\Event\CompanyMap;
use Laminas\View\Model\ViewModel;

/**
 * EventController
 *
 * Controller for events organised by VTK Corporate Relations itself.
 *
 * @author Matthias Swiggers <matthias.swiggers@vtk.be>
 */

class EventController extends \CommonBundle\Component\Controller\ActionController\AdminController
{
    public function manageAction()
    {
        $paginator = $this->paginator()->createFromQuery(
            $this->getEntityManager()
                ->getRepository('BrBundle\Entity\Event')
                ->findAllActiveQuery(),
            $this->getParam('page')
        );

        return new ViewModel(
            array(
                'paginator'         => $paginator,
                'paginationControl' => $this->paginator()->createControl(true),
            )
        );
    }

    public function oldAction()
    {
        $paginator = $this->paginator()->createFromQuery(
            $this->getEntityManager()
                ->getRepository('BrBundle\Entity\Event')
                ->findAllOldQuery(),
            $this->getParam('page')
        );

        return new ViewModel(
            array(
                'paginator'         => $paginator,
                'paginationControl' => $this->paginator()->createControl(true),
            )
        );
    }

    public function addAction()
    {
        $person = $this->getAuthentication()->getPersonObject();
        if ($person == null){
            return new ViewModel();
        }

        $form = $this->getForm('br_event_add');

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());

            if ($form->isValid()) {
                $this->getEntityManager()->persist(
                    $form->hydrateObject()
                );
                $this->getEntityManager()->flush();

                $this->flashMessenger()->success(
                    'Success',
                    'The event was succesfully created!'
                );

                $this->redirect()->toRoute(
                    'br_admin_event',
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

    public function editAction()
    {
        $event = $this->getEventEntity();
        if ($event === null) {
            return new ViewModel();
        }

        $propertiesForm = $this->getForm('br_event_edit', array('event' => $event));
        $companyMapForm = $this->getForm('br_event_companyMap');

        if ($this->getRequest()->isPost()) {
//            die("is post");
            $formData = $this->getRequest()->getPost();
            $propertiesForm->setData($formData);
            $companyMapForm->setData($formData);
//            die(json_encode($propertiesForm->getData()));
//            die(json_encode($propertiesForm->isValid()));
//            if (isset($formData['event_edit']) && $propertiesForm->isValid()) {
            if ($propertiesForm->isValid()) {
//                die("hier zo");
                $this->flashMessenger()->success(
                    'Success',
                    'The event was successfully updated!'
                );

                $this->redirect()->toRoute(
                    'br_admin_event',
                    array(
                        'action' => 'manage',
                    )
                );
            } elseif (isset($formData['event_companyMap']) && $companyMapForm->isValid()) {
                $company = $this->getEntityManager()
                    ->getRepository('BrBundle\Entity\Company')
                    ->findOneById($formData['company']);
                if (count(
                    $this->getEntityManager()
                        ->getRepository('BrBundle\Entity\Event\CompanyMap')
                        ->findByEventAndCompany($event, $company)
                ) == 0
                ) {
                    $objectMap = new CompanyMap($company, $event);
                    $objectMap->setDone();
                    $this->getEntityManager()->persist($objectMap);

                    $this->flashMessenger()->success(
                        'Success',
                        'The attendee was successfully added!'
                    );
                } else {
                    $this->flashMessenger()->error(
                        'Error',
                        'That company is already attending this event!'
                    );
                }

                $this->redirect()->toRoute(
                    'br_admin_event',
                    array(
                        'action' => 'edit',
                        'id'     => $event->getId(),
                    )
                );
            }

            $this->getEntityManager()->flush();
        }


        $allEventCompanyMaps = $this->getEntityManager()
            ->getRepository('BrBundle\Entity\Event\CompanyMap')
            ->findAllByEvent($event);

        $maps = array();
        $comps = array();

        foreach ($allEventCompanyMaps as $map) {
            $comp = $map->getCompany()->getId();
            if (!in_array($comp, $comps)) {
                array_push($comps, $comp);
                array_push($maps, $map);
            }
        }

        return new ViewModel(
            array(
                'propertiesForm'   => $propertiesForm,
                'companyMapForm'   => $companyMapForm,
                'eventCompanyMaps' => $maps,
                'event'            => $event,
            )
        );
    }

    public function deleteAction()
    {
        die("delete");
        $this->initAjax();
        $event = $this->getEventEntity();
        if ($event === null) {
            return new ViewModel();
        }

        $this->getEntityManager()->remove($event);
        $this->getEntityManager()->flush();

        return new ViewModel(
            array(
                'result' => (object) array('status' => 'success'),
            )
        );
    }

    public function deleteAttendeeAction()
    {
        $this->initAjax();


        $event = $this->getEventEntity();
        if ($event === null) {
            return new ViewModel();
        }

        $companymap = $this->getCompanyMapEntity();
        if ($companymap === null) {
            return new ViewModel();
        }

        $this->getEntityManager()->remove($companymap);
        $this->getEntityManager()->flush();

        return new ViewModel(
            array(
                'result' => (object) array('status' => 'success'),
            )
        );
    }

    /**
     * @return Event|null
     */
    private function getEventEntity()
    {
        $event = $this->getEntityById('BrBundle\Entity\Event');

        if (!($event instanceof Event)) {
            $this->flashMessenger()->error(
                'Error',
                'No event was found!'
            );

            $this->redirect()->toRoute(
                'br_admin_event',
                array(
                    'action' => 'manage',
                )
            );

            return;
        }

        return $event;
    }

    /**
     * @return CompanyMap|null
     */
    private function getCompanyMapEntity()
    {
        $event = $this->getEntityById('BrBundle\Entity\Event\CompanyMap', 'map');
        if (!($event instanceof CompanyMap)) {
            $this->flashMessenger()->error(
                'Error',
                'No Map was found!'
            );

            $this->redirect()->toRoute(
                'br_admin_event',
                array(
                    'action' => 'manage',
                )
            );

            return;
        }

        return $event;
    }
}
