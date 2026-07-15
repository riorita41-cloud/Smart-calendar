<?php

namespace App\Controller;

use App\Entity\StudyTask;
use App\Form\StudyTaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Exam;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'app_tasks')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $tasks = $entityManager->getRepository(StudyTask::class)->findBy(['user' => $this->getUser()]);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/task/new', name: 'app_task_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new StudyTask();
        $task->setUser($this->getUser());

        $form = $this->createForm(StudyTaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'Задача успешно добавлена!');
            return $this->redirectToRoute('app_tasks');
        }

        return $this->render('task/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/task/{id}/toggle', name: 'app_task_toggle', methods: ['POST'])]
    public function toggle(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('toggle_task_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Неверный токен');
        }

        $task = $entityManager->getRepository(StudyTask::class)->find($id);
        
        if (!$task || $task->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Задача не найдена');
        }
        
        $task->setIsCompleted(!$task->isCompleted());
        $entityManager->flush();
        
        return $this->redirectToRoute('app_tasks');
    }
    
    #[Route('/task/{id}/delete', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete_task_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Неверный токен');
        }

        $task = $entityManager->getRepository(StudyTask::class)->find($id);
        
        if (!$task || $task->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Задача не найдена');
        }
        
        $entityManager->remove($task);
        $entityManager->flush();
        
        $this->addFlash('success', 'Задача удалена');
        return $this->redirectToRoute('app_tasks');
    }

    #[Route('/api/task/quick-add', name: 'app_task_quick_add', methods: ['POST'])]
    public function quickAdd(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('quick_add_task', $token)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Неверный CSRF-токен'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $exam = $entityManager->getRepository(Exam::class)->find($data['examId'] ?? null);

        if (!$exam || $exam->getUser() !== $this->getUser()) {
            return new JsonResponse(['status' => 'error', 'message' => 'Экзамен не найден'], 400);
        }

        $task = new StudyTask();
        $task->setUser($this->getUser());
        $task->setTitle($data['title'] ?? '');
        $task->setScheduledDate(new \DateTimeImmutable($data['date'] ?? 'now'));
        $task->setExam($exam);

        $entityManager->persist($task);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success']);
    }
}