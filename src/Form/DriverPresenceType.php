<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Driver;

/**
 * DriverPresenceType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverPresenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('driver', EntityType::class, array(
                'required' => false,
                'class' => Driver::class,
                ))
            ->add('date', DateType::class, array(
                'required' => false,
                'widget' => 'single_text',
                ))
            ->add('start', TimeType::class, array(
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                ))
            ->add('end', TimeType::class, array(
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\DriverPresence',
            'intention' => 'DriverPresenceForm',
        ));
    }
}
