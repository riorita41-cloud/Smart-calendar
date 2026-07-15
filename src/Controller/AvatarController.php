<?php

namespace App\Controller;

use App\Repository\AvatarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AvatarController extends AbstractController
{
    #[Route('/avatar', name: 'app_avatar')]
    public function index(AvatarRepository $avatarRepository): Response
    {
        $avatar = $avatarRepository->findOrCreateForUser($this->getUser());
        
        return $this->render('avatar/index.html.twig', [
            'avatar' => $avatar,
        ]);
    }
    
    #[Route('/avatar/save', name: 'app_avatar_save', methods: ['POST'])]
    public function save(Request $request, AvatarRepository $avatarRepository, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('avatar_save', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Неверный CSRF-токен.');
        }
        
        $avatar = $avatarRepository->findOrCreateForUser($this->getUser());
        
        $avatar->setSkinColor(preg_match('/^[a-f0-9]{6}$/i', $request->request->get('skinColor')) ? $request->request->get('skinColor') : 'edb98a');
        $avatar->setHairColor($request->request->get('hairColor', '724133'));
        $avatar->setHairStyle($request->request->get('hair', 'long01'));
        $avatar->setSeed($request->request->get('seed') ?: $this->getUser()->getEmail());
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Аватар сохранён!');
        return $this->redirectToRoute('app_avatar');
    }
}