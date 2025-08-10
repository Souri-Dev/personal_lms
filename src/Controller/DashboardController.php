<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\AttendanceService;
use App\Entity\ClassSection;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(AttendanceService $attendanceService, ClassSection $section): Response
    {

        $data = $attendanceService->getTodayAttendanceTotals($section);

        return $this->render('dashboard/index.html.twig', $data);
    }
}
