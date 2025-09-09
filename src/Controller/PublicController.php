<?php

// src/Controller/PublicController.php
namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentTypeForm as StudentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\StudentRepository;
use Symfony\Component\Uid\Uuid;

class PublicController extends AbstractController
{
    #[Route('/add-student', name: 'public_add_student')]
    public function addStudent(Request $request, EntityManagerInterface $em, StudentRepository $studentRepository): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $student->setQr(Uuid::v4()->toRfc4122());

                $em->persist($student);
                $em->flush();

                // $this->addFlash('success', 'Student added successfully.');
                return $this->redirectToRoute('public_student_list');
            } else {
                $this->addFlash('danger', 'There was an error adding the student. Please check for duplicate name or missing fields.');
            }
        }

        return $this->render('public/add_student.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/students-list', name: 'public_student_list')]
    public function listStudents(StudentRepository $studentRepository): Response
    {
        $students = $studentRepository->findAll();
        return $this->render('public/index.html.twig', [
            'students' => $students,
        ]);
    }
}
