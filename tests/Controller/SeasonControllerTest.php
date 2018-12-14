<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Season;
use App\Tests\TestTrait;

class SeasonControllerTest extends WebTestCase
{
    use TestTrait;

    /**
     * Tests creation Season
     */
    public function testCreate()
    {
        $this->clientAuthenticated->request(
            'POST',
            '/season/create',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name": "Name", "status": "active"}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $content = $this->assertJsonResponse($response, 200);

        $this->assertArrayHasKey('seasonId', $content['season']);

        self::$objectId = $content['season']['seasonId'];
    }

    /**
     * Tests display Season
     */
    public function testDisplay()
    {
        $this->clientAuthenticated->request('GET', '/season/display/' . self::$objectId);

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests modify Season
     */
    public function testModify()
    {
        //Tests with full data array
        $this->clientAuthenticated->request(
            'PUT',
            '/season/modify/' . self::$objectId,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name": "Name modified", "status": "active"}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Tests with partial data array
        $this->clientAuthenticated->request(
            'PUT',
            '/season/modify/' . self::$objectId,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name": "Name modified 2"}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests list of Season
     */
    public function testList()
    {
        $this->clientAuthenticated->request('GET', '/season/list');

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests delete Season AND physically deletes it
     */
    public function testDelete()
    {
        $this->clientAuthenticated->request('DELETE', '/season/delete/' . self::$objectId);

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Deletes physically the entity created by test
        $this->deleteEntity('Season', 'seasonId', self::$objectId);
    }
}