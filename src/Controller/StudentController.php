<?php

namespace App\Controller;

use App\Entity\Student;
use App\Repository\StudentRepository;
use App\Form\StudentTypeForm as StudentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Writer\WriterInterface;




final class StudentController extends AbstractController
{

    #[Route('/student', name: 'app_student_index')]
    public function index(Request $request, EntityManagerInterface $em, StudentRepository $studentRepository): Response
    {
        $students = $studentRepository->findAll();

        // Create an empty form for the modal
        $student = new Student();
        // $form = $this->createForm(StudentType::class, $studentEntity);
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $student->setQr(Uuid::v4()->toRfc4122());

                $em->persist($student);
                $em->flush();

                $this->addFlash('success', 'Student added successfully.');

                return $this->redirectToRoute('app_student_index');
            } else {

                $this->addFlash('danger', 'There was an error adding the student. Please check for duplicate name or missing fields.');
            }
        }

        $editForms = [];
        foreach ($students as $s) {
            $editForms[$s->getId()] = $this->createForm(StudentType::class, $s, [
                'action' => $this->generateUrl('app_student_edit', ['id' => $s->getId()]),
                'method' => 'POST',
            ])->createView();
        }

        return $this->render('student/index.html.twig', [
            'students' => $students,
            'form' => $form->createView(),
            'editForms' => $editForms,
        ]);

        $qrValue = $student->getQr() ?: 'no-qr-value';

        $qrCode = new QrCode($qrValue);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            ['Content-Type' => $result->getMimeType()]
        );
    }

    #[Route('/student/{id}/json', name: 'app_student_json', methods: ['GET'])]
    public function getStudentJson(Student $student): Response
    {
        $qrUrl = $this->generateUrl('app_student_qr', ['id' => $student->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json([
            'id' => $student->getId(),
            'name' => $student->getName(),
            'course' => $student->getCourse(),
            'section' => $student->getSection(),
            'studentNumber' => $student->getStudentNumber(),
            'qr' => [
                'url' => $qrUrl,
                'label' => $student->getName()
            ]
        ]);
    }

    // #[Route('/student/{id}/qr', name: 'app_student_qr')]
    // public function generateQr(Student $student): Response
    // {
    //     $qrValue = $student->getQr() ?: 'no-qr-value';

    //     $qrCode = new QrCode($qrValue);

    //     $writer = new PngWriter();
    //     $result = $writer->write($qrCode);

    //     return new Response(
    //         $result->getString(),
    //         Response::HTTP_OK,
    //         ['Content-Type' => $result->getMimeType()]
    //     );
    // }

    #[Route('/student/{id}/qr', name: 'app_student_qr')]
    public function generateQr(Student $student): Response
    {
        $qrValue = $student->getQr() ?: 'no-qr-value';
        $studentName = $student->getName(); // Assuming getName() exists

        $qrCode = new QrCode($qrValue);
        $writer = new PngWriter();
        $qrResult = $writer->write($qrCode);

        $qrImage = imagecreatefromstring($qrResult->getString());

        // QR code dimensions
        $qrWidth = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);

        // Font settings
        $fontSize = 5; // GD font size (1–5)
        $fontWidth = imagefontwidth($fontSize);
        $fontHeight = imagefontheight($fontSize);
        $textWidth = $fontWidth * strlen($studentName);
        $padding = 30;

        // Create new image with space for QR + text
        $newHeight = $qrHeight + $fontHeight + $padding;
        $finalImage = imagecreatetruecolor($qrWidth, $newHeight);

        // White background
        $white = imagecolorallocate($finalImage, 255, 255, 255);
        imagefill($finalImage, 0, 0, $white);

        // Copy QR image
        imagecopy($finalImage, $qrImage, 0, 0, 0, 0, $qrWidth, $qrHeight);

        // Add text centered below
        $black = imagecolorallocate($finalImage, 0, 0, 0);
        $textX = ($qrWidth - $textWidth) / 2;
        $textY = $qrHeight + ($padding / 2);
        imagestring($finalImage, $fontSize, $textX, $textY, $studentName, $black);

        // Output image
        ob_start();
        imagepng($finalImage);
        $output = ob_get_clean();

        imagedestroy($qrImage);
        imagedestroy($finalImage);

        return new Response($output, Response::HTTP_OK, ['Content-Type' => 'image/png']);

        // // Clean up
        // imagedestroy($qrImage);
        // imagedestroy($finalImage);

        // // Normalize filename (e.g., replace spaces)
        // $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $studentName);

        // return new Response($output, Response::HTTP_OK, [
        //     'Content-Type' => 'image/png',
        //     'Content-Disposition' => 'attachment; filename="qr_' . $safeName . '.png"',
        // ]);
    }



    #[Route('/student/{id}/edit', name: 'app_student_edit', methods: ['POST'])]
    public function edit(Request $request, Student $student, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(StudentType::class, $student, [
            'action' => $this->generateUrl('app_student_edit', ['id' => $student->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Student updated successfully.');
            return $this->redirectToRoute('app_student_index');
        }

        $students = $em->getRepository(Student::class)->findAll();
        $editForms = [];
        foreach ($students as $s) {
            $editForms[$s->getId()] = $this->createForm(StudentType::class, $s, [
                'action' => $this->generateUrl('app_student_edit', ['id' => $s->getId()]),
                'method' => 'POST',
            ])->createView();
        }

        $editForms[$student->getId()] = $form->createView();
        return $this->render('student/index.html.twig', [
            'students' => $students,
            'form' => $this->createForm(StudentType::class, new Student())->createView(),
            'editForms' => $editForms,
        ]);

        return $this->redirectToRoute('app_student_index'); // fallback
    }


    #[Route('/student/{id}', name: 'app_student_show', requirements: ['id' => '\d+'])]
    public function show(Student $student): Response
    {
        return $this->render('student/show.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/student/new', name: 'app_student_new')]
    public function new(Request $request, EntityManagerInterface $em, StudentRepository $studentRepository): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                dd((string) $form->getErrors(true, false));
                $student->setQr(Uuid::v4()->toRfc4122());

                $em->persist($student);
                $em->flush();

                $this->addFlash('success', 'Student added successfully.');

                return $this->redirectToRoute('app_student_index');
            } else {
                $this->addFlash('danger', 'There was an error adding the student. Please check for duplicate name or missing fields.');
            }
        }

        return $this->render('student/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/student/{id}/delete', name: 'app_student_delete', methods: ['POST'])]
    public function delete(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $student->getId(), $request->request->get('_token'))) {
            $entityManager->remove($student);
            $entityManager->flush();
            $this->addFlash('success', 'Student deleted successfully.');
        } else {
            $this->addFlash('danger', 'Invalid CSRF token. Please try again.');
        }

        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }
}
