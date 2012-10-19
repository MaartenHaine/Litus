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

namespace BrBundle\Controller\Career;

use CommonBundle\Component\FlashMessenger\FlashMessage,
    DateTime,
    Zend\Http\Headers,
    Zend\View\Model\ViewModel;

/**
 * CompanyController
 *
 * @author Niels Avonds <niels.avonds@litus.cc>
 */
class CompanyController extends \BrBundle\Component\Controller\CareerController
{
    public function overviewAction()
    {
        $paginator = $this->paginator()->createFromArray(
            $this->getEntityManager()
            ->getRepository('BrBundle\Entity\Company\Page')
            ->findAllActive($this->getCurrentAcademicYear()),
            $this->getParam('page')
        );

        return new ViewModel(
            array(
                'paginator' => $paginator,
            )
        );
    }

    public function viewAction()
    {
        if (!($page = $this->_getPage()))
            return new ViewModel();

        $events = $this->getEntityManager()
            ->getRepository('BrBundle\Entity\Company\Event')
            ->findAllFutureByCompany(new DateTime(), $page->getCompany());

        $internships = $this->getEntityManager()
            ->getRepository('BrBundle\Entity\Company\Job')
            ->findAllActiveByCompanyAndType($page->getCompany(), 'internship');

        $vacancies = $this->getEntityManager()
            ->getRepository('BrBundle\Entity\Company\Job')
            ->findAllActiveByCompanyAndType($page->getCompany(), 'vacancy');

        return new ViewModel(
            array(
                'page' => $page,
                'events' => $events,
                'internships' => $internships,
                'vacancies' => $vacancies,
            )
        );
    }

    public function fileAction()
    {
        $filePath = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('br.file_path') . '/' . $this->getParam('name');

        if ($this->getParam('name') == '' || !file_exists($filePath)) {
            $this->getResponse()->setStatusCode(404);
            return new ViewModel();
        }

        $headers = new Headers();
        $headers->addHeaders(array(
            'Content-Disposition' => 'inline; filename="' . $this->getParam('name') . '"',
            'Content-type' => mime_content_type($filePath),
            'Content-Length' => filesize($filePath),
        ));
        $this->getResponse()->setHeaders($headers);

        $handle = fopen($filePath, 'r');
        $data = fread($handle, filesize($filePath));
        fclose($handle);

        return new ViewModel(
            array(
                'data' => $data,
            )
        );
    }

    public function searchAction()
    {
        $this->initAjax();

        $pages = $this->getEntityManager()
            ->getRepository('BrBundle\Entity\Company\Page')
            ->findAllActiveBySearch($this->getCurrentAcademicYear(), $this->getParam('string'));

        $result = array();
        foreach($pages as $page) {
            $item = (object) array();
            $item->name = $page->getCompany()->getName();
            $item->logo = $page->getCompany()->getLogo();
            $item->slug = $page->getCompany()->getSlug();
            $result[] = $item;
        }

        return new ViewModel(
            array(
                'result' => $result,
            )
        );
    }


    private function _getPage()
    {
        if (null === $this->getParam('company')) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No name was given to identify the company!'
                )
            );

            $this->redirect()->toRoute(
                'career_company',
                array(
                    'action' => 'overview'
                )
            );

            return;
        }

        $company = $this->getEntityManager()
            ->getRepository('BrBundle\Entity\Company\Page')
            ->findOneActiveBySlug($this->getParam('company'), $this->getCurrentAcademicYear());

        if (null === $company) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No company with the given name was found!'
                )
            );

            $this->redirect()->toRoute(
                'career_company',
                array(
                    'action' => 'overview'
                )
            );

            return;
        }

        return $company;
    }
}
