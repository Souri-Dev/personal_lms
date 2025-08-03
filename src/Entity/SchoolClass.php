<?php

namespace App\Entity;

use App\Repository\SchoolClassRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\ClassSection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SchoolClassRepository::class)]
#[UniqueEntity(fields: ['subjectCode'], message: 'This subject code already exists.')]
class SchoolClass
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $subjectName = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $subjectCode = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * @var Collection<int, ClassSection>
     */
    #[ORM\OneToMany(targetEntity: ClassSection::class, mappedBy: 'class')]
    private Collection $classSections;

    public function __construct()
    {
        $this->classSections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubjectName(): ?string
    {
        return $this->subjectName;
    }

    public function setSubjectName(string $subjectName): static
    {
        $this->subjectName = $subjectName;

        return $this;
    }

    public function getSubjectCode(): ?string
    {
        return $this->subjectCode;
    }

    public function setSubjectCode(string $subjectCode): static
    {
        $this->subjectCode = $subjectCode;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, ClassSection>
     */
    public function getClassSections(): Collection
    {
        return $this->classSections;
    }

    public function addClassSection(ClassSection $classSection): static
    {
        if (!$this->classSections->contains($classSection)) {
            $this->classSections->add($classSection);
            $classSection->setClass($this);
        }

        return $this;
    }

    public function removeClassSection(ClassSection $classSection): static
    {
        if ($this->classSections->removeElement($classSection)) {
            // set the owning side to null (unless already changed)
            if ($classSection->getClass() === $this) {
                $classSection->setClass(null);
            }
        }

        return $this;
    }
}
