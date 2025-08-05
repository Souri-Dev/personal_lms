<?php

namespace App\Controller;

use App\Entity\SchoolClass;
use App\Entity\ClassSection;
use App\Entity\Attendance;
use App\Entity\Student;
use App\Repository\StudentRepository;
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
    public function takeAttendance(ClassSection $classSection, EntityManagerInterface $em): Response
    {
        $todayStart = new \DateTimeImmutable('today midnight');
        $todayEnd = $todayStart->modify('+1 day');

        $attendanceLogs = $em->getRepository(Attendance::class)->createQueryBuilder('a')
            ->where('a.classSection = :section')
            ->andWhere('a.date >= :start')
            ->andWhere('a.date < :end')
            ->setParameter('section', $classSection)
            ->setParameter('start', $todayStart)
            ->setParameter('end', $todayEnd)
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('attendance/take.html.twig', [
            'section' => $classSection,
            'students' => $classSection->getStudents(),
            'attendanceLogs' => $attendanceLogs,
        ]);
    }


    // #[Route('/mark-attendance', name: 'mark_attendance', methods: ['POST'])]
    // public function markAttendance(Request $request, EntityManagerInterface $em): JsonResponse
    // {
    //     $data = json_decode($request->getContent(), true);
    //     $qr = $data['qr'] ?? null;
    //     $sectionId = $data['sectionId'] ?? null;

    //     if (!$qr || !$sectionId) {
    //         return new JsonResponse(['success' => false, 'message' => 'Invalid data.'], 400);
    //     }

    //     $student = $em->getRepository(Student::class)->findOneBy(['qr' => $qr]);
    //     $section = $em->getRepository(ClassSection::class)->find($sectionId);

    //     if (!$student || !$section) {
    //         return new JsonResponse(['success' => false, 'message' => 'Student or section not found.'], 404);
    //     }

    //     // ✅ Check if student is in the section (corrected)
    //     if (!$student->getClassSections()->contains($section)) {
    //         return new JsonResponse(['success' => false, 'message' => 'Student not in this section.'], 403);
    //     }

    //     // Check if already marked present today
    //     $today = new \DateTimeImmutable('today');
    //     $existing = $em->getRepository(Attendance::class)->findOneBy([
    //         'student' => $student,
    //         'classSection' => $section,
    //         'date' => $today,
    //     ]);

    //     if ($existing) {
    //         return new JsonResponse(['success' => false, 'message' => 'Already marked present.']);
    //     }

    //     $attendance = new Attendance();
    //     $attendance->setStudent($student);
    //     $attendance->setClassSection($section);
    //     $attendance->setDate(new \DateTimeImmutable('today'));
    //     $attendance->setStatus('present');

    //     $em->persist($attendance);
    //     $em->flush();

    //     // return new JsonResponse(['success' => true, 'message' => $student->getName() . ' marked present.']);
    //     // Fetch updated attendance logs for today
    //     $logs = $em->getRepository(Attendance::class)->createQueryBuilder('a')
    //         ->where('a.classSection = :section')
    //         ->andWhere('a.date = :today')
    //         ->setParameter('section', $section)
    //         ->setParameter('today', $today)
    //         ->orderBy('a.timeMarked', 'DESC') // Optional: if you have a timestamp field like `timeMarked`
    //         ->getQuery()
    //         ->getResult();

    //     // Format logs for JSON
    //     $logData = array_map(function ($log) {
    //         return [
    //             'studentName' => $log->getStudent()->getName(),
    //             'time' => $log->getDate()->format('H:i A'), // Adjust if you store separate time
    //         ];
    //     }, $logs);

    //     return new JsonResponse([
    //         'success' => true,
    //         'message' => $student->getName() . ' marked present.',
    //         'attendanceLogs' => $logData,
    //     ]);
    // }

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

        // ✅ Check if student is in the section
        if (!$student->getClassSections()->contains($section)) {
            return new JsonResponse(['success' => false, 'message' => 'Student not in this section.'], 403);
        }

        // ✅ Use date range to find existing attendance today
        $todayStart = new \DateTimeImmutable('today midnight');
        $todayEnd = $todayStart->modify('+1 day');

        $existing = $em->getRepository(Attendance::class)->createQueryBuilder('a')
            ->where('a.student = :student')
            ->andWhere('a.classSection = :section')
            ->andWhere('a.date >= :start')
            ->andWhere('a.date < :end')
            ->setParameter('student', $student)
            ->setParameter('section', $section)
            ->setParameter('start', $todayStart)
            ->setParameter('end', $todayEnd)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existing) {
            return new JsonResponse(['success' => false, 'message' => 'Already marked present.']);
        }

        // ✅ Save current datetime (date and time)
        $attendance = new Attendance();
        $attendance->setStudent($student);
        $attendance->setClassSection($section);
        $attendance->setDate(new \DateTimeImmutable()); // full datetime now
        $attendance->setStatus('present');

        $em->persist($attendance);
        $em->flush();

        // ✅ Fetch today's attendance logs
        $logs = $em->getRepository(Attendance::class)->createQueryBuilder('a')
            ->where('a.classSection = :section')
            ->andWhere('a.date >= :start')
            ->andWhere('a.date < :end')
            ->setParameter('section', $section)
            ->setParameter('start', $todayStart)
            ->setParameter('end', $todayEnd)
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult();

        // ✅ Format logs
        $logData = array_map(function ($log) {
            return [
                'studentName' => $log->getStudent()->getName(),
                'time' => $log->getDate()->format('h:i A'), // e.g. "11:15 PM"
            ];
        }, $logs);

        return new JsonResponse([
            'success' => true,
            'message' => $student->getName() . ' marked present.',
            'attendanceLogs' => $logData,
        ]);
    }


    #[Route('/section/{id}/attendance-logs', name: 'section_attendance_logs', methods: ['GET'])]
    public function getAttendanceLogs(ClassSection $section, EntityManagerInterface $em): Response
    {
        // Define today's range
        $todayStart = new \DateTimeImmutable('today midnight');
        $todayEnd = $todayStart->modify('+1 day');

        // Get students in this section
        $students = $section->getStudents(); // assuming OneToMany with Student

        // Get today's attendance logs for this section
        $attendanceLogs = $em->getRepository(Attendance::class)->createQueryBuilder('a')
            ->where('a.classSection = :section')
            ->andWhere('a.date >= :start')
            ->andWhere('a.date < :end')
            ->setParameter('section', $section)
            ->setParameter('start', $todayStart)
            ->setParameter('end', $todayEnd)
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult();

        // Group logs by student ID
        $logsByStudentId = [];
        foreach ($attendanceLogs as $log) {
            $studentId = $log->getStudent()->getId();
            $logsByStudentId[$studentId] = $log;
        }

        // Prepare attendance info per student
        $studentAttendance = [];
        foreach ($students as $student) {
            $studentId = $student->getId();
            $log = $logsByStudentId[$studentId] ?? null;

            $studentAttendance[] = [
                'student' => $student,
                'isPresent' => $log !== null,
                'time' => $log?->getDate()?->format('h:i A'),
            ];
        }

        return $this->render('attendance/view_attendance.html.twig', [
            'section' => $section,
            'studentAttendance' => $studentAttendance,
        ]);
    }
}
