<?php
/**
 * Litus is a project by a group of students from the K.U.Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 * @author Michiel Staessen <michiel.staessen@litus.cc>
 * @author Alan Szepieniec <alan.szepieniec@litus.cc>
 *
 * @license http://litus.cc/LICENSE
 */

namespace ApiBundle\Controller;

use CommonBundle\Entity\Users\People\Academic,
    Zend\View\Model\ViewModel;

/**
 * AuthController
 *
 * @author Pieter Maene <pieter.maene@litus.cc>
 */
class AuthController extends \ApiBundle\Component\Controller\ActionController\ApiController
{
    public function getPersonAction()
    {
        if (!($person = $this->_getPerson())) {
            return new ViewModel(
                array(
                    'result' => null
                )
            );
        }

        $result = array(
            'username' => $person->getUsername(),
            'full_name' => $person->getFullName(),
            'email' => $person->getPrimaryEmail()
        );

        if ($person instanceof Academic) {
            $result['university_status'] = $person->getUniversityStatus($this->getCurrentAcademicYear());
        }

        return new ViewModel(
            array(
                'result' => $result
            )
        );
    }

    private function _getPerson()
    {
        if ('' != $this->getRequest()->post()->get('session', '')) {
            $session = $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\Users\Session')
                ->findOneById($this->getRequest()->post()->get('session'));

            return $session->getPerson();
        }

        if ('' != $this->getRequest()->post()->get('username', '')) {
            return $this->getEntityManager()
                ->getRepository('CommonBundle\Entity\Users\Person')
                ->getOneByUsername($this->getRequest()->post()->get('username'));
        }

        return null;
    }
}
