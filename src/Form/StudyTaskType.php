<?php

namespace App\Form;

use App\Entity\Exam;
use App\Entity\StudyTask;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudyTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название задачи',
                'attr' => [
                    'placeholder' => 'Например: Решить 10 уравнений'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание (необязательно)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Подробное описание задачи...',
                    'rows' => 3
                ]
            ])
            ->add('scheduledDate', DateType::class, [
                'label' => 'Дата выполнения',
                'widget' => 'single_text',
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d')
                ]
            ])
            ->add('exam', EntityType::class, [
                'label' => 'Экзамен',
                'class' => Exam::class,
                'choice_label' => function($exam) {
                    return $exam->getName() . ' (' . $exam->getSubject() . ')';
                },
                'placeholder' => 'Выберите экзамен'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StudyTask::class,
        ]);
    }
}