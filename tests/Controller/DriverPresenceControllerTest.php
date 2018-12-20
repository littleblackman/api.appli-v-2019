<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\DriverPresence;
use App\Tests\TestTrait;

class DriverPresenceControllerTest extends WebTestCase
{
    use TestTrait;

    /**
     * Tests creation DriverPresence
     */
    public function testCreate()
    {
        $this->clientAuthenticated->request(
            'POST',
            '/driver/presence/create',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '[{"driver": "1", "date": "1999-01-20", "start": "08:00:00", "end": "12:00:00"}]'
        );

        $response = $this->clientAuthenticated->getResponse();
        $content = $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests display DriverPresence
     */
    public function testDisplay()
    {
        $this->clientAuthenticated->request('GET', '/driver/presence/display/1/1999-01-20');

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests list of DriverPresence
     */
    public function testList()
    {
        $this->clientAuthenticated->request('GET', '/driver/presence/list/1999-01-20');

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests delete DriverPresence AND physically deletes it
     */
    public function testDelete()
    {
        $data = array(
            'driver' => '1',
            'date' => '1999-01-20',
            'start' => '08:00:00',
            'end' => '12:00:00',
        );
        self::$objectId = $this->em->getRepository('App:DriverPresence')->findByData($data)->getDriverPresenceId();
        $this->clientAuthenticated->request('DELETE', '/driver/presence/delete/' . self::$objectId);

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Deletes physically the entity created by test
        $this->deleteEntity('DriverPresence', 'driverPresenceId', self::$objectId);
    }
}
