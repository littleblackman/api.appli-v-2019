<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Child;
use App\Entity\Ride;

/**
 * PickupType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PickupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', DateTimeType::class, array(
                'required' => false,
                'widget' => 'single_text',
                'empty_data' => $options['data']->getStart(),
                ))
            ->add('address', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getAddress(),
                ))
            ->add('sortOrder', IntegerType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getSortOrder(),
                ))
            ->add('status', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getStatus(),
                ))
            ->add('statusChange', DateTimeType::class, array(
                'required' => false,
                'widget' => 'single_text',
                'empty_data' => $options['data']->getStatusChange(),
                ))
            ->add('places', IntegerType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getPlaces(),
                ))
            ->add('comment', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getComment(),
                ))
            ->add('child', EntityType::class, array(
                'required' => false,
                'class' => Child::class,
                'empty_data' => $options['data']->getChild(),
                ))
            ->add('ride', EntityType::class, array(
                'required' => false,
                'class' => Ride::class,
                'empty_data' => $options['data']->getRide(),
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Pickup',
            'intention' => 'PickupForm',
        ));
    }
}
