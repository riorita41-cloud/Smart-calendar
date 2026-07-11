<?php

namespace App\Controller;

use App\Entity\ExamMaterial;
use App\Entity\Exam;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExamsController extends AbstractController
{
    #[Route('/exams', name: 'app_exams')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Получаем экзамены пользователя
        $exams = $entityManager->getRepository(Exam::class)->findBy(
            ['user' => $user]
        );
        
        // Получаем материалы пользователя
        $materials = $entityManager->getRepository(ExamMaterial::class)->findBy(
            ['user' => $user],
            ['uploadedAt' => 'DESC']
        );
        
        return $this->render('exams/index.html.twig', [
            'exams' => $exams,
            'materials' => $materials,
        ]);
    }
    
    #[Route('/materials/view/{id}', name: 'app_materials_view')]
    public function view(ExamMaterial $material, EntityManagerInterface $entityManager): Response
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
