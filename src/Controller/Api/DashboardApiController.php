<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\SchoolClass;
use App\Entity\ClassSection;

#[Route('/dashboard', name: 'api_dashboard_')]
class DashboardApiController extends AbstractController
{
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(EntityManagerInterface $em): JsonResponse
    {
        $subjects = $em->getRepository(SchoolClass::class)->findAll();
        $categories = array_map(fn($subject) => $subject->getSubjectName(), $subjects);

        $sections = $em->getRepository(ClassSection::class)->findAll();

        $series = [];

        foreach ($sections as $section) {
            $data = [];

            foreach ($subjects as $subject) {
                if ($section->getClass()->getId() === $subject->getId()) {
                    $count = count($section->getStudents());
                } else {
                    $count = 0;
                }

                $data[] = $count;
            }

            $series[] = [
                'name' => $section->getClass()->getSubjectName() . ' - ' . $section->getSectionName(),
                'data' => $data
            ];
        }

        return $this->json([
            'series' => $series,
            'categories' => $categories
        ]);
    }
}
