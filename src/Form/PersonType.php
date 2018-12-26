<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                ))
            ->add('lastname', TextType::class, array(
                'required' => false,
                ))
            ->add('photo', TextType::class, array(
                'required' => false,
                ))
            ->add('user', EntityType::class, array(
                'required' => false,
                'class' => User::class,
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
