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

namespace BrBundle\Controller\Corporate;

use CommonBundle\Component\FlashMessenger\FlashMessage,
    Zend\Http\Headers,
    Zend\View\Model\ViewModel;

/**
 * IndexController
 *
 * @author Kristof Mariën <kristof.marien@litus.cc>
 */
class IndexController extends \BrBundle\Component\Controller\CorporateController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function cvAction()
    {
        $academicYear = $this->getAcademicYear();

        $groups = $this->getEntityManager()
            ->getRepository('SyllabusBundle\Entity\Group')
            ->findAllCvBook();

        $studies = array();
        $result = array();
        foreach ($groups as $group) {

            $entries = array();
            $studyMaps = $this->getEntityManager()
                ->getRepository('SyllabusBundle\Entity\StudyGroupMap')
                ->findAllByGroupAndAcademicYear($group, $academicYear);

            foreach ($studyMaps as $studyMap) {
                $study = $studyMap->getStudy();
                $studies[] = $study->getId();

                $entries = array_merge($entries, $this->getEntityManager()
                    ->getRepository('BrBundle\Entity\Cv\Entry')
                    ->findAllByStudyAndAcademicYear($study, $academicYear));
            }

            $result[] = array(
                'id' => 'group-' . $group->getId(),
                'name' => $group->getName(),
                'entries' => $entries,
            );
        }

        // Add all studies that are not in a group.
        $cvStudies = $this->getEntityManager()
            ->getRepository('BrBundle\Entity\Cv\Entry')
            ->findAllStudies();

        foreach ($cvStudies as $study) {

            if (in_array($study->getId(), $studies))
                continue;

            $studies[] = $study->getId();

            $entries = $this->getEntityManager()
                ->getRepository('BrBundle\Entity\Cv\Entry')
                ->findAllByStudyAndAcademicYear($study, $academicYear);

            if (count($entries) > 0) {
                $result[] = array(
                    'id' => 'study-' . $study->getId(),
                    'name' => $study->getFullTitle(),
                    'entries' => $entries,
                );
            }

        }

        return new ViewModel(
            array(
                'academicYear' => $academicYear,
                'studies' => $result,
            )
        );
    }

    public function cvPhotoAction() {
        $imagePath = $this->getEntityManager()
            ->getRepository('CommonBundle\Entity\General\Config')
            ->getConfigValue('common.profile_path') . '/' . $this->getParam('image');

        $headers = new Headers();
        $headers->addHeaders(array(
            'Content-Disposition' => 'inline; filename="' . $this->getParam('image') . '"',
            'Content-type' => mime_content_type($imagePath),
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
}