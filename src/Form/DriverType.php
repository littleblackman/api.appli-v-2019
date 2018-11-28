<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Person;

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
            ->add('postal', TextType::class, array(
                'required' => false,
                ))
            ->add('priority', IntegerType::class, array(
                'required' => false,
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