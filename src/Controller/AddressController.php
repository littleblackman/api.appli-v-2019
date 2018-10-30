<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
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
     * @Entity("address", expr="repository.findOneById(id)")
     */
    public function display(Address $address)
    {
        $this->denyAccessUnlessGranted('addressDisplay', $address);

        $addressArray = $this->addressService->filter($address->toArray());

        return new JsonResponse($addressArray);
    }

//CREATE
    /**
     * Creates ana ddress "/address/create"
     * @return JsonResponse
     * @throws AccessDeniedException
     *
     * @Route("/address/create",
     *    name="address_create",
     *    methods={"HEAD", "POST"})
     */
    public function create(Request $request)
    {
        $address = new Address();
        $this->denyAccessUnlessGranted('addressCreate', $address);

        $createdData = $this->addressService->create($address, $request->request);

        return new JsonResponse($createdData);
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
     * @Entity("address", expr="repository.findOneById(id)")
     */
    public function modify(Address $address)
    {
        $this->denyAccessUnlessGranted('addressModify', $address);

        $modifiedData = $this->addressService->modify($address, $request->request);

        return new JsonResponse($modifiedData);
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
     * @Entity("address", expr="repository.findOneById(id)")
     */
    public function delete(Request $request, Address $address)
    {
        $this->denyAccessUnlessGranted('addressDelete', $address);

        $suppressedData = $this->addressService->delete($address, $request->request);

        return new JsonResponse($suppressedData);
    }
}
