<?php

namespace App\Service;

use App\Entity\Student;
use App\Entity\Attendance;
use Doctrine\ORM\EntityManagerInterface;

class AttendanceService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getTodayAttendanceTotals(): array
    {
        $todayStart = new \DateTimeImmutable('today midnight');
        $todayEnd = $todayStart->modify('+1 day');


        $students = $this->em->getRepository(Student::class)->findAll();
        $totalStudents = count($students);

        $attendanceLogs = $this->em->getRepository(Attendance::class)
            ->createQueryBuilder('a')
            ->where('a.date >= :start')
            ->andWhere('a.date < :end')
            ->setParameter('start', $todayStart)
            ->setParameter('end', $todayEnd)
            ->orderBy('a.date', 'ASC') // earliest first
            ->getQuery()
            ->getResult();


        $logsByStudentId = [];
        foreach ($attendanceLogs as $log) {
            $studentId = $log->getStudent()->getId();
            if (!isset($logsByStudentId[$studentId])) {
                $logsByStudentId[$studentId] = $log;
            }
        }

        $totalPresent = 0;
        $totalLate = 0;
        $totalAbsent = 0;

        foreach ($students as $student) {
            $log = $logsByStudentId[$student->getId()] ?? null;

            if ($log) {
                $section = $log->getClassSection();
                $expectedTimeIn = $section?->getTimeIn();

                if ($expectedTimeIn) {
                    $expectedTimeToday = (new \DateTimeImmutable('today'))->setTime(
                        (int) $expectedTimeIn->format('H'),
                        (int) $expectedTimeIn->format('i'),
                        (int) $expectedTimeIn->format('s')
                    );
                    $graceTime = $expectedTimeToday->modify('+5 minutes 59 seconds');

                    if ($log->getDate() > $graceTime) {
                        $totalLate++;
                    } else {
                        $totalPresent++;
                    }
                } else {
                    $totalPresent++;
                }
            } else {
                $totalAbsent++;
            }
        }

        return [
            'totalStudents' => $totalStudents,
            'totalPresent'  => $totalPresent,
            'totalLate'     => $totalLate,
            'totalAbsent'   => $totalAbsent,
        ];
    }
}
