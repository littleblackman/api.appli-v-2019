<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Person;
use App\Entity\Vehicle;

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
                'empty_data' => $options['data']->getDate(),
                ))
            ->add('name', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getName(),
                ))
            ->add('start', TimeType::class, array(
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'empty_data' => $options['data']->getStart(),
                ))
            ->add('arrival', TimeType::class, array(
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'empty_data' => $options['data']->getArrival(),
                ))
            ->add('startPoint', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getStartPoint(),
                ))
            ->add('endPoint', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getEndPoint(),
                ))
            ->add('person', EntityType::class, array(
                'required' => false,
                'class' => Person::class,
                'empty_data' => $options['data']->getPerson(),
                ))
            ->add('vehicle', EntityType::class, array(
                'required' => false,
                'class' => Vehicle::class,
                'empty_data' => $options['data']->getVehicle(),
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
