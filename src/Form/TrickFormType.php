<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Trick;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Url;

class TrickFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Title must contain at least 3 characters'
                    ]),
                ],
            ])
            ->add('description')
            ->add('category', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'title'
            ])
            ->add('images', FileType::class, [
                'multiple' => true,
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/*',
                    'multiple' => 'multiple'
                ]
            ])
            ->add('video', UrlType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Url(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
