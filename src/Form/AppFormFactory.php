<?php

namespace App\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use App\Form\AppFormFactoryInterface;
use App\Form\AddressType;
use App\Form\ChildType;
use App\Form\ComponentType;
use App\Form\DriverType;
use App\Form\FoodType;
use App\Form\LocationType;
use App\Form\MealType;
use App\Form\PersonType;
use App\Form\PhoneType;
use App\Form\PickupType;
use App\Form\ProductType;
use App\Form\RideType;
use App\Form\SeasonType;
use App\Form\VehicleType;

/**
 * AppFormFactory class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class AppFormFactory implements AppFormFactoryInterface
{
    /**
     * Stores FormFactoryInterface
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $name, $object)
    {
        switch ($name) {
            case 'address-create':
            case 'address-modify':
                $form = AddressType::class;
                break;
            case 'child-create':
            case 'child-modify':
                $form = ChildType::class;
                break;
            case 'component-create':
            case 'component-modify':
                $form = ComponentType::class;
                break;
            case 'driver-create':
            case 'driver-modify':
                $form = DriverType::class;
                break;
            case 'food-create':
            case 'food-modify':
                $form = FoodType::class;
                break;
            case 'location-create':
            case 'location-modify':
                $form = LocationType::class;
                break;
            case 'meal-create':
            case 'meal-modify':
                $form = MealType::class;
                break;
            case 'person-create':
            case 'person-modify':
                $form = PersonType::class;
                break;
            case 'phone-create':
            case 'phone-modify':
                $form = PhoneType::class;
                break;
            case 'pickup-create':
            case 'pickup-modify':
                $form = PickupType::class;
                break;
            case 'product-create':
            case 'product-modify':
                $form = ProductType::class;
                break;
            case 'ride-create':
            case 'ride-modify':
                $form = RideType::class;
                break;
            case 'season-create':
            case 'season-modify':
                $form = SeasonType::class;
                break;
            case 'vehicle-create':
            case 'vehicle-modify':
                $form = VehicleType::class;
                break;
            default:
                break;
        }

        return $this->formFactory->create($form, $object);
    }
}
