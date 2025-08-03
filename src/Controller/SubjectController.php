<?php

namespace App\Controller;

use App\Repository\SchoolClassRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\SchoolClassTypeForm;
use App\Entity\SchoolClass as SchoolClass;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class SubjectController extends AbstractController
{
    #[Route('/subject', name: 'app_subject')]
    public function index(Request $request, EntityManagerInterface $em, SchoolClassRepository $schoolclassRepository): Response
    {
        $schoolClasses = $schoolclassRepository->findAll();

        $schoolClass = new SchoolClass();
        $form = $this->createForm(SchoolClassTypeForm::class, $schoolClass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($schoolClass);
            $em->flush();

            $this->addFlash('success', 'Subject added successfully.');

            // redirect or render as needed
            return $this->redirectToRoute('app_subject', []);
        } elseif ($form->isSubmitted()) {
            // ❌ Flash error message
            $this->addFlash('danger', 'There was an error adding the subject. Please check for duplicate name or missing fields.');
        }

        $editForms = [];
        foreach ($schoolClasses as $s) {
            $editForms[$s->getId()] = $this->createForm(SchoolClassTypeForm::class, $s, [
                'action' => $this->generateUrl('app_subject_edit', ['id' => $s->getId()]),
                'method' => 'POST',
            ])->createView();
        }

        return $this->render('subject/index.html.twig', [
            'controller_name' => 'SubjectController',
            'form' => $form->createView(),
            'schoolClasses' => $schoolClasses,
            'editForms' => $editForms,
        ]);
    }

    #[Route('/subject/{id}/edit', name: 'app_subject_edit', methods: ['POST'])]
    public function edit(Request $request, SchoolClass $schoolClass, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SchoolClassTypeForm::class, $schoolClass, [
            'action' => $this->generateUrl('app_subject_edit', ['id' => $schoolClass->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Subject updated successfully.');
            return $this->redirectToRoute('app_subject');
        }

        $schoolClasses = $em->getRepository(SchoolClassTypeForm::class)->findAll();
        $editForms = [];
        foreach ($schoolClasses as $s) {
            $editForms[$s->getId()] = $this->createForm(SchoolClassTypeForm::class, $s, [
                'action' => $this->generateUrl('app_subject_edit', ['id' => $s->getId()]),
                'method' => 'POST',
            ]);
            $editForms[$s->getId()] = $form->createView();
        }

        $editForms[$schoolClass->getId()] = $form->createView();
        return $this->render('student/index.html.twig', [
            'schoolClasses' => $schoolClasses,
            'form' => $form->createView(),
            'editForms' => $editForms,
        ]);

        return $this->redirectToRoute('app_subject'); // fallback
    }

    #[Route('/subject/{id}/delete', name: 'app_subject_delete', methods: ['POST'])]
    public function delete(Request $request, SchoolClass $schoolClass, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $schoolClass->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($schoolClass);
            $entityManager->flush();
            $this->addFlash('success', 'Subject deleted successfully.');
        } else {
            $this->addFlash('danger', 'Invalid CSRF token. Subject deletion failed.');
        }

        return $this->redirectToRoute('app_subject', [], Response::HTTP_SEE_OTHER);
    }
}
