<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LikeController extends AbstractController
{
    #[Route('/project/{id}/like', name: 'like_new', methods: ['POST'])]
    public function new(Project $project, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        foreach ($project->getLikes() as $like) {
            if ($like->getUser() === $user) {
                $this->addFlash('error', 'You have already liked this project.');
                return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
            }
        }

        $like = new Like();
        $like->setProject($project);
        $like->setUser($user);

        $entityManager->persist($like);
        $entityManager->flush();

        return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
    }
}