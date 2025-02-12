<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/project/{id}/comment', name: 'comment_new', methods: ['POST'])]
    public function new(Project $project, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $comment->setContent($request->request->get('content'));
        $comment->setCreatedAt(new \DateTime());
        $comment->setProject($project);
        $comment->setAuthor($this->getUser());

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
    }

    #[Route('/comment/{id}/edit', name: 'comment_edit', methods: ['GET', 'POST'])]
    public function edit(Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($comment->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to edit this comment.');
        }

        if ($request->isMethod('POST')) {
            $comment->setContent($request->request->get('content'));
            $entityManager->flush();

            return $this->redirectToRoute('project_show', ['id' => $comment->getProject()->getId()]);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/comment/{id}/delete', name: 'comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if ($comment->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to delete this comment.');
        }

        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('project_show', ['id' => $comment->getProject()->getId()]);
    }
}