<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ComponentType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ComponentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nameFr', TextType::class, array(
                'required' => false,
                ))
            ->add('nameEn', TextType::class, array(
                'required' => false,
                ))
            ->add('descriptionFr', TextType::class, array(
                'required' => false,
                ))
            ->add('descriptionEn', TextType::class, array(
                'required' => false,
                ))
            ->add('price', NumberType::class, array(
                'required' => false,
                'scale' => 2,
                ))
            ->add('vat', NumberType::class, array(
                'required' => false,
                'scale' => 2,
                ))
            ->add('photo', TextType::class, array(
                'required' => false,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Component',
            'intention' => 'ComponentForm',
        ));
    }
}
