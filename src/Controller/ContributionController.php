<?php

namespace App\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContributionController extends AbstractController
{
    #[Route('/project/{id}/contribute', name: 'contribution_new', methods: ['POST'])]
    public function contribute(Project $project, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($project->getContributors()->contains($user)) {
            $this->addFlash('error', 'You are already a contributor to this project.');
            return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
        }

        $project->addContributor($user);
        $entityManager->flush();

        return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
    }
}