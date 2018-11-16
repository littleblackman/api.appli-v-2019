<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\PersonAddressLinkType;

/**
 * PersonType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PersonType extends AbstractType
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Person',
            'intention' => 'PersonForm',
        ));
    }
}
