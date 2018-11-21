<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\ProductComponentLinkType;

/**
 * ProductType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => false,
                ))
            ->add('description', TextType::class, array(
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
