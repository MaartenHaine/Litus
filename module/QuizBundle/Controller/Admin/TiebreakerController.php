<?php

namespace QuizBundle\Controller\Admin;

use Laminas\View\Model\ViewModel;
use QuizBundle\Entity\Quiz;
use QuizBundle\Entity\Tiebreaker;

/**
 * TiebreakerController
 *
 * Controller for /admin/quiz/:quizid/tiebreaker[/:action[/:id]][/page/:page][/]
 *
 * @author Robbe Serry <robbe.serry@vtk.be>
 */
class TiebreakerController extends \CommonBundle\Component\Controller\ActionController\AdminController
{
    public function manageAction()
    {
        $quiz = $this->getQuizEntity();
        if ($quiz === null) {
            return new ViewModel();
        }

        $paginator = $this->paginator()->createFromEntity(
            'QuizBundle\Entity\Tiebreaker',
            $this->getParam('page'),
            array(
                'quiz' => $quiz,
            ),
            array(
                'order' => 'ASC',
            )
        );

        return new ViewModel(
            array(
                'quiz'              => $quiz,
                'paginator'         => $paginator,
                'paginationControl' => $this->paginator()->createControl(),
            )
        );
    }

    public function addAction()
    {
        $quiz = $this->getQuizEntity();
        if ($quiz === null) {
            return new ViewModel();
        }

        $form = $this->getForm('quiz_tiebreaker_add', array('quiz' => $quiz));

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());

            if ($form->isValid()) {
                $tiebreaker = $form->hydrateObject(
                    new Tiebreaker($quiz)
                );

                $this->getEntityManager()->persist($tiebreaker);
                $this->getEntityManager()->flush();

                $this->flashMessenger()->success(
                    'Success',
                    'The tiebreaker was successfully added!'
                );
                $this->redirect()->toRoute(
                    'quiz_admin_tiebreaker',
                    array(
                        'action' => 'manage',
                        'quizid' => $quiz->getId(),
                    )
                );
            }
        }

        $next_tiebreaker_number = $this->getEntityManager()
            ->getRepository('QuizBundle\Entity\Tiebreaker')
            ->getNextTiebreakerOrderForQuiz($quiz);

        $form->get('order')
            ->setValue($next_tiebreaker_number);

        return new ViewModel(
            array(
                'quiz' => $quiz,
                'form' => $form,
            )
        );
    }

    public function editAction()
    {
        $tiebreaker = $this->getTiebreakerEntity();
        if ($tiebreaker === null) {
            return new ViewModel();
        }

        $form = $this->getForm('quiz_tiebreaker_edit', array('tiebreaker' => $tiebreaker));

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());

            if ($form->isValid()) {
                $this->getEntityManager()->flush();

                $this->flashMessenger()->success(
                    'Success',
                    'The tiebreaker was successfully edited!'
                );

                $this->redirect()->toRoute(
                    'quiz_admin_tiebreaker',
                    array(
                        'action' => 'manage',
                        'quizid' => $tiebreaker->getQuiz()->getId(),
                    )
                );
            }
        }

        return new ViewModel(
            array(
                'quiz' => $tiebreaker->getQuiz(),
                'form' => $form,
            )
        );
    }

    public function deleteAction()
    {
        $this->initAjax();

        $tiebreaker = $this->getTiebreakerEntity();
        if ($tiebreaker === null) {
            return new ViewModel();
        }

        $this->getEntityManager()->remove($tiebreaker);
        $this->getEntityManager()->flush();

        return new ViewModel(
            array(
                'result' => array(
                    'status' => 'success',
                ),
            )
        );
    }

    public function sortAction()
    {
        $this->initAjax();

        $quiz = $this->getQuizEntity();
        if ($quiz === null) {
            return new ViewModel();
        }

        if (!$this->getRequest()->isPost()) {
            return new ViewModel();
        }

        $data = $this->getRequest()->getPost();

        if (!$data['items']) {
            return new ViewModel();
        }

        foreach ($data['items'] as $order => $id) {
            $tiebreaker = $this->getEntityManager()->find('QuizBundle\Entity\Tiebreaker', $id);
            $tiebreaker->setOrder($order + 1);
        }

        $this->getEntityManager()->flush();

        return new ViewModel(
            array(
                'result' => array(
                    'status' => 'success',
                ),
            )
        );
    }

    /**
     * @return Quiz|null
     */
    private function getQuizEntity()
    {
        $quiz = $this->getEntityById('QuizBundle\Entity\Quiz', 'quizid');

        if (!($quiz instanceof Quiz) || !$quiz->canBeEditedBy($this->getAuthentication()->getPersonObject())) {
            $this->flashMessenger()->error(
                'Error',
                'No quiz was found!'
            );

            $this->redirect()->toRoute(
                'quiz_admin_quiz',
                array(
                    'action' => 'manage',
                )
            );

            return;
        }

        return $quiz;
    }

    /**
     * @return Tiebreaker|null
     */
    private function getTiebreakerEntity()
    {
        $tiebreaker = $this->getEntityById('QuizBundle\Entity\Tiebreaker');

        if (!($tiebreaker instanceof Tiebreaker) || !$tiebreaker->getQuiz()->canBeEditedBy($this->getAuthentication()->getPersonObject())) {
            $this->flashMessenger()->error(
                'Error',
                'No tiebreaker was found!'
            );

            $this->redirect()->toRoute(
                'quiz_admin_quiz',
                array(
                    'action' => 'manage',
                )
            );

            return;
        }

        return $tiebreaker;
    }
}
