<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Transaction;
use App\Tests\TestTrait;

class TransactionControllerTest extends WebTestCase
{
    use TestTrait;

    /**
     * Tests creation Transaction
     */
    public function testCreate()
    {
        $this->clientAuthenticated->request(
            'POST',
            '/transaction/create',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"internalOrder": "Order", "status": "Status", "number": "Number", "amount": "152.25", "person": "1", "invoice": "1"}'
        );
        $response = $this->clientAuthenticated->getResponse();
        $content = $this->assertJsonResponse($response, 200);
        $this->assertArrayHasKey('transactionId', $content['transaction']);

        self::$objectId = $content['transaction']['transactionId'];
    }

    /**
     * Tests display Transaction
     */
    public function testDisplay()
    {
        //Test for a day
        $this->clientAuthenticated->request('GET', '/transaction/display/' . self::$objectId);
        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);
    }

    /**
     * Tests list of Transaction
     */
    public function testList()
    {
        //Tests list
        $this->clientAuthenticated->request('GET', '/transaction/list');
        $response = $this->clientAuthenticated->getResponse();
        $content = $this->assertJsonResponse($response, 200);
        $this->assertInternalType('array', $content);
        $first = $content[0];
        $this->assertArrayHasKey('transactionId', $first);
    }

    /**
     * Tests delete Transaction AND physically deletes it
     */
    public function testDelete()
    {
        $this->clientAuthenticated->request('DELETE', '/transaction/delete/' . self::$objectId);
        $response = $this->clientAuthenticated->getResponse();
        $this->assertJsonResponse($response, 200);

        //Deletes physically the entity created by test
        $this->deleteEntity('Transaction', 'transactionId', self::$objectId);
    }
}
