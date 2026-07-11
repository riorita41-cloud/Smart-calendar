<?php

namespace App\Controller;

use App\Entity\ExamMaterial;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MaterialsController extends AbstractController
{
    #[Route('/materials', name: 'app_materials')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException('Необходимо авторизоваться');
        }
        
        $materials = $entityManager->getRepository(ExamMaterial::class)->findBy(
            ['user' => $user],
            ['uploadedAt' => 'DESC']
        );
        
        if ($request->isMethod('POST')) {
            $manualContent = $request->request->get('manualContent');
            $files = $request->files->all('files');
            
            $saved = false;
            
            // Обработка ручного ввода
            if (!empty($manualContent)) {
                $material = new ExamMaterial();
                $material->setName('Ручной ввод');
                $material->setContent($manualContent);
                $material->setFileType('manual');
                $material->setFilePath(null);
                $material->setUploadedAt(new \DateTime());
                $material->setUser($user);
                $entityManager->persist($material);
                $saved = true;
            }
            
            // Обработка файлов (может быть один файл или массив)
            if ($files) {
                // Если это не массив - превращаем в массив
                if (!is_array($files)) {
                    $files = [$files];
                }
                
                foreach ($files as $file) {
                    if ($file instanceof UploadedFile && $file->isValid()) {
                        try {
                            $this->processFile($file, $user, $entityManager);
                            $saved = true;
                        } catch (\Exception $e) {
                            $this->addFlash('error', 'Ошибка загрузки: ' . $e->getMessage());
                        }
                    }
                }
            }
            
            if ($saved) {
                $entityManager->flush();
                $this->addFlash('success', 'Материалы загружены!');
            } else {
                $this->addFlash('error', 'Ничего не выбрано для загрузки');
            }
            
            return $this->redirectToRoute('app_materials');
        }
        
        return $this->render('materials/index.html.twig', [
            'materials' => $materials,
        ]);
    }
    
    private function processFile(UploadedFile $file, $user, EntityManagerInterface $entityManager)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->guessExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION) ?: 'txt';
        $newName = uniqid() . '.' . $extension;
        
        $uploadDir = $this->getParameter('kernel.project_dir') . '/uploads/materials';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $file->move($uploadDir, $newName);
        
        $material = new ExamMaterial();
        $material->setName($originalName);
        $material->setFileType($extension);
        $material->setFilePath('uploads/materials/' . $newName);
        $material->setUploadedAt(new \DateTime());
        $material->setUser($user);
        
        $entityManager->persist($material);
    }
}
