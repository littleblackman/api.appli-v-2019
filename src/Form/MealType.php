<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Child;
use App\Entity\Person;

/**
 * MealType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class MealType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, array(
                'required' => false,
                'widget' => 'single_text',
                ))
            ->add('child', EntityType::class, array(
                'required' => false,
                'class' => Child::class,
                ))
            ->add('person', EntityType::class, array(
                'required' => false,
                'class' => Person::class,
                ))
            ->add('freeName', TextType::class, array(
                'required' => false,
                ))
            ->add('links', CollectionType::class, array(
                'required' => false,
                'entry_type' => MealFoodLinkType::class,
                'mapped' => false,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Meal',
            'intention' => 'MealForm',
        ));
    }
}
