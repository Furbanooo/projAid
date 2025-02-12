<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Comment;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\Donation;
use App\Entity\User;

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
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $project = new Project();
            $title = $request->request->get('title');
            
            $project->setTitle($title);
            $project->setName($title); // Set name same as title
            $project->setDescription($request->request->get('description'));
            $project->setInitiator($this->getUser());
            $project->setCreatedAt(new \DateTime());
            $project->setCreationDate(new \DateTime());

            // Handle file uploads
            $contentType = $request->request->get('content_type');
            
            if ($contentType === 'image') {
                $images = $request->files->get('images');
                if ($images) {
                    $imagePaths = [];
                    foreach ($images as $image) {
                        $filename = md5(uniqid()) . '.' . $image->guessExtension();
                        $image->move(
                            $this->getParameter('project_images_directory'),
                            $filename
                        );
                        $imagePaths[] = $filename;
                    }
                    $project->setContent(['type' => 'image', 'files' => $imagePaths]);
                }
            } else {
                $video = $request->files->get('video');
                if ($video) {
                    $filename = md5(uniqid()) . '.' . $video->guessExtension();
                    $video->move(
                        $this->getParameter('project_videos_directory'),
                        $filename
                    );
                    $project->setContent(['type' => 'video', 'file' => $filename]);
                }
            }

            $entityManager->persist($project);
            $entityManager->flush();

            $this->addFlash('success', 'Project created successfully!');
            return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/new.html.twig');
    }

    #[Route('/project/{id}', name: 'project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project
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
            $this->addFlash('success', 'Project updated successfully!');
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

        $this->addFlash('success', 'Project deleted successfully!');
        return $this->redirectToRoute('project_index');
    }

    #[Route('/project/{id}/comment', name: 'project_comment', methods: ['POST'])]
    public function addComment(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            throw new AccessDeniedException('You must be logged in to comment.');
        }

        $content = trim($request->request->get('content'));
        if (!empty($content)) {
            $comment = new Comment();
            $comment->setContent($content);
            $comment->setProject($project);
            $comment->setAuthor($this->getUser());
            $comment->setCreatedAt(new \DateTime());

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Comment added successfully!');
        }

        return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
    }

    #[Route('/project/{id}/contribute', name: 'project_contribute', methods: ['GET'])]
    public function contribute(Project $project, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user === $project->getInitiator()) {
            $this->addFlash('info', 'You cannot contribute to your own project.');
            return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
        }

        // Check if user is not already a contributor
        if (!$project->getContributors()->contains($user)) {
            $project->addContributor($user);
            $entityManager->persist($project);
            $entityManager->flush();
            
            $this->addFlash('success', 'You are now contributing to this project!');
        } else {
            $this->addFlash('info', 'You are already contributing to this project.');
        }

        return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
    }

    #[Route('/project/{id}/donate', name: 'project_donate', methods: ['POST'])]
    public function donate(Project $project, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $amount = (float) $request->request->get('amount');
        
        if ($amount < 1 || $amount > 10000) {
            $this->addFlash('error', 'Donation amount must be between 1 and 10000 units.');
            return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
        }

        $donation = new Donation();
        $donation->setProject($project);
        $donation->setContributor($this->getUser());
        $donation->setAmount($amount);

        $entityManager->persist($donation);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Thank you for your donation of %.2f units!', $amount));
        return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
    }
}