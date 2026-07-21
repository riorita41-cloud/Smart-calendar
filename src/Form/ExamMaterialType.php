<?php

namespace App\Form;

use App\Entity\ExamMaterial;
use App\Entity\Exam;
use App\Repository\ExamRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ExamMaterialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название материала',
                'attr' => [
                    'placeholder' => 'Например: Билеты 1-20',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Введите название материала']),
                ]
            ])
            ->add('exam', EntityType::class, [
                'class' => Exam::class,
                'choice_label' => 'name',
                'label' => 'Экзамен (необязательно)',
                'placeholder' => '-- Общий материал (для всех экзаменов) --',
                'required' => false,
                'query_builder' => function (ExamRepository $er) use ($user) {
                    return $er->createQueryBuilder('e')
                        ->where('e.user = :user')
                        ->setParameter('user', $user)
                        ->orderBy('e.examDate', 'ASC');
                },
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExamMaterial::class,
            'user' => null,
        ]);
    }
}