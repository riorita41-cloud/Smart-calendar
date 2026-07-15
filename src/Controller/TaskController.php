<?php

namespace App\Controller;

use App\Entity\StudyTask;
use App\Entity\Exam;
use App\Form\StudyTaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'app_tasks')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $tasks = $entityManager->getRepository(StudyTask::class)->findBy(
            ['user' => $this->getUser()],
            ['scheduledDate' => 'ASC']
        );

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/task/{id}/toggle', name: 'app_task_toggle', methods: ['POST'])]
    public function toggle(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $task = $entityManager->getRepository(StudyTask::class)->find($id);

        if (!$task || $task->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Задача не найдена');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('toggle_task_' . $id, $token)) {
            throw $this->createAccessDeniedException('Неверный токен безопасности');
        }

        $task->setIsCompleted(!$task->isCompleted());
        $entityManager->flush();

        return $this->redirectToRoute('app_tasks');
    }

    #[Route('/task/{id}/delete', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $task = $entityManager->getRepository(StudyTask::class)->find($id);

        if (!$task || $task->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Задача не найдена');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_task_' . $id, $token)) {
            throw $this->createAccessDeniedException('Неверный токен безопасности');
        }

        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash('success', 'Задача успешно удалена');
        return $this->redirectToRoute('app_tasks');
    }

    #[Route('/task/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new StudyTask();
        $task->setUser($this->getUser());
        $task->setIsCompleted(false);

        $form = $this->createForm(\App\Form\StudyTaskType::class, $task);
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
    public function quickAdd(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['title']) || empty($data['examId'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Заполните все поля'], 400);
        }

        $exam = $entityManager->getRepository(Exam::class)->find($data['examId']);
        if (!$exam || $exam->getUser() !== $this->getUser()) {
            return new JsonResponse(['status' => 'error', 'message' => 'Экзамен не найден'], 404);
        }

        $task = new StudyTask();
        $task->setUser($this->getUser());
        $task->setExam($exam);
        $task->setTitle($data['title']);
        $task->setScheduledDate(new \DateTimeImmutable($data['date'] ?? 'now'));
        $task->setIsCompleted(false);

        $entityManager->persist($task);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Задача добавлена']);
    }
}