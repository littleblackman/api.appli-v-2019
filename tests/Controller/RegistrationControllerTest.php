<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Registration;
use App\Tests\TestTrait;

class RegistrationControllerTest extends WebTestCase
{
    use TestTrait;

    /**
     * Tests creation Registration
     */
    public function testCreate()
    {
        $this->clientAuthenticated->request(
            'POST',
            '/registration/create',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"child": "1", "person": "1", "product": "1", "invoice": 1, "payed": "100.50", "status": "Status", "sessions": [{"date": "2018-01-20", "start": "09:00:00", "end": "18:00:00"}, {"date": "2018-01-20", "start": "09:00:00", "end": "18:00:00"}], "location": 1, "sport": 1}'
        );
        $response = $this->clientAuthenticated->getResponse();
        $content = $this->assertJsonResponse($response, 200);
        $this->assertArrayHasKey('registrationId', $content['registration']);

        self::$objectId = $content['registration']['registrationId'];
    }

    /**
     * Tests display Registration
     */
    public function testDisplay()
    {
        $this->clientAuthenticated->request('GET', '/registration/display/' . self::$objectId);
        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests modify Registration
     */
    public function testModify()
    {
        //Tests with full data array
        $this->clientAuthenticated->request(
            'PUT',
            '/registration/modify/' . self::$objectId,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"child": "1", "person": "1", "product": "1", "invoice": 1, "payed": "100.50", "status": "Status", "sessions": [{"date": "2018-01-20", "start": "09:00:00", "end": "18:00:00"}, {"date": "2018-01-20", "start": "09:00:00", "end": "18:00:00"}], "location": 1, "sport": 1}'
        );
        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Tests with partial data array
        $this->clientAuthenticated->request(
            'PUT',
            '/registration/modify/' . self::$objectId,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"status": "New Status"}'
        );
        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests list of Registration
     */
    public function testList()
    {
        $this->clientAuthenticated->request('GET', '/registration/list');
        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests delete Registration AND physically deletes it
     */
    public function testDelete()
    {
        $this->clientAuthenticated->request('DELETE', '/registration/delete/' . self::$objectId);
        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Deletes physically the entity created by test
        $this->deleteEntity('Registration', 'registrationId', self::$objectId);
    }
}
