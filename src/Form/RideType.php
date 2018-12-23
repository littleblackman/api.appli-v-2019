<?php

namespace App\Form;

use App\Entity\Driver;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * RideType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class RideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, array(
                'required' => false,
                'widget' => 'single_text',
                ))
            ->add('name', TextType::class, array(
                'required' => false,
                ))
            ->add('start', TimeType::class, array(
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                ))
            ->add('arrival', TimeType::class, array(
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                ))
            ->add('startPoint', TextType::class, array(
                'required' => false,
                ))
            ->add('endPoint', TextType::class, array(
                'required' => false,
                ))
            ->add('driver', EntityType::class, array(
                'required' => false,
                'class' => Driver::class,
                ))
            ->add('vehicle', EntityType::class, array(
                'required' => false,
                'class' => Vehicle::class,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Ride',
            'intention' => 'RideForm',
        ));
    }
}
