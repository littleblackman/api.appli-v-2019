<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Phone;
use App\Entity\Person;

/**
 * PersonPhoneLinkType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PersonPhoneLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', EntityType::class, array(
                'required' => false,
                'class' => Person::class,
                ))
            ->add('phone', EntityType::class, array(
                'required' => false,
                'class' => Phone::class,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\PersonPhoneLink',
            'intention' => 'PersonPhoneLinkForm',
        ));
    }
}
