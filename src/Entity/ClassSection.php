<?php

namespace App\Entity;

use App\Repository\ClassSectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Student;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Validator\UniqueSectionPerClass;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClassSectionRepository::class)]
#[UniqueSectionPerClass(message: 'This section name already exists in this class.')]
class ClassSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $sectionName = null;

    #[ORM\ManyToOne(inversedBy: 'classSections')]
    private ?SchoolClass $class = null;

    #[ORM\Column(type: 'time', nullable: false)]
    #[Assert\NotNull(message: 'Time-in is required.')]
    private ?\DateTimeInterface $timeIn = null;

    #[ORM\ManyToMany(targetEntity: Student::class, mappedBy: 'classSections')]
    private Collection $students;

    /**
     * @var Collection<int, Attendance>
     */
    #[ORM\OneToMany(targetEntity: Attendance::class, mappedBy: 'classSection')]
    private Collection $attendances;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSectionName(): ?string
    {
        return $this->sectionName;
    }

    public function setSectionName(string $sectionName): static
    {
        $this->sectionName = $sectionName;

        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function getTimeIn(): ?\DateTimeInterface
    {
        return $this->timeIn;
    }

    public function setClass(?SchoolClass $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function setTimeIn(?\DateTimeInterface $timeIn): static
    {
        $this->timeIn = $timeIn;

        return $this;
    }

    public function __construct()
    {
        $this->students = new ArrayCollection();
        $this->attendances = new ArrayCollection();
    }

    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): static
    {
        if (!$this->students->contains($student)) {
            $this->students[] = $student;
            $student->addClassSection($this); // keep the relation bidirectional
        }

        return $this;
    }

    public function removeStudent(Student $student): static
    {
        if ($this->students->removeElement($student)) {
            $student->removeClassSection($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function addAttendance(Attendance $attendance): static
    {
        if (!$this->attendances->contains($attendance)) {
            $this->attendances->add($attendance);
            $attendance->setClassSection($this);
        }

        return $this;
    }

    public function removeAttendance(Attendance $attendance): static
    {
        if ($this->attendances->removeElement($attendance)) {
            // set the owning side to null (unless already changed)
            if ($attendance->getClassSection() === $this) {
                $attendance->setClassSection(null);
            }
        }

        return $this;
    }
}
