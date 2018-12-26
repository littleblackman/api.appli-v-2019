<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Pickup;
use App\Tests\TestTrait;

class PickupControllerTest extends WebTestCase
{
    use TestTrait;

    /**
     * Tests creation Pickup
     */
    public function testCreate()
    {
        $this->clientAuthenticated->request(
            'POST',
            '/pickup/create',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"kind": "Kind", "start": "2018-11-20T08:00:00Z", "postal": "75016", "address": "Address", "sortOrder": 1, "status": "null", "statusChange": "2018-11-20T08:00:01Z", "places": 1, "comment": "Comment", "validated": "validated", "child": "1", "ride": "1"}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $content = $this->assertJsonResponse($response, 200);

        $this->assertArrayHasKey('pickupId', $content['pickup']);

        self::$objectId = $content['pickup']['pickupId'];
    }

    /**
     * Tests display Pickup
     */
    public function testDisplay()
    {
        $this->clientAuthenticated->request('GET', '/pickup/display/' . self::$objectId);

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests modify Pickup
     */
    public function testModify()
    {
        //Tests with full data array
        $this->clientAuthenticated->request(
            'PUT',
            '/pickup/modify/' . self::$objectId,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"kind": "Kind", "start": "2018-11-20T08:00:00Z", "postal": "75016", "address": "Address modifiée", "sortOrder": 1, "status": "null", "statusChange": "2018-11-20T08:00:01Z", "places": 1, "comment": "Comment", "validated": "validated", "child": "1", "ride": "1"}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Tests with partial data array
        $this->clientAuthenticated->request(
            'PUT',
            '/pickup/modify/' . self::$objectId,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"address": "Address modifiée 2"}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests list of Ride
     */
    public function testList()
    {
        //Tests with date
        $this->clientAuthenticated->request('GET', '/pickup/list/2018-11-20');

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Tests with date and status
        $this->clientAuthenticated->request('GET', '/pickup/list/2018-11-20/absent');

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Tests with date and status
        $this->clientAuthenticated->request('GET', '/pickup/list/2018-11-20/supported');

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Tests with date and status
        $this->clientAuthenticated->request('GET', '/pickup/list/2018-11-20/unaffected');

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests delete Pickup AND physically deletes it
     */
    public function testDelete()
    {
        $this->clientAuthenticated->request('DELETE', '/pickup/delete/' . self::$objectId);

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Deletes physically the entity created by test
        $this->deleteEntity('Pickup', 'pickupId', self::$objectId);
    }
}
