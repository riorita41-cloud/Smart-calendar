<?php

namespace App\Controller;

use App\Entity\Avatar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AvatarController extends AbstractController
{
    #[Route('/avatar', name: 'app_avatar')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        $avatar = $user->getAvatar();
        if (!$avatar) {
            $avatar = new Avatar();
            $avatar->setUser($user);
            $avatar->setSeed($user->getEmail());
            $avatar->setSkinColor('edb98a');
            $avatar->setHairColor('724133');
            $avatar->setHairStyle('long01');
            $entityManager->persist($avatar);
            $entityManager->flush();
        }
        
        return $this->render('avatar/index.html.twig', [
            'avatar' => $avatar,
        ]);
    }
    
    #[Route('/avatar/save', name: 'app_avatar_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->geЫt('_token');
        if (!$this->isCsrfTokenValid('avatar_save', $submittedToken)) {
            throw $this->createAccessDeniedException('Неверный CSRF-токен.');
        }
        
        $user = $this->getUser();
        $avatar = $user->getAvatar() ?? (new Avatar())->setUser($user);
        
        $avatar->setSkinColor($request->request->get('skinColor', 'edb98a'));
        $avatar->setHairColor($request->request->get('hairColor', '724133'));
        $avatar->setHairStyle($request->request->get('hair', 'long01'));
        
        $seed = $request->request->get('seed') ?: $user->getEmail();
        $avatar->setSeed($seed);
        
        $entityManager->persist($avatar);
        $entityManager->flush();
        
        $this->addFlash('success', 'Аватар сохранён!');
        return $this->redirectToRoute('app_avatar');
    }
}