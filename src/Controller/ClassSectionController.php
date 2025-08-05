<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Form\ClassSectionTypeForm;
use App\Entity\SchoolClass;
use App\Entity\ClassSection;
use App\Form\SchoolClassTypeForm;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;


final class ClassSectionController extends AbstractController
{
    #[Route('/subject/{id}/sections', name: 'app_subject_sections')]
    public function manageSections(Request $request, SchoolClass $schoolClass, EntityManagerInterface $em): Response
    {
        $section = new ClassSection();
        $section->setClass($schoolClass);

        $form = $this->createForm(ClassSectionTypeForm::class, $section, [
            'section' => $section,
            'hide_students_field' => true, // Hide student name field in this form
            'school_class' => $schoolClass,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure students are linked both ways
            foreach ($section->getStudents() as $student) {
                $student->addClassSection($section);
            }
            $em->persist($section);
            $em->flush();
            $this->addFlash('success', 'Section added.');
            return $this->redirectToRoute('app_subject_sections', ['id' => $schoolClass->getId()]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'There was an error adding the section. Please check for duplicate name or missing fields.');
        }

        $editForms = [];
        foreach ($schoolClass->getClassSections() as $s) {
            $editForms[$s->getId()] = $this->createForm(ClassSectionTypeForm::class, $s, [
                'action' => $this->generateUrl('app_section_edit', ['id' => $s->getId()]),
                'method' => 'POST',
                // 'hide_section_name' => true,
                'hide_students_field' => true, // Hide student name field in this form
            ])->createView();
        }

        return $this->render('class_section/index.html.twig', [
            'controller_name' => 'Section',
            'schoolClass' => $schoolClass,
            'sections' => $schoolClass->getClassSections(),
            'form' => $form->createView(),
            'editForms' => $editForms,
        ]);
    }

    #[Route('/subject/{id}/sections/{sectionId}/list_of_students', name: 'app_subject_sections_list_of_students')]
    public function listOfStudents(
        #[MapEntity(id: 'id')] SchoolClass $schoolClass,
        #[MapEntity(id: 'sectionId')] ClassSection $section,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $form = $this->createForm(ClassSectionTypeForm::class, $section, [
            'hide_section_name' => true, // Hide section name field in this form
            'school_class' => $schoolClass,
            'hide_time_in' => true, // Hide time-in field in this form
            'section' => $section,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure students are linked both ways
            foreach ($section->getStudents() as $student) {
                $student->addClassSection($section);
            }
            $em->persist($section);
            $em->flush();
            $this->addFlash('success', 'Student added.');
            return $this->redirectToRoute('app_subject_sections', ['id' => $schoolClass->getId()]);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'There was an error adding the student. Please check for duplicate name or missing fields.');
        }

        return $this->render('class_section/student_list.html.twig', [
            'controller_name' => 'Section',
            'schoolClass' => $schoolClass,
            'form' => $form->createView(),
            'section' => $section,
        ]);
    }

    #[Route('/section/{id}/edit', name: 'app_section_edit', methods: ['POST'])]
    public function edit(Request $request, ClassSection $section, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ClassSectionTypeForm::class, $section, [
            'action' => $this->generateUrl('app_section_edit', ['id' => $section->getId()]),
            'method' => 'POST',
            'hide_students_field' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Section updated successfully.');
            return $this->redirectToRoute('app_subject_sections', ['id' => $section->getClass()->getId()]);
        }

        return $this->render('class_section/index.html.twig', [
            'section' => $section,
            'form' => $form->createView(),
        ]);
    }
}
