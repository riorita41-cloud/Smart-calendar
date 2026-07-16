<?php

namespace App\Form;

use App\Entity\Exam;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ExamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название экзамена',
                'attr' => [
                    'placeholder' => 'Например: ЕГЭ по математике'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Введите название экзамена']),
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => 'Предмет',
                'attr' => [
                    'placeholder' => 'Например: Математика'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Введите предмет']),
                ]
            ])
            ->add('examDate', DateType::class, [
                'label' => 'Дата экзамена',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d')
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Выберите дату экзамена']),
                ]
            ])
            ->add('studyDays', ChoiceType::class, [
                'label' => 'Дни для занятий',
                'choices' => [
                    'Понедельник' => 'monday',
                    'Вторник' => 'tuesday',
                    'Среда' => 'wednesday',
                    'Четверг' => 'thursday',
                    'Пятница' => 'friday',
                    'Суббота' => 'saturday',
                    'Воскресенье' => 'sunday',
                ],
                'multiple' => true,
                'expanded' => true, 
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Выберите хотя бы один день для занятий']),
                ]
            ])
            ->add('studyStartTime', TimeType::class, [
                'label' => 'Время начала занятий',
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Укажите время начала']),
                ]
            ])
            ->add('studyEndTime', TimeType::class, [
                'label' => 'Время окончания занятий',
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Укажите время окончания']),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exam::class,
        ]);
    }
}