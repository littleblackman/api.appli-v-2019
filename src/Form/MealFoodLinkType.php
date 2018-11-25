<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Food;
use App\Entity\Meal;

/**
 * MealFoodLinkType FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class MealFoodLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', EntityType::class, array(
                'required' => false,
                'class' => Meal::class,
                ))
            ->add('component', EntityType::class, array(
                'required' => false,
                'class' => Food::class,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\MealFoodLink',
            'intention' => 'MealFoodLinkFoem',
        ));
    }
}
