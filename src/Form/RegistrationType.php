<?php

namespace App\Form;

use App\Entity\Child;
use App\Entity\Person;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * RegistrationType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('registration', DateTimeType::class, array(
                'required' => false,
                'widget' => 'single_text',
                ))
            ->add('child', EntityType::class, array(
                'required' => false,
                'class' => Child::class,
                ))
            ->add('person', EntityType::class, array(
                'required' => false,
                'class' => Person::class,
                ))
            ->add('product', EntityType::class, array(
                'required' => false,
                'class' => Product::class,
                ))
            ->add('invoice', IntegerType::class, array(
                'required' => false,
                ))
            ->add('isPayed', CheckboxType::class, array(
                'required' => false,
                ))
            ->add('sessionDate', DateType::class, array(
                'required' => false,
                'widget' => 'single_text',
                ))
            ->add('sessionStart', TimeType::class, array(
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                ))
            ->add('sessionEnd', TimeType::class, array(
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Registration',
            'intention' => 'RegistrationForm',
        ));
    }
}
