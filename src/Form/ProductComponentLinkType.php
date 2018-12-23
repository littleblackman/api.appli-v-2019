<?php

namespace App\Form;

use App\Entity\Component;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ProductComponentLinkType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ProductComponentLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', EntityType::class, array(
                'required' => false,
                'class' => Product::class,
                ))
            ->add('component', EntityType::class, array(
                'required' => false,
                'class' => Component::class,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\ProductComponentLink',
            'intention' => 'ProductComponentLinkForm',
        ));
    }
}
