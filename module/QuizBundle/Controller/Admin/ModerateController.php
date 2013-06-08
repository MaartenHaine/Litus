<?php

namespace QuizBundle\Controller\Admin;

use CommonBundle\Component\FlashMessenger\FlashMessage,
    QuizBundle\Entity\Point,
    Zend\View\Model\ViewModel;

/**
 * ModerateController
 *
 * Controller for /admin/quiz/:quizid/moderate[/:action[/:roundid/:teamid]]
 *
 * @author Lars Vierbergen <vierbergenlars@gmail.com>
 */
class ModerateController extends \CommonBundle\Component\Controller\ActionController\AdminController
{
    public function manageAction()
    {
        if(!($quiz = $this->_getQuiz()))
            return new ViewModel;

        $rounds = $this->getEntityManager()
                ->getRepository('QuizBundle\Entity\Round')
                ->findByQuiz($quiz);
        $teams = $this->getEntityManager()
                ->getRepository('QuizBundle\Entity\Team')
                ->findByQuiz($quiz);
        $allPoints = $this->getEntityManager()
                ->getRepository('QuizBundle\Entity\Point')
                ->findByQuiz($quiz);

        $points = array();
        foreach($allPoints as $point) {
            $points[$point->getTeam()->getId()][$point->getRound()->getId()] = $point->getPoint();
        }

        return new ViewModel(
            array(
                'quiz' => $quiz,
                'rounds' => $rounds,
                'teams' => $teams,
                'points' => $points,
            )
        );
    }

    public function updateAction()
    {
        $this->initAjax();

        if(!($team = $this->_getTeam()) || !($round = $this->_getRound()))
            return new ViewModel;

        $point = $this->getEntityManager()
                ->getRepository('QuizBundle\Entity\Point')
                ->findOneBy(
                    array(
                        'team' => $team,
                        'round' => $round,
                    )
                );

        if($point === null) {
            // If the point does not exist yet, create it.
            $point = new Point($round, $team, 0);
            $this->getEntityManager()->persist($point);
        }

        $postData = $this->getRequest()->getPost();
        $point->setPoint($postData['score']);

        $this->getEntityManager()->flush();

        return new ViewModel(
            array(
                'json' => array(
                    'status' => 'success'
                ),
            )
        );
    }

    public function viewAction()
    {
        if(!($quiz = $this->_getQuiz()))
            return new ViewModel;

        $rounds = $this->getEntityManager()
                ->getRepository('QuizBundle\Entity\Round')
                ->findByQuiz($quiz);
        $teams = $this->getEntityManager()
                ->getRepository('QuizBundle\Entity\Team')
                ->findByQuiz($quiz);
        $allPoints = $this->getEntityManager()
                ->getRepository('QuizBundle\Entity\Point')
                ->findByQuiz($quiz);

        $points = array();
        $totals = array();
        foreach($allPoints as $point) {
            $points[$point->getTeam()->getId()][$point->getRound()->getId()] = $point->getPoint();
            if(!isset($totals[$point->getTeam()->getId()])) // If no point yet counted, set to zero
                $totals[$point->getTeam()->getId()] = 0;
            $totals[$point->getTeam()->getId()] += $point->getPoint();
        }

        arsort($totals); // Reverse sort of the totals (highest points first)

        $teams_indexed = array();
        foreach($teams as $team) {
            $teams_indexed[$team->getId()] = $team;
        }

        return new ViewModel(
            array(
                'quiz' => $quiz,
                'rounds' => $rounds,
                'teams' => $teams_indexed,
                'points' => $points,
                'total_points' => $totals,
            )
        );
    }

    public function resultsAction()
    {
        return $this->viewAction();
    }

    /**
     * @return null|\QuizBundle\Entity\Quiz
     */
    private function _getQuiz()
    {
        if($this->getParam('quizid') === null) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No id was given to identify the quiz!'
                )
            );

            $this->redirect()->toRoute(
                'quiz_admin_quiz',
                array(
                    'action' => 'manage',
                )
            );

            return;
        }

        $quiz = $this->getEntityManager()
            ->getRepository('QuizBundle\Entity\Quiz')
            ->findOneById($this->getParam('quizid'));

        if($quiz === null) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No quiz with the given id was found!'
                )
            );

            $this->redirect()->toRoute(
                'quiz_admin_quiz',
                array(
                    'action' => 'manage'
                )
            );

            return;
        }

        return $quiz;
    }

    /**
     * @return null|\QuizBundle\Entity\Round
     */
    private function _getRound()
    {
        if($this->getParam('roundid') === null) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No id was given to identify the round!'
                )
            );

            $this->redirect()->toRoute(
                'quiz_admin_moderate',
                array(
                    'action' => 'manage',
                )
            );

            return;
        }

        $round = $this->getEntityManager()
            ->getRepository('QuizBundle\Entity\Round')
            ->findOneById($this->getParam('roundid'));

        if($round === null) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No round with the given id was found!'
                )
            );

            $this->redirect()->toRoute(
                'quiz_admin_moderate',
                array(
                    'action' => 'manage'
                )
            );

            return;
        }

        return $round;
    }

    /**
     * @return null|\QuizBundle\Entity\Team
     */
    private function _getTeam()
    {
        if($this->getParam('teamid') === null) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No id was given to identify the team!'
                )
            );

            $this->redirect()->toRoute(
                'quiz_admin_moderate',
                array(
                    'action' => 'manage',
                )
            );

            return;
        }

        $team = $this->getEntityManager()
            ->getRepository('QuizBundle\Entity\Team')
            ->findOneById($this->getParam('teamid'));

        if($team === null) {
            $this->flashMessenger()->addMessage(
                new FlashMessage(
                    FlashMessage::ERROR,
                    'Error',
                    'No team with the given id was found!'
                )
            );

            $this->redirect()->toRoute(
                'quiz_admin_moderate',
                array(
                    'action' => 'manage'
                )
            );

            return;
        }

        return $team;
    }

}