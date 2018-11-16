<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Person;

/**
 * ChildPersonLinkType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildPersonLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', EntityType::class, array(
                'required' => false,
                'class' => Person::class,
                'empty_data' => $options['data']->getPerson(),
                ))
            ->add('relation', TextType::class, array(
                'required' => false,
                'empty_data' => $options['data']->getRelation(),
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\ChildPersonLink',
            'intention' => 'ChildPersonLinkForm',
        ));
    }
}
