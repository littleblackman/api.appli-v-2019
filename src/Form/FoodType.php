<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Person;
use App\Entity\Vehicle;

/**
 * FoodType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class FoodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => false,
                ))
            ->add('description', TextType::class, array(
                'required' => false,
                ))
            ->add('kind', TextType::class, array(
                'required' => false,
                ))
            ->add('isActive', CheckboxType::class, array(
                'required' => false,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Food',
            'intention' => 'FoodForm',
        ));
    }
}
