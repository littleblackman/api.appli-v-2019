<?php

namespace App\Form;

use App\Entity\Invoice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * InvoiceProductType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class InvoiceProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invoice', EntityType::class, array(
                'required' => false,
                'class' => Invoice::class,
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
            ->add('prices', TextType::class, array(
                'required' => false,
                ))
            ->add('invoiceComponents', CollectionType::class, array(
                'required' => false,
                'entry_type' => InvoiceComponentType::class,
                'mapped' => false,
                'allow_extra_fields' => true,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\InvoiceProduct',
            'intention' => 'InvoiceProductForm',
        ));
    }
}
