<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\AddressServiceInterface;
use App\Entity\Address;

class AddressController extends AbstractController
{
    private $addressService;

    public function __construct(AddressServiceInterface $addressService)
    {
        $this->addressService = $addressService;
    }

//DISPLAY
    /**
     * Specific address using "/address/display/{id}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/address/display/{id}",
     *    name="address_display",
     *    requirements={"id": "^([0-9]+)"},
     *    methods={"HEAD", "GET"})
     */
    public function display(Address $address)
    {
        $this->denyAccessUnlessGranted('addressDisplay', $address);

        return new JsonResponse($address->toArray());
    }

//MODIFY
    /**
     * Modifiy specific address using "/address/modify/{id}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/address/modify/{id}",
     *    name="address_modify",
     *    requirements={"id": "^([0-9]+)"},
     *    methods={"HEAD", "POST"})
     */
    public function modify(Address $address)
    {
        $this->denyAccessUnlessGranted('addressModify', $address);
dd('here');
        return new JsonResponse(array());
    }

//DELETE
    /**
     * Delete specific address using "/address/delete/{id}"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/address/delete/{id}",
     *    name="address_delete",
     *    requirements={"id": "^([0-9]+)"},
     *    methods={"HEAD", "POST"})
     */
    public function delete(Address $address)
    {
        $this->denyAccessUnlessGranted('addressDelete', $address);
dd('here');
        return new JsonResponse(array());
    }
}
