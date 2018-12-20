<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Address;
use App\Entity\Person;
use App\Entity\Vehicle;

/**
 * DriverType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', EntityType::class, array(
                'required' => false,
                'class' => Person::class,
                ))
            ->add('priority', IntegerType::class, array(
                'required' => false,
                ))
            ->add('vehicle', EntityType::class, array(
                'required' => false,
                'class' => Vehicle::class,
                ))
            ->add('address', EntityType::class, array(
                'required' => false,
                'class' => Address::class,
                ))
            ->add('links', CollectionType::class, array(
                'required' => false,
                'entry_type' => DriverZoneType::class,
                'mapped' => false,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Driver',
            'intention' => 'DriverForm',
        ));
    }
}
