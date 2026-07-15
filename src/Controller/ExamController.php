<?php

namespace App\Controller;

use App\Entity\Exam;
use App\Entity\ExamMaterial;
use App\Form\ExamType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExamController extends AbstractController
{
    #[Route('/exams', name: 'app_exams')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $exams = $entityManager->getRepository(Exam::class)->findBy(['user' => $user]);
        $materials = $entityManager->getRepository(ExamMaterial::class)->findBy(
            ['user' => $user],
            ['uploadedAt' => 'DESC']
        );

        return $this->render('exams/index.html.twig', [
            'exams' => $exams,
            'materials' => $materials,
        ]);
    }

    #[Route('/exam/new', name: 'app_exam_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $exam = new Exam();
        $exam->setUser($user);

        $form = $this->createForm(ExamType::class, $exam);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exam);
            $entityManager->flush();

            $this->addFlash('success', 'Экзамен успешно создан!');
            return $this->redirectToRoute('app_exams');
        }

        return $this->render('exam/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/exam/{id}/delete', name: 'app_exam_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_exam_' . $id, $submittedToken)) {
            throw $this->createAccessDeniedException('Ошибка безопасности: неверный CSRF-токен.');
        }

        $user = $this->getUser();
        $exam = $entityManager->getRepository(Exam::class)->find($id);

        if (!$exam || $exam->getUser() !== $user) {
            throw $this->createNotFoundException('Экзамен не найден');
        }

        $entityManager->remove($exam);
        $entityManager->flush();

        $this->addFlash('success', 'Экзамен удален');
        return $this->redirectToRoute('app_exams');
    }

    #[Route('/materials/view/{id}', name: 'app_materials_view')]
    public function view(ExamMaterial $material): Response
    {
        if ($material->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        if ($material->getFileType() === 'manual') {
            return $this->render('exams/view_material.html.twig', [
                'material' => $material,
            ]);
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/' . $material->getFilePath();
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Файл не найден');
        }

        return $this->file($filePath, $material->getName());
    }
}