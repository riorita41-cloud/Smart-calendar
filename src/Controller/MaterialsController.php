<?php

namespace App\Controller;

use App\Entity\ExamMaterial;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
        
        $materials = $entityManager->getRepository(ExamMaterial::class)->findBy(
            ['user' => $user],
            ['uploadedAt' => 'DESC']
        );
        
        if ($request->isMethod('POST')) {
            // Проверка CSRF токена
            if (!$this->isCsrfTokenValid('materials_upload', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Неверный CSRF-токен');
            }

            $manualContent = $request->request->get('manualContent');
            $files = $request->files->all('files');
            $saved = false;
            
            if (!empty($manualContent)) {
                $material = new ExamMaterial();
                $material->setName('Ручной ввод');
                $material->setContent($manualContent);
                $material->setFileType('manual');
                $material->setUploadedAt(new \DateTime());
                $material->setUser($user);
                $entityManager->persist($material);
                $saved = true;
            }
            
            if ($files) {
                if (!is_array($files)) { $files = [$files]; }
                
                foreach ($files as $file) {
                    if ($file instanceof UploadedFile && $file->isValid()) {
                        try {
                            $this->processFile($file, $user, $entityManager);
                            $saved = true;
                        } catch (\Exception $e) {
                            $this->addFlash('error', 'Ошибка файла: ' . $e->getMessage());
                        }
                    }
                }
            }
            
            if ($saved) {
                $entityManager->flush();
                $this->addFlash('success', 'Материалы успешно загружены!');
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
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $mimeType = $file->getMimeType();
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception('Недопустимый формат файла: ' . $mimeType);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \Exception('Файл слишком большой (макс. 5МБ).');
        }

        $originalName = $file->getClientOriginalName();
        $extension = $file->guessExtension() ?: 'bin';
        $newName = uniqid('mat_', true) . '.' . $extension;
        
        $uploadDir = $this->getParameter('kernel.project_dir') . '/uploads/materials';
        
        try {
            $file->move($uploadDir, $newName);
        } catch (FileException $e) {
            throw new \Exception('Не удалось сохранить файл на сервере.');
        }
        
        $material = new ExamMaterial();
        $material->setName($originalName);
        $material->setFileType($extension);
        $material->setFilePath('uploads/materials/' . $newName);
        $material->setUploadedAt(new \DateTime());
        $material->setUser($user);
        
        $entityManager->persist($material);
    }
}