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
use App\Entity\AttendanceSession;
use Doctrine\ORM\EntityManager;

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
    public function sections(int $id, EntityManagerInterface $em): Response
    {
        $schoolClass = $em->getRepository(SchoolClass::class)->find($id);

        if (!$schoolClass) {
            throw $this->createNotFoundException('SchoolClass not found with ID ' . $id);
        }

        $today = new \DateTimeImmutable('today');
        $sessionsBySection = [];

        foreach ($schoolClass->getClassSections() as $section) {
            $session = $em->getRepository(AttendanceSession::class)->findOneBy([
                'classSection' => $section,
                'date' => $today,
            ]);

            $sessionsBySection[$section->getId()] = $session;
        }

        return $this->render('attendance/sections.html.twig', [
            'schoolClass' => $schoolClass,
            'sections' => $schoolClass->getClassSections(),
            'sessionsBySection' => $sessionsBySection,
        ]);
    }



    // #[Route('/attendance/section/{id}', name: 'attendance_take')]
    // public function takeAttendance(ClassSection $classSection, EntityManagerInterface $em): Response
    // {
    //     $todayStart = new \DateTimeImmutable('today midnight');
    //     $todayEnd = $todayStart->modify('+1 day');

    //     $attendanceLogs = $em->getRepository(Attendance::class)->createQueryBuilder('a')
    //         ->where('a.classSection = :section')
    //         ->andWhere('a.date >= :start')
    //         ->andWhere('a.date < :end')
    //         ->setParameter('section', $classSection)
    //         ->setParameter('start', $todayStart)
    //         ->setParameter('end', $todayEnd)
    //         ->orderBy('a.date', 'DESC')
    //         ->getQuery()
    //         ->getResult();

    //     return $this->render('attendance/take.html.twig', [
    //         'section' => $classSection,
    //         'students' => $classSection->getStudents(),
    //         'attendanceLogs' => $attendanceLogs,
    //     ]);
    // }

    #[Route('/attendance/section/{id}', name: 'attendance_take')]
    public function takeAttendance(ClassSection $classSection, EntityManagerInterface $em): Response
    {
        $today = new \DateTimeImmutable('today');
        $attendanceSessionRepo = $em->getRepository(AttendanceSession::class);

        // Check for existing session today
        $session = $attendanceSessionRepo->findOneBy([
            'classSection' => $classSection,
            'date' => $today,
        ]);

        if (!$session) {
            $session = new AttendanceSession();
            $session->setClassSection($classSection);
            $session->setDate($today);
            $em->persist($session);
            $em->flush();
        }

        // Now fetch logs based on session
        $logs = $em->getRepository(Attendance::class)->findBy([
            'attendanceSession' => $session,
        ]);

        return $this->render('attendance/take.html.twig', [
            'section' => $classSection,
            'students' => $classSection->getStudents(),
            'attendanceLogs' => $logs,
            'attendanceSession' => $session,
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

    //     // ✅ Check if student is in the section
    //     if (!$student->getClassSections()->contains($section)) {
    //         return new JsonResponse(['success' => false, 'message' => 'Student not in this section.'], 403);
    //     }

    //     // ✅ Use date range to find existing attendance today
    //     $todayStart = new \DateTimeImmutable('today midnight');
    //     $todayEnd = $todayStart->modify('+1 day');

    //     $existing = $em->getRepository(Attendance::class)->createQueryBuilder('a')
    //         ->where('a.student = :student')
    //         ->andWhere('a.classSection = :section')
    //         ->andWhere('a.date >= :start')
    //         ->andWhere('a.date < :end')
    //         ->setParameter('student', $student)
    //         ->setParameter('section', $section)
    //         ->setParameter('start', $todayStart)
    //         ->setParameter('end', $todayEnd)
    //         ->getQuery()
    //         ->getOneOrNullResult();

    //     if ($existing) {
    //         return new JsonResponse(['success' => false, 'message' => 'Already marked present.']);
    //     }

    //     // ✅ Save current datetime (date and time)
    //     $attendance = new Attendance();
    //     $attendance->setStudent($student);
    //     $attendance->setClassSection($section);
    //     $attendance->setDate(new \DateTimeImmutable()); // full datetime now
    //     $attendance->setStatus('present');

    //     $em->persist($attendance);
    //     $em->flush();

    //     // ✅ Fetch today's attendance logs
    //     $logs = $em->getRepository(Attendance::class)->createQueryBuilder('a')
    //         ->where('a.classSection = :section')
    //         ->andWhere('a.date >= :start')
    //         ->andWhere('a.date < :end')
    //         ->setParameter('section', $section)
    //         ->setParameter('start', $todayStart)
    //         ->setParameter('end', $todayEnd)
    //         ->orderBy('a.date', 'DESC')
    //         ->getQuery()
    //         ->getResult();

    //     // ✅ Format logs
    //     $logData = array_map(function ($log) {
    //         return [
    //             'studentName' => $log->getStudent()->getName(),
    //             'time' => $log->getDate()->format('h:i A'), // e.g. "11:15 PM"
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

        if (!$student->getClassSections()->contains($section)) {
            return new JsonResponse(['success' => false, 'message' => 'Student not in this section.'], 403);
        }

        $today = new \DateTimeImmutable('today');
        $session = $em->getRepository(AttendanceSession::class)->findOneBy([
            'classSection' => $section,
            'date' => $today,
        ]);

        if (!$session) {
            return new JsonResponse(['success' => false, 'message' => 'No active attendance session for today.'], 403);
        }

        if (!$session->isActive()) {
            return new JsonResponse(['success' => false, 'message' => 'Attendance session is already closed.'], 403);
        }


        $existing = $em->getRepository(Attendance::class)->findOneBy([
            'student' => $student,
            'attendanceSession' => $session,
        ]);

        if ($existing) {
            return new JsonResponse(['success' => false, 'message' => 'Already marked present.']);
        }

        $attendance = new Attendance();
        $attendance->setStudent($student);
        $attendance->setClassSection($section);
        $attendance->setAttendanceSession($session);
        $attendance->setDate(new \DateTimeImmutable()); // Full datetime
        $attendance->setStatus('present');

        $em->persist($attendance);
        $em->flush();

        $logs = $em->getRepository(Attendance::class)->findBy(
            ['attendanceSession' => $session],
            ['date' => 'DESC']
        );

        $logData = array_map(function ($log) {
            return [
                'studentName' => $log->getStudent()->getName(),
                'time' => $log->getDate()->format('h:i A'),
            ];
        }, $logs);

        return new JsonResponse([
            'success' => true,
            'message' => $student->getName() . ' marked present.',
            'attendanceLogs' => $logData,
        ]);
    }



    #[Route('/section/{id}/attendance-logs', name: 'section_attendance_logs', methods: ['GET'])]
    public function getAttendanceLogs(ClassSection $section, EntityManagerInterface $em): JsonResponse
    {
        $todayStart = new \DateTimeImmutable('today midnight');
        $todayEnd = $todayStart->modify('+1 day');

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

        $logData = array_map(function ($log) {
            return [
                'studentName' => $log->getStudent()->getName(),
                'time' => $log->getDate()->format('h:i A'),
            ];
        }, $attendanceLogs);

        return new JsonResponse([
            'attendanceLogs' => $logData,
        ]);
    }

    #[Route('/section/{id}/attendance-view', name: 'section_attendance_view', methods: ['GET'])]
    public function viewAttendanceTable(ClassSection $section, EntityManagerInterface $em): Response
    {

        $students = $section->getStudents();
        $totalStudents = count($students);
        $todayStart = new \DateTimeImmutable('today midnight');
        $todayEnd = $todayStart->modify('+1 day');

        $students = $section->getStudents();

        $attendanceLogs = $em->getRepository(Attendance::class)->createQueryBuilder('a')
            ->where('a.classSection = :section')
            ->andWhere('a.date >= :start')
            ->andWhere('a.date < :end')
            ->setParameter('section', $section)
            ->setParameter('start', $todayStart)
            ->setParameter('end', $todayEnd)
            ->getQuery()
            ->getResult();

        // Group logs by student ID
        $logsByStudentId = [];
        foreach ($attendanceLogs as $log) {
            $studentId = $log->getStudent()->getId();
            $logsByStudentId[$studentId] = $log;
        }

        // Prepare formatted data for Twig
        $studentAttendance = [];
        $totalPresent = 0;
        $totalLate = 0;
        $totalAbsent = 0;
        $expectedTimeIn = $section->getTimeIn();

        foreach ($students as $student) {
            $studentId = $student->getId();
            $log = $logsByStudentId[$studentId] ?? null;

            $status = 'Absent';
            $time = null;

            if ($log) {
                $logTime = $log->getDate();
                $time = $logTime->format('h:i A');

                if ($expectedTimeIn) {
                    // Build expected time-in today with full date
                    $expectedTimeToday = (new \DateTimeImmutable('today'))->setTime(
                        (int) $expectedTimeIn->format('H'),
                        (int) $expectedTimeIn->format('i'),
                        (int) $expectedTimeIn->format('s')
                    );

                    // Add 5 minutes and 59 seconds grace period
                    $graceTime = $expectedTimeToday->modify('+5 minutes 59 seconds');

                    if ($logTime > $graceTime) {
                        $status = 'Late';
                        $totalLate++;
                    } else {
                        $status = 'Present';
                        $totalPresent++;
                    }
                } else {
                    // No timeIn configured, default to Present
                    $status = 'Present';
                }
            } else {
                $status = 'Absent';
                $totalAbsent++;
            }

            $studentAttendance[] = [
                'student' => $student,
                'status' => $status,
                'time' => $time,
            ];
        }

        return $this->render('attendance/view_attendance.html.twig', [
            'section' => $section,
            'studentAttendance' => $studentAttendance,
            'totalStudents' => $totalStudents,
            'totalPresent' => $totalPresent,
            'totalLate' => $totalLate,
            'totalAbsent' => $totalAbsent,
        ]);
    }

    #[Route('/section/{sectionId}/start-attendance', name: 'start_attendance_session', methods: ['POST'])]
    public function startAttendanceSession(int $sectionId, EntityManagerInterface $em): Response
    {
        $section = $em->getRepository(ClassSection::class)->find($sectionId);

        if (!$section) {
            throw $this->createNotFoundException('Class section not found.');
        }

        $today = new \DateTimeImmutable('today');
        $existingSession = $em->getRepository(AttendanceSession::class)->findOneBy([
            'classSection' => $section,
            'date' => $today,
        ]);

        if ($existingSession) {
            $this->addFlash('warning', 'Attendance session already exists for today.');
            return $this->redirectToRoute('attendance_sections', ['id' => $section->getClass()->getId()]);
        }

        $session = new AttendanceSession();
        $session->setClassSection($section);
        $session->setDate($today);
        $session->setStartedAt(new \DateTimeImmutable());

        $em->persist($session);
        $em->flush();

        $this->addFlash('success', 'Attendance session started.');
        return $this->redirectToRoute('attendance_sections', ['id' => $section->getClass()->getId()]);
    }


    #[Route('/attendance/session/stop/{sectionId}', name: 'stop_attendance_session')]
    public function stopAttendanceSession(int $sectionId, EntityManagerInterface $em): Response
    {
        $section = $em->getRepository(ClassSection::class)->find($sectionId);

        if (!$section) {
            throw $this->createNotFoundException('Section not found.');
        }

        $today = new \DateTimeImmutable('today');

        $session = $em->getRepository(AttendanceSession::class)->findOneBy([
            'classSection' => $section,
            'date' => $today,
            'isActive' => true,
        ]);

        if (!$session) {
            $this->addFlash('error', 'No active session found for today.');
            return $this->redirectToRoute('attendance_sections', ['id' => $section->getClass()->getId()]);
        }

        $session->setIsActive(false);
        $session->setEndedAt(new \DateTimeImmutable()); //

        $em->flush();

        $this->addFlash('success', 'Attendance session stopped.');
        return $this->redirectToRoute('attendance_sections', ['id' => $section->getClass()->getId()]);
    }
}
