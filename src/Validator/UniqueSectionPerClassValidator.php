<?php

namespace App\Validator;

use App\Entity\ClassSection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueSectionPerClassValidator extends ConstraintValidator
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof ClassSection) {
            return;
        }

        $sectionName = $value->getSectionName();
        $class = $value->getClass();

        if (!$sectionName || !$class) {
            return;
        }

        $existing = $this->em->getRepository(ClassSection::class)->findOneBy([
            'sectionName' => $sectionName,
            'class' => $class,
        ]);

        if ($existing && $existing->getId() !== $value->getId()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('sectionName')
                ->addViolation();
        }
    }
}
