<?php

namespace App\Controller;

use App\Entity\StudyTask;
use App\Entity\Exam;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{

    #[Route('/api/task/{id}/toggle', name: 'app_task_toggle', methods: ['POST'])]
    public function toggle(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('task_action', $token)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Неверный токен'], 403);
        }

        $task = $entityManager->getRepository(StudyTask::class)->find($id);
        if (!$task || $task->getUser() !== $this->getUser()) {
            return new JsonResponse(['status' => 'error', 'message' => 'Задача не найдена'], 404);
        }
        
        $task->setIsCompleted(!$task->isCompleted());
        $entityManager->flush();
        
        return new JsonResponse(['status' => 'success']);
    }
    
    #[Route('/api/task/quick-add', name: 'app_task_quick_add', methods: ['POST'])]
    public function quickAdd(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('task_action', $token)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Неверный CSRF-токен'], 403);
        }

        $data = json_decode($request->getContent(), true);
        
        $task = new StudyTask();
        $task->setUser($this->getUser());
        $task->setTitle($data['title'] ?? 'Без названия');
        
        try {
            $task->setScheduledDate(new \DateTimeImmutable($data['date'] ?? 'now'));
        } catch (\Exception $e) {
            $task->setScheduledDate(new \DateTimeImmutable());
        }

        if (!empty($data['examId'])) {
            $exam = $entityManager->getRepository(Exam::class)->find($data['examId']);
            if ($exam && $exam->getUser() === $this->getUser()) {
                $task->setExam($exam);
            }
        }

        $entityManager->persist($task);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success']);
    }
}