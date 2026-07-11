<?php

namespace App\Controller;

use App\Entity\Exam;
use App\Form\ExamType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExamController extends AbstractController
{
    #[Route('/exams', name: 'app_exams')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        $user = $this->getUser();
        $exams = $user->getExams();
        
        return $this->render('exam/index.html.twig', [
            'exams' => $exams,
        ]);
    }
    
    #[Route('/exam/new', name: 'app_exam_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        $exam = new Exam();
        $exam->setUser($this->getUser());
        
        $form = $this->createForm(ExamType::class, $exam);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exam);
            $entityManager->flush();
            
            $this->addFlash('success', 'Exam created successfully!');
            return $this->redirectToRoute('app_exams');
        }
        
        return $this->render('exam/new.html.twig', [
            'form' => $form,
        ]);
    }
    
    #[Route('/exam/{id}/delete', name: 'app_exam_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        $exam = $entityManager->getRepository(Exam::class)->find($id);
        
        if (!$exam || $exam->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Exam not found');
        }
        
        $entityManager->remove($exam);
        $entityManager->flush();
        
        $this->addFlash('success', 'Exam deleted');
        return $this->redirectToRoute('app_exams');
    }
}
