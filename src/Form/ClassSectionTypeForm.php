<?php

namespace App\Form;

use App\Entity\ClassSection;
use App\Entity\SchoolClass;
use App\Entity\Student;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ClassSectionTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['hide_section_name']) {
            $builder
                ->add('sectionName');
        }

        $builder
            ->add('class', EntityType::class, [
                'class' => SchoolClass::class,
                'choice_label' => 'subjectName',
                'disabled' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'col-sm-3 col-form-label',
                ],
            ]);

        if (!$options['hide_students_field']) {
            $builder
                ->add('students', EntityType::class, [
                    'class' => Student::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                    'row_attr' => [
                        'class' => 'flex flex-col gap-2',
                    ],
                    'choice_attr' => function () {
                        return ['class' => 'flex items-center gap-2'];
                    },
                    'label_attr' => [
                        'class' => 'col-form-label',
                    ],
                    'query_builder' => function (EntityRepository $er) use ($options) {
                        $schoolClass = $options['school_class'];
                        $excludedStudentIds = [];

                        if ($schoolClass) {
                            foreach ($schoolClass->getClassSections() as $section) {
                                foreach ($section->getStudents() as $student) {
                                    $excludedStudentIds[] = $student->getId();
                                }
                            }
                        }

                        $qb = $er->createQueryBuilder('s');

                        if (!empty($excludedStudentIds)) {
                            $qb->where($qb->expr()->notIn('s.id', ':excluded'))
                                ->setParameter('excluded', $excludedStudentIds);
                        }

                        return $qb;
                    },
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClassSection::class,
            'hide_section_name' => false, // new option default
            'hide_students_field' => false,
            'section' => null,
            'school_class' => null,
        ]);
    }
}
