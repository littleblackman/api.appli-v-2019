<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Child;
use App\Entity\Ride;

/**
 * PickupType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PickupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', DateTimeType::class, array(
                'required' => false,
                'widget' => 'single_text',
                ))
            ->add('address', TextType::class, array(
                'required' => false,
                ))
            ->add('sortOrder', IntegerType::class, array(
                'required' => false,
                ))
            ->add('status', TextType::class, array(
                'required' => false,
                ))
            ->add('statusChange', DateTimeType::class, array(
                'required' => false,
                'widget' => 'single_text',
                ))
            ->add('places', IntegerType::class, array(
                'required' => false,
                ))
            ->add('comment', TextType::class, array(
                'required' => false,
                ))
            ->add('validated', CheckboxType::class, array(
                'required' => false,
                ))
            ->add('child', EntityType::class, array(
                'required' => false,
                'class' => Child::class,
                ))
            ->add('ride', EntityType::class, array(
                'required' => false,
                'class' => Ride::class,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Pickup',
            'intention' => 'PickupForm',
        ));
    }
}
