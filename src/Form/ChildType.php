<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\ChildPersonLinkType;

/**
 * ChildType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getFirstname(),
                ))
            ->add('lastname', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getLastname(),
                ))
            ->add('phone', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getPhone(),
                ))
            ->add('birthdate', DateType::class, array(
                'required' => false,
                'widget' => 'single_text',
                'empty_data' => $options['data']->getBirthdate(),
                ))
            ->add('medical', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getMedical(),
                ))
            ->add('links', CollectionType::class, array(
                'required' => false,
                'entry_type' => ChildPersonLinkType::class,
                'mapped' => false,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Child',
            'intention' => 'ChildForm',
        ));
    }
}
