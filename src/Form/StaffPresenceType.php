<?php

namespace App\Form;

use App\Entity\Staff;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * StaffPresenceType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class StaffPresenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('staff', EntityType::class, array(
                'required' => false,
                'class' => Staff::class,
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
            'data_class' => 'App\Entity\StaffPresence',
            'intention' => 'StaffPresenceForm',
        ));
    }
}
