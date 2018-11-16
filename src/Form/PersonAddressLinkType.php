<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Address;
use App\Entity\Person;

/**
 * PersonAddressLinkType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PersonAddressLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', EntityType::class, array(
                'required' => false,
                'class' => Person::class,
                'empty_data' => $options['data']->getPerson(),
                ))
            ->add('address', EntityType::class, array(
                'required' => false,
                'class' => Address::class,
                'empty_data' => $options['data']->getAddress(),
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\PersonAddressLink',
            'intention' => 'PersonAddressLinkForm',
        ));
    }
}
