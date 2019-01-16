<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\StaffPresence;
use App\Tests\TestTrait;

class StaffPresenceControllerTest extends WebTestCase
{
    use TestTrait;

    /**
     * Tests creation StaffPresence
     */
    public function testCreate()
    {
        $this->clientAuthenticated->request(
            'POST',
            '/staff/presence/create',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '[{"staff": "1", "date": "1999-01-20", "start": "08:00:00", "end": "12:00:00"}]'
        );
        $response = $this->clientAuthenticated->getResponse();
        $content = $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests display StaffPresence
     */
    public function testDisplay()
    {
        $this->clientAuthenticated->request('GET', '/staff/presence/display/1/1999-01-20');
        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests list of StaffPresence
     */
    public function testList()
    {
        $this->clientAuthenticated->request('GET', '/staff/presence/list/driver/1999-01-20');
        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests delete StaffPresence AND physically deletes it
     */
    public function testDelete()
    {
        $data = array(
            'staff' => '1',
            'date' => '1999-01-20',
            'start' => '08:00:00',
            'end' => '12:00:00',
        );
        self::$objectId = $this->em->getRepository('App:StaffPresence')->findByData($data)->getStaffPresenceId();
        $this->clientAuthenticated->request('DELETE', '/staff/presence/delete/' . self::$objectId);
        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Deletes physically the entity created by test
        $this->deleteEntity('StaffPresence', 'staffPresenceId', self::$objectId);
    }
}
