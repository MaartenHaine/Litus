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

namespace PublicationBundle\Controller\Admin;

use CommonBundle\Component\FlashMessenger\FlashMessage,
    PublicationBundle\Entity\Publication,
    PublicationBundle\Entity\HtmlEdition,
    PublicationBundle\Form\Admin\HtmlEdition\Add as AddForm,
    Zend\File\Transfer\Adapter\Http as FileUpload,
    Zend\Validator\File\Size as SizeValidator,
    Zend\Validator\File\Extension as ExtensionValidator,
    Zend\View\Model\ViewModel;

class HtmlEditionController extends \CommonBundle\Component\Controller\ActionController\AdminController
{
    public function manageAction()
    {
        if (!($publication = $this->_getPublication()))
            return new ViewModel();

        $paginator = $this->paginator()->createFromArray(
            $this->getEntityManager()
                ->getRepository('PublicationBundle\Entity\HtmlEdition')
                ->findAllByPublicationAndAcademicYear($publication, $this->getCurrentAcademicYear()),
            $this->getParam('page')
        );

        return new ViewModel(
            array(
                'publication' => $publication,
                'paginator' => $paginator,
                'paginationControl' => $this->paginator()->createControl(true),
            )
        );
    }

    public function addAction()
    {
        if (!($publication = $this->_getPublication()))
            return new ViewModel();

        $form = new AddForm($this->getEntityManager());

        if($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            $form->setData($formData);

            if ($form->isValid()) {

                $upload = new FileUpload();

                $upload->addValidator(new SizeValidator(array('max' => '30MB')));
                $upload->addValidator(new ExtensionValidator('zip'));

                if ($upload->isValid()) {
//                    $edition = new HtmlEdition($publication, $formData['title'], $formData['file']);

//                    $this->getEntityManager()->persist($edition);
//                    $this->getEntityManager()->flush();

                    $this->flashMessenger()->addMessage(
                        new FlashMessage(
                            FlashMessage::SUCCESS,
                            'SUCCES',
                            'The publication was succesfully created!'
                        )
                    );

                    $this->redirect()->toRoute(
                        'admin_edition_html',
                        array(
                            'action' => 'manage',
                            'id' => $publication->getId(),
                        )
                    );

                    return new ViewModel();

                } else {
                    $dataError = $upload->getMessages();
                    $error = array();

                    foreach($dataError as $key=>$row)
                        $error[] = $row;

                    $form->setMessages(array('file'=>$error ));
                }

            }
        }

        return new ViewModel(
            array(
                'publication' => $publication,
                'form' => $form,
            )
        );
    }

    public function deleteAction()
    {
        $this->initAjax();

        if (!($edition = $this->_getEdition()))
            return new ViewModel();

        $this->getEntityManager()->remove($edition);
        $this->getEntityManager()->flush();

        return new ViewModel(
            array(
                'result' => (object) array("status" => "success"),
            )
        );
    }

    private function _getEdition()
    {
        if (null === $this->getParam('id')) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No ID was given to identify the edition!'
                )
            );

            $this->redirect()->toRoute(
                'admin_publication',
                array(
                    'action' => 'manage'
                )
            );

            return;
        }

        $edition = $this->getEntityManager()
            ->getRepository('PublicationBundle\Entity\HtmlEdition')
            ->findOneActiveById($this->getParam('id'));

        if (null === $edition) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No edition with the given ID was found!'
                )
            );

            $this->redirect()->toRoute(
                'admin_publication',
                array(
                    'action' => 'manage'
                )
            );

            return;
        }

        return $edition;
    }
    private function _getPublication()
    {
        if (null === $this->getParam('id')) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No ID was given to identify the publication!'
                )
            );

            $this->redirect()->toRoute(
                'admin_publication',
                array(
                    'action' => 'manage'
                )
            );

            return;
        }

        $publication = $this->getEntityManager()
            ->getRepository('PublicationBundle\Entity\Publication')
            ->findOneActiveById($this->getParam('id'));

        if (null === $publication) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No publication with the given ID was found!'
                )
            );

            $this->redirect()->toRoute(
                'admin_publication',
                array(
                    'action' => 'manage'
                )
            );

            return;
        }

        return $publication;
    }
}