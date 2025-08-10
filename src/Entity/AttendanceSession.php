<?php

namespace App\Entity;

use App\Repository\AttendanceSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttendanceSessionRepository::class)]
#[ORM\Table(name: 'attendance_session')]
#[ORM\UniqueConstraint(name: 'unique_section_date', columns: ['class_section_id', 'date'])]
class AttendanceSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'attendanceSessions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ClassSection $classSection = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endedAt = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    public function __construct()
    {
        $this->isActive = true;
        $this->date = new \DateTime(); // optional
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClassSection(): ?ClassSection
    {
        return $this->classSection;
    }

    public function setClassSection(?ClassSection $classSection): static
    {
        $this->classSection = $classSection;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): static
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeInterface $endedAt): static
    {
        $this->endedAt = $endedAt;
        return $this;
    }
}
