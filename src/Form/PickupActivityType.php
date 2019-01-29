<?php

namespace App\Form;

use App\Entity\Child;
use App\Entity\Registration;
use App\Entity\Sport;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PickupActivityType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PickupActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('registration', EntityType::class, array(
                'required' => false,
                'class' => Registration::class,
                ))
            ->add('start', DateTimeType::class, array(
                'required' => false,
                'widget' => 'single_text',
                ))
            ->add('status', TextType::class, array(
                'required' => false,
                ))
            ->add('statusChange', DateTimeType::class, array(
                'required' => false,
                'widget' => 'single_text',
                ))
            ->add('validated', TextType::class, array(
                'required' => false,
                ))
            ->add('child', EntityType::class, array(
                'required' => false,
                'class' => Child::class,
                ))
            ->add('sport', EntityType::class, array(
                'required' => false,
                'class' => Sport::class,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\PickupActivity',
            'intention' => 'PickupActivityForm',
        ));
    }
}
