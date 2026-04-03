<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCreateUser(): void
    {
        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'name' => 'Test User',
            'email' => 'test@test.com'
        ]));

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $data);
        $this->assertEquals('Test User', $data['name']);
    }

    public function testGetUsers(): void
    {
        $this->client->request('GET', '/api/users');

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
    }

    public function testGetOneUser(): void
    {
        // create user
        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'name' => 'Single User',
            'email' => 'single@test.com'
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $id = $data['id'];

        // get one user
        $this->client->request('GET', "/api/users/$id");

        $this->assertResponseIsSuccessful();

        $user = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($id, $user['id']);
    }

    public function testUpdateUser(): void
    {
        // create
        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'name' => 'Old Name',
            'email' => 'old@test.com'
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $id = $data['id'];

        // update
        $this->client->request('PUT', "/api/users/$id", [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'name' => 'New Name'
        ]));

        $this->assertResponseIsSuccessful();

        $updated = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('New Name', $updated['name']);
    }

    public function testDeleteUser(): void
    {
        // create
        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'name' => 'Delete Me',
            'email' => 'delete@test.com'
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $id = $data['id'];

        // delete
        $this->client->request('DELETE', "/api/users/$id");

        $this->assertResponseIsSuccessful();

        // check if empty
        $this->client->request('GET', "/api/users/$id");

        $this->assertResponseStatusCodeSame(404);
    }
}
