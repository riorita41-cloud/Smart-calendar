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

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'app_tasks')]
    public function index(): Response
    {
        $user = $this->getUser();
        $tasks = $user->getStudyTasks();
        
        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }
    
    #[Route('/task/new', name: 'app_task_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new StudyTask();
        $task->setUser($this->getUser());
        
        $form = $this->createForm(\App\Form\StudyTaskType::class, $task);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();
            
            $this->addFlash('success', 'Задача создана!');
            return $this->redirectToRoute('app_tasks');
        }
        
        return $this->render('task/new.html.twig', [
            'form' => $form,
        ]);
    }
    
    #[Route('/task/{id}/toggle', name: 'app_task_toggle', methods: ['POST'])]
    public function toggle(int $id, EntityManagerInterface $entityManager): Response
    {
        $task = $entityManager->getRepository(StudyTask::class)->find($id);
        
        if (!$task || $task->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Задача не найдена');
        }
        
        $task->setIsCompleted(!$task->isCompleted());
        $entityManager->flush();
        
        return $this->redirectToRoute('app_tasks');
    }
    
    #[Route('/task/{id}/delete', name: 'app_task_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
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
    $data = json_decode($request->getContent(), true);

    $exam = $entityManager->getRepository(\App\Entity\Exam::class)->find($data['examId']);

    if (!$exam || $exam->getUser() !== $this->getUser()) {
        return new JsonResponse(['status' => 'error', 'message' => 'Экзамен не найден!'], 400);
    }

    $task = new \App\Entity\StudyTask();
    $task->setUser($this->getUser());
    $task->setTitle($data['title']);
    $task->setScheduledDate(new \DateTimeImmutable($data['date']));
    $task->setExam($exam);

    $entityManager->persist($task);
    $entityManager->flush();

    return new JsonResponse(['status' => 'success']);
}
}