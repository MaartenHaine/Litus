<?php
/**
 * Litus is a project by a group of students from the KU Leuven. The goal is to create
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

namespace FormBundle\Controller;

use CommonBundle\Component\FlashMessenger\FlashMessage,
    DateTime,
    Zend\View\Model\ViewModel;

/**
 * GroupController
 *
 * @author Kristof Mariën <kristof.marien@litus.cc>
 */
class GroupController extends \CommonBundle\Component\Controller\ActionController\SiteController
{
    public function viewAction()
    {
        if (!($group = $this->_getGroup()))
            return new ViewModel();

        $now = new DateTime();
        if ($now < $group->getStartDate() || $now > $group->getEndDate() || !$group->isActive() || sizeof($group->getForms()) == 0) {
            return new ViewModel(
                array(
                    'message'   => 'This form group is currently closed.',
                    'group'     => $group,
                )
            );
        }

        if (!$this->getAuthentication()->isAuthenticated() && !$group->isNonMember()) {
            return new ViewModel(
                array(
                    'message'   => 'Please login to view this group.',
                    'group'     => $group,
                )
            );
        }

        $entries = array();
        $firstForm = $group->getForms()[0]->getForm();
        $startForm = $group->getForms()[0]->getForm();

        foreach($group->getForms() as $form) {
            $person = $this->getAuthentication()->getPersonObject();

            if (null !== $person) {
                $entries[$form->getForm()->getId()] = current(
                    $this->getEntityManager()
                        ->getRepository('FormBundle\Entity\Node\Entry')
                        ->findAllByFormAndPerson($form->getForm(), $person)
                );

                if ($entries[$form->getForm()->getId()]) {
                    $startForm = $form->getForm();
                }
            } elseif(isset($_COOKIE['LITUS_form'])) {
                $guestInfo = $this->getEntityManager()
                    ->getRepository('FormBundle\Entity\Node\GuestInfo')
                    ->findOneBySessionId($_COOKIE['LITUS_form']);

                $entries[$form->getForm()->getId()] = current(
                    $this->getEntityManager()
                        ->getRepository('FormBundle\Entity\Node\Entry')
                        ->findAllByFormAndGuestInfo($form->getForm(), $guestInfo)
                );

                if ($entries[$form->getForm()->getId()]) {
                    $startForm = $form->getForm();
                }
            }
        }

        return new ViewModel(
            array(
                'group' => $group,
                'entries' => $entries,
                'startForm' => $startForm,
                'isFirstForm' => $startForm->getId() == $firstForm->getId(),
            )
        );
    }

    private function _getGroup()
    {
        if (null === $this->getParam('id')) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $group = $this->getEntityManager()
            ->getRepository('FormBundle\Entity\Node\Group')
            ->findOneById($this->getParam('id'));

        if (null === $group) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return $group;
    }
}