<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Child;

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
                'required' => true,
                'widget' => 'single_text',
                ))
            ->add('address', TextType::class, array(
                'required' => true,
                ))
            ->add('sortOrder', IntegerType::class, array(
                'required' => true,
                ))
            ->add('status', TextType::class, array(
                'required' => true,
                ))
            ->add('statusChange', DateTimeType::class, array(
                'required' => true,
                'widget' => 'single_text',
                ))
            ->add('comment', TextType::class, array(
                'required' => true,
                ))
            ->add('child', EntityType::class, array(
                'required' => true,
                'class' => Child::class,
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
