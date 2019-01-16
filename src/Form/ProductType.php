<?php

namespace App\Form;

use App\Entity\Family;
use App\Entity\Season;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ProductType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('family', EntityType::class, array(
                'required' => false,
                'class' => Family::class,
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
            ->add('priceTtc', NumberType::class, array(
                'required' => false,
                'scale' => 2,
                ))
            ->add('transport', CheckboxType::class, array(
                'required' => false,
                ))
            ->add('photo', TextType::class, array(
                'required' => false,
                ))
            ->add('isLocationSelectable', CheckboxType::class, array(
                'required' => false,
                ))
            ->add('isDateSelectable', CheckboxType::class, array(
                'required' => false,
                ))
            ->add('isHourSelectable', CheckboxType::class, array(
                'required' => false,
                ))
            ->add('isSportAssociated', CheckboxType::class, array(
                'required' => false,
                ))
            ->add('visibility', TextType::class, array(
                'required' => false,
                ))
            ->add('hourDropin', TimeType::class, array(
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                ))
            ->add('hourDropoff', TimeType::class, array(
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                ))
            ->add('categories', CollectionType::class, array(
                'required' => false,
                'entry_type' => ProductCategoryLinkType::class,
                'mapped' => false,
                ))
            ->add('components', CollectionType::class, array(
                'required' => false,
                'entry_type' => ProductComponentType::class,
                'mapped' => false,
                ))
            ->add('dates', CollectionType::class, array(
                'required' => false,
                'entry_type' => ProductDateLinkType::class,
                'mapped' => false,
                ))
            ->add('hours', CollectionType::class, array(
                'required' => false,
                'entry_type' => ProductHourLinkType::class,
                'mapped' => false,
                ))
            ->add('locations', CollectionType::class, array(
                'required' => false,
                'entry_type' => ProductLocationLinkType::class,
                'mapped' => false,
                ))
            ->add('sports', CollectionType::class, array(
                'required' => false,
                'entry_type' => ProductSportLinkType::class,
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
