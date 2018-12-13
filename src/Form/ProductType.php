<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\ProductComponentLinkType;
use App\Entity\Location;
use App\Entity\Season;

/**
 * ProductType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('family', TextType::class, array(
                'required' => false,
                ))
            ->add('season', EntityType::class, array(
                'required' => false,
                'class' => Season::class,
                ))
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
            ->add('dateStart', DateType::class, array(
                'required' => false,
                ))
            ->add('dateEnd', DateType::class, array(
                'required' => false,
                ))
            ->add('exclusionFrom', DateType::class, array(
                'required' => false,
                ))
            ->add('exclusionTo', DateType::class, array(
                'required' => false,
                ))
            ->add('location', EntityType::class, array(
                'required' => false,
                'class' => Location::class,
                ))
            ->add('transport', CheckboxType::class, array(
                'required' => false,
                ))
            ->add('dayReference', TextType::class, array(
                'required' => false,
                ))
            ->add('daysAvailable', CollectionType::class, array(
                'required' => false,
                'entry_type' => ChoiceType::class,
                'mapped' => false,
                ))
            ->add('duration', CollectionType::class, array(
                'required' => false,
                'entry_type' => TextType::class,
                'mapped' => false,
                ))
            ->add('expectedTimes', CollectionType::class, array(
                'required' => false,
                'entry_type' => TextType::class,
                'mapped' => false,
                ))
            ->add('photo', TextType::class, array(
                'required' => false,
                ))
            ->add('links', CollectionType::class, array(
                'required' => false,
                'entry_type' => ProductComponentLinkType::class,
                'mapped' => false,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Product',
            'intention' => 'ProductForm',
        ));
    }
}
