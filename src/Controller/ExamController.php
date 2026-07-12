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
    // --- БЛОК: Управление экзаменами (из первого контроллера) ---

    #[Route('/exams', name: 'app_exams')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Проверка: если пользователь не авторизован, отправляем на логин
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Получаем экзамены пользователя
        $exams = $entityManager->getRepository(Exam::class)->findBy(
            ['user' => $user]
        );

        // Получаем материалы пользователя (из второго контроллера)
        $materials = $entityManager->getRepository(ExamMaterial::class)->findBy(
            ['user' => $user],
            ['uploadedAt' => 'DESC']
        );

        return $this->render('exams/index.html.twig', [
            'exams' => $exams,
            'materials' => $materials, // Передаем и материалы тоже
        ]);
    }

    #[Route('/exam/new', name: 'app_exam_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $exam = new Exam();
        // Привязываем экзамен к текущему пользователю
        $exam->setUser($user);

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
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $exam = $entityManager->getRepository(Exam::class)->find($id);

        // Проверка безопасности: существует ли экзамен и принадлежит ли он текущему пользователю
        if (!$exam || $exam->getUser() !== $user) {
            throw $this->createNotFoundException('Exam not found');
        }

        $entityManager->remove($exam);
        $entityManager->flush();

        $this->addFlash('success', 'Exam deleted');
        return $this->redirectToRoute('app_exams');
    }

    // --- БЛОК: Работа с материалами (из второго контроллера) ---

    #[Route('/materials/view/{id}', name: 'app_materials_view')]
    public function view(ExamMaterial $material): Response
    {
        // Проверка безопасности: доступ разрешен только владельцу материала
        if ($material->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        // Если это "ручная" ссылка (текст), показываем шаблон
        if ($material->getFileType() === 'manual') {
            return $this->render('exams/view_material.html.twig', [
                'material' => $material,
            ]);
        }

        // Если это файл, отдаем его для скачивания
        $filePath = $this->getParameter('kernel.project_dir') . '/' . $material->getFilePath();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Файл не найден');
        }

        // Используем встроенный метод Symfony для скачивания файлов
        return $this->file($filePath, $material->getName());
    }
}