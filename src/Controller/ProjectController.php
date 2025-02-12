<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAll();
        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/project/new', name: 'project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $project = new Project();
            $project->setTitle($request->request->get('title'));
            $project->setDescription($request->request->get('description'));
            $project->setInitiator($this->getUser());
            $project->setCreatedAt(new \DateTime());

            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/new.html.twig');
    }

    #[Route('/project/{id}', name: 'project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/project/{id}/edit', name: 'project_edit', methods: ['GET', 'POST'])]
    public function edit(Project $project, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($project->getInitiator() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to edit this project.');
        }

        if ($request->isMethod('POST')) {
            $project->setTitle($request->request->get('title'));
            $project->setDescription($request->request->get('description'));
            $project->setUpdatedAt(new \DateTime());

            $entityManager->flush();

            return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/project/{id}/delete', name: 'project_delete', methods: ['POST'])]
    public function delete(Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($project->getInitiator() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to delete this project.');
        }

        $entityManager->remove($project);
        $entityManager->flush();

        return $this->redirectToRoute('project_index');
    }
}