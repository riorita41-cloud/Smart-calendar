<?php

namespace App\Controller;

use App\Entity\ExamMaterial;
use App\Entity\Question;
use App\Form\ExamMaterialType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MaterialsController extends AbstractController
{
    #[Route('/materials', name: 'app_materials')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        $materials = $entityManager->getRepository(ExamMaterial::class)->findBy(
            ['user' => $user],
            ['uploadedAt' => 'DESC']
        );
        
        return $this->render('materials/index.html.twig', [
            'materials' => $materials,
        ]);
    }

    #[Route('/materials/new', name: 'app_material_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $material = new ExamMaterial();
        $material->setUser($this->getUser());
        $material->setFileType('manual');
        $material->setUploadedAt(new \DateTime());

        $form = $this->createForm(ExamMaterialType::class, $material);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($material);
            $entityManager->flush();

            $this->addFlash('success', 'Материал создан! Теперь добавьте в него вопросы.');
            return $this->redirectToRoute('app_questions_add', ['id' => $material->getId()]);
        }

        return $this->render('materials/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/materials/{id}/questions', name: 'app_questions_add')]
    public function addQuestions(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $material = $entityManager->getRepository(ExamMaterial::class)->find($id);
        
        if (!$material || $material->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Материал не найден');
        }

        if ($request->isMethod('POST')) {
            $questionsText = $request->request->get('questions');
            
            $lines = array_filter(array_map('trim', explode("\n", $questionsText)));
            
            $maxOrder = 0;
            foreach ($material->getQuestions() as $q) {
                if ($q->getOrderNumber() > $maxOrder) {
                    $maxOrder = $q->getOrderNumber();
                }
            }
            $orderNumber = $maxOrder + 1;
            
            $addedCount = 0;
            foreach ($lines as $line) {
                if (empty($line)) continue;
                
                if (preg_match('/^(.+?)(?:Ответ:|\|)\s*(.+)$/i', $line, $matches)) {
                    $questionText = trim($matches[1]);
                    $answer = trim($matches[2]);
                } else {
                    $questionText = $line;
                    $answer = null;
                }
                
                $question = new Question();
                $question->setText($questionText);
                $question->setAnswer($answer);
                $question->setOrderNumber($orderNumber++);
                
                $material->addQuestion($question);
                $addedCount++;
            }
            
            $entityManager->persist($material);
            $entityManager->flush();
            
            $this->addFlash('success', "Успешно добавлено вопросов: {$addedCount}");
            return $this->redirectToRoute('app_questions_add', ['id' => $material->getId()]);
        }

        return $this->render('materials/add_questions.html.twig', [
            'material' => $material,
        ]);
    }

    #[Route('/materials/{id}/view', name: 'app_material_view')]
    public function view(int $id, EntityManagerInterface $entityManager): Response
    {
        $material = $entityManager->getRepository(ExamMaterial::class)->find($id);
        
        if (!$material || $material->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Материал не найден');
        }

        $questions = $material->getQuestions()->toArray();
        usort($questions, fn($a, $b) => $a->getOrderNumber() <=> $b->getOrderNumber());

        return $this->render('materials/view.html.twig', [
            'material' => $material,
            'questions' => $questions,
        ]);
    }

    #[Route('/materials/{id}/delete', name: 'app_material_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $material = $entityManager->getRepository(ExamMaterial::class)->find($id);
        
        if (!$material || $material->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Материал не найден');
        }

        if (!$this->isCsrfTokenValid('delete' . $material->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Неверный токен безопасности');
        }

        $entityManager->remove($material); 
        $entityManager->flush();

        $this->addFlash('success', 'Материал и все его вопросы удалены');
        return $this->redirectToRoute('app_materials');
    }
}