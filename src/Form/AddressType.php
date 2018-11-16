<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\PersonAddressLinkType;

/**
 * AddressType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getName(),
                ))
            ->add('address', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getAddress(),
                ))
            ->add('address2', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getAddress2(),
                ))
            ->add('postal', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getPostal(),
                ))
            ->add('town', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getTown(),
                ))
            ->add('country', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getCountry(),
                ))
            ->add('phone', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getPhone(),
                ))
            ->add('links', CollectionType::class, array(
                'required' => false,
                'entry_type' => PersonAddressLinkType::class,
                'mapped' => false,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Address',
            'intention' => 'AddressForm',
        ));
    }
}
