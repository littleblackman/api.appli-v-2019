<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Person;
use App\Entity\Vehicle;

/**
 * VehicleType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => true,
                ))
            ->add('matriculation', TextType::class, array(
                'required' => true,
                ))
            ->add('combustible', TextType::class, array(
                'required' => true,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Vehicle',
            'intention' => 'VehicleForm',
        ));
    }
}
