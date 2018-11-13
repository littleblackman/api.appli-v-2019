<?php

namespace App\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use App\Form\AppFormFactoryInterface;
use App\Form\AddressType;
use App\Form\ChildType;
use App\Form\PersonType;
use App\Form\PickupType;
use App\Form\RideType;
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
    public function create(string $name, $subject)
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
            case 'person-create':
            case 'person-modify':
                $form = PersonType::class;
                break;
            case 'pickup-create':
            case 'pickup-modify':
                $form = PickupType::class;
                break;
            case 'ride-create':
            case 'ride-modify':
                $form = RideType::class;
                break;
            case 'vehicle-create':
            case 'vehicle-modify':
                $form = VehicleType::class;
                break;
            default:
                break;
        }

        return $this->formFactory->create($form, $subject);
    }
}
