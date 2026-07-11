<?php

namespace App\Form;

use App\Entity\Exam;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название экзамена',
                'attr' => [
                    'placeholder' => 'Например: ЕГЭ по математике'
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => 'Предмет',
                'attr' => [
                    'placeholder' => 'Например: Математика'
                ]
            ])
            ->add('examDate', DateType::class, [
                'label' => 'Дата экзамена',
                'widget' => 'single_text',
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d')
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
