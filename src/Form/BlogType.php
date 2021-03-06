<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * BlogType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'required' => false,
                ))
            ->add('content', TextType::class, array(
                'required' => false,
                ))
            ->add('photo', TextType::class, array(
                'required' => false,
                ))
            ->add('author', TextType::class, array(
                'required' => false,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Blog',
            'intention' => 'BlogForm',
        ));
    }
}
