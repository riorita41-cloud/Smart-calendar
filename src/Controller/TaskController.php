<?php

namespace App\Controller;

use App\Entity\StudyTask;
use App\Entity\Exam;
use App\Form\StudyTaskType;
use App\Repository\ExamRepository;
use App\Repository\StudyTaskRepository;
use App\Service\XpService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'app_tasks')]
    public function index(StudyTaskRepository $studyTaskRepository): Response
    {
        $tasks = $studyTaskRepository->findByUserOrderedByDate($this->getUser());

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/api/task/{id}/toggle', name: 'app_task_toggle', methods: ['POST'])]
    public function toggle(
        Request $request, 
        int $id, 
        EntityManagerInterface $entityManager, 
        StudyTaskRepository $studyTaskRepository,
        XpService $xpService 
    ): JsonResponse
    {
        try {
            $task = $studyTaskRepository->findForUserOrThrow($id, $this->getUser());
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Задача не найдена'], 404);
        }

        $token = $request->headers->get('X-CSRF-TOKEN');
        
        if (!$token || !$this->isCsrfTokenValid('task_action', $token)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Неверный токен безопасности'], 403);
        }

        $task->setIsCompleted(!$task->isCompleted());
        
        $response = [
            'status' => 'success', 
            'isCompleted' => $task->isCompleted()
        ];

        if ($task->isCompleted() && !$task->isXpAwarded()) {
            $user = $this->getUser();
            $xpResult = $xpService->awardXp($user, 10, 'task_completed');
            
            $task->setXpAwarded(true);
            
            $entityManager->flush(); 

            $response['xp'] = $xpResult;
            $response['message'] = "Задача выполнена! +{$xpResult['xpAdded']} XP";
        } else {
            $entityManager->flush(); 
            
            if ($task->isCompleted()) {
                $response['message'] = 'Задача отмечена как выполненная (XP уже был начислен ранее)';
            }
        }

        return new JsonResponse($response);
    }

    #[Route('/task/{id}/delete', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager, StudyTaskRepository $studyTaskRepository): Response
    {
        $task = $studyTaskRepository->findForUserOrThrow($id, $this->getUser());

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_task_' . $id, $token)) {
            throw $this->createAccessDeniedException('Неверный токен безопасности');
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->redirectToRoute('app_tasks');
    }

    #[Route('/task/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new StudyTask();
        $task->setUser($this->getUser());
        $task->setIsCompleted(false);

        $form = $this->createForm(StudyTaskType::class, $task, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'Задача успешно создана!');
            return $this->redirectToRoute('app_tasks');
        }

        return $this->render('task/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/api/task/quick-add', name: 'app_task_quick_add', methods: ['POST'])]
    public function quickAdd(Request $request, EntityManagerInterface $entityManager, ExamRepository $examRepository): JsonResponse
    {
        $token = $request->headers->get('X-CSRF-TOKEN');
        
        if (!$token || !$this->isCsrfTokenValid('task_action', $token)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Неверный токен безопасности'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['title'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Введите название задачи'], 400);
        }

        $task = new StudyTask();
        $task->setUser($this->getUser());
        $task->setTitle($data['title']);
        $task->setScheduledDate(new \DateTimeImmutable($data['date'] ?? 'now'));
        $task->setIsCompleted(false);

        if (!empty($data['examId'])) {
            $exam = $examRepository->findForUser($data['examId'], $this->getUser());
            if (!$exam) {
                return new JsonResponse(['status' => 'error', 'message' => 'Экзамен не найден'], 404);
            }
            $task->setExam($exam);
        }

        $entityManager->persist($task);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Задача добавлена']);
    }

    #[Route('/api/tasks/delete-bulk', name: 'app_tasks_delete_bulk', methods: ['POST'])]
    public function deleteBulk(Request $request, EntityManagerInterface $entityManager, StudyTaskRepository $studyTaskRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $taskIds = $data['taskIds'] ?? [];

        if (empty($taskIds)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Не выбраны задачи'], 400);
        }

        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$token || !$this->isCsrfTokenValid('task_action', $token)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Неверный токен безопасности'], 403);
        }

        $tasks = $studyTaskRepository->findByIdsForUser($taskIds, $this->getUser());

        foreach ($tasks as $task) {
            $entityManager->remove($task);
        }

        $entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Задачи удалены']);
    }
}