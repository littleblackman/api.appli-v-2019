<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * SeasonType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class SeasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => false,
                ))
            ->add('status', TextType::class, array(
                'required' => false,
                ))
            ->add('dateStart', DateType::class, array(
                'required' => false,
                'widget' => 'single_text',
                ))
            ->add('dateEnd', DateType::class, array(
                'required' => false,
                'widget' => 'single_text',
                ))
            ->add('links', CollectionType::class, array(
                'required' => false,
                'entry_type' => WeekType::class,
                'mapped' => false,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Season',
            'intention' => 'SeasonForm',
        ));
    }
}
