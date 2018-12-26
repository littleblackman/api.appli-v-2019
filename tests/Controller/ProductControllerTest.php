<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Product;
use App\Tests\TestTrait;

class ProductControllerTest extends WebTestCase
{
    use TestTrait;

    /**
     * Tests creation Product
     */
    public function testCreate()
    {
        $this->clientAuthenticated->request(
            'POST',
            '/product/create',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"family": "1", "season": "1", "nameFr": "name Fr", "nameEn": "Name En", "descriptionFr": "Description Fr", "descriptionEn": "Description En", "dateStart": "2018-11-20", "dateEnd": "2018-11-21", "exclusionFrom": "2018-11-20", "exclusionTo": "2018-11-20", "location": "1", "transport": true, "dayReference": "Day ref"}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $content = $this->assertJsonResponse($response, 200);

        $this->assertArrayHasKey('productId', $content['product']);

        self::$objectId = $content['product']['productId'];
    }

    /**
     * Tests display Product
     */
    public function testDisplay()
    {
        $this->clientAuthenticated->request('GET', '/product/display/' . self::$objectId);

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests modify Product
     */
    public function testModify()
    {
        //Tests with full data array
        $this->clientAuthenticated->request(
            'PUT',
            '/product/modify/' . self::$objectId,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"family": "1", "season": "1", "nameFr": "name Fr", "nameEn": "Name En", "descriptionFr": "Description Fr", "descriptionEn": "Description En", "dateStart": "2018-11-20", "dateEnd": "2018-11-21", "exclusionFrom": "2018-11-20", "exclusionTo": "2018-11-20", "location": "1", "transport": true, "dayReference": "Day ref"}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Tests with partial data array
        $this->clientAuthenticated->request(
            'PUT',
            '/product/modify/' . self::$objectId,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"family": "Family modifiée 2"}'
        );

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests list of Product
     */
    public function testList()
    {
        $this->clientAuthenticated->request('GET', '/product/list');

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests delete Product AND physically deletes it
     */
    public function testDelete()
    {
        $this->clientAuthenticated->request('DELETE', '/product/delete/' . self::$objectId);

        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Deletes physically the entity created by test
        $this->deleteEntity('Product', 'productId', self::$objectId);
    }
}
