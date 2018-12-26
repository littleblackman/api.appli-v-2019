<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Driver;
use App\Tests\TestTrait;

class DriverControllerTest extends WebTestCase
{
    use TestTrait;

    /**
     * Tests creation Driver
     */
    public function testCreate()
    {
        $this->clientAuthenticated->request(
            'POST',
            '/driver/create',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"person": "1", "postal": "11111", "priority": 10}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $content = $this->assertJsonResponse($response, 200);

        $this->assertArrayHasKey('driverId', $content['driver']);

        self::$objectId = $content['driver']['driverId'];
    }

    /**
     * Tests display Driver
     */
    public function testDisplay()
    {
        $this->clientAuthenticated->request('GET', '/driver/display/' . self::$objectId);

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests modify Driver
     */
    public function testModify()
    {
        //Tests with full data array
        $this->clientAuthenticated->request(
            'PUT',
            '/driver/modify/' . self::$objectId,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"person": "1", "postal": "22222", "priority": 10}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Tests with partial data array
        $this->clientAuthenticated->request(
            'PUT',
            '/driver/modify/' . self::$objectId,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"postal": "33333"}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests list of Driver
     */
    public function testList()
    {
        $this->clientAuthenticated->request('GET', '/driver/list');

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests delete Driver AND physically deletes it
     */
    public function testDelete()
    {
        $this->clientAuthenticated->request('DELETE', '/driver/delete/' . self::$objectId);

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Deletes physically the entity created by test
        $this->deleteEntity('Driver', 'driverId', self::$objectId);
    }
}
