<?php

namespace App\Controller;

use App\Entity\SchoolClass;
use App\Entity\ClassSection;
use App\Entity\Attendance;
use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AttendanceController extends AbstractController
{
    #[Route('/attendance', name: 'attendance_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $classes = $em->getRepository(SchoolClass::class)->findAll();

        return $this->render('attendance/index.html.twig', [
            'classes' => $classes,
        ]);
    }

    #[Route('/attendance/{id}/sections', name: 'attendance_sections')]
    public function sections(SchoolClass $schoolClass): Response
    {
        return $this->render('attendance/sections.html.twig', [
            'schoolClass' => $schoolClass,
            'sections' => $schoolClass->getClassSections(),
        ]);
    }

    #[Route('/attendance/section/{id}', name: 'attendance_take')]
    public function takeAttendance(ClassSection $classSection): Response
    {
        return $this->render('attendance/take.html.twig', [
            'section' => $classSection,
            'students' => $classSection->getStudents(),
        ]);
    }

    #[Route('/mark-attendance', name: 'mark_attendance', methods: ['POST'])]
    public function markAttendance(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $qr = $data['qr'] ?? null;
        $sectionId = $data['sectionId'] ?? null;

        if (!$qr || !$sectionId) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid data.'], 400);
        }

        $student = $em->getRepository(Student::class)->findOneBy(['qr' => $qr]);
        $section = $em->getRepository(ClassSection::class)->find($sectionId);

        if (!$student || !$section) {
            return new JsonResponse(['success' => false, 'message' => 'Student or section not found.'], 404);
        }

        // ✅ Check if student is in the section (corrected)
        if (!$student->getClassSections()->contains($section)) {
            return new JsonResponse(['success' => false, 'message' => 'Student not in this section.'], 403);
        }

        // Check if already marked present today
        $existing = $em->getRepository(Attendance::class)->findOneBy([
            'student' => $student,
            'classSection' => $section,
            'date' => new \DateTimeImmutable('today'),
        ]);

        if ($existing) {
            return new JsonResponse(['success' => false, 'message' => 'Already marked present.']);
        }

        $attendance = new Attendance();
        $attendance->setStudent($student);
        $attendance->setClassSection($section);
        $attendance->setDate(new \DateTimeImmutable('today'));
        $attendance->setStatus('present');

        $em->persist($attendance);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => $student->getName() . ' marked present.']);
    }
}
