<?php

namespace App\Tests\Unit\Controller;

use App\Controller\UserController;
use App\Document\AbstractUser;
use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Dto\UserFilterDto;
use App\Service\UserService;
use App\Tests\Unit\Mocks\TestUser;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    /**
     * @throws \JsonException
     */
    public function testGetOne(): void
    {
        $mockUser = new TestUser('1', 'John', 'john@example.com');

        $service = $this->createMock(UserService::class);
        $service->expects($this->once())
            ->method('getOne')
            ->with('1')
            ->willReturn($mockUser);

        $controller = new UserController($service);

        $response = $controller->getOne('1');

        $responseContent = $response->getContent();
        $this->assertIsString($responseContent, 'Response content should be a string');

        $data = json_decode($responseContent ?: '{}', true, 512, JSON_THROW_ON_ERROR);
        /** @var array<string, mixed> $data */
        $this->assertSame('1', $data['id']);
        $this->assertSame('John', $data['name']);
        $this->assertSame('john@example.com', $data['email']);
    }

    /**
     * @throws \JsonException
     */
    public function testGetUsers(): void
    {
        $mockUser = new TestUser('1', 'John', 'john@example.com');

        $service = $this->createMock(UserService::class);
        $service->method('getUsers')->willReturn([
            'data' => [$mockUser],
            'total' => 1,
        ]);

        $controller = new UserController($service);

        $dto = new UserFilterDto(sortBy: 'name', order: 'asc', page: 1, limit: 10);

        $response = $controller->list($dto);

        $responseContent = $response->getContent();
        $this->assertIsString($responseContent, 'Response content should be a string');
        $data = json_decode($responseContent ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        /** @var array<string, mixed> $data */
        $this->assertArrayHasKey('items', $data);

        /** @var array<int, mixed> $items */
        $items = $data['items'];
        $this->assertCount(1, $items);

        $this->assertSame(1, $data['total']);
    }

    public function testCreateReturnsJsonResponse(): void
    {
        $dto = new CreateUserDto(
            name: 'Alice',
            email: 'alice@example.com',
            ip: '127.0.0.1'
        );

        $mockUser = $this->createMock(AbstractUser::class);
        $mockUser->id = '1';
        $mockUser->name = 'Alice';
        $mockUser->email = 'alice@example.com';

        $userService = $this->createMock(UserService::class);
        $userService->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($mockUser);

        $controller = new UserController($userService);

        $response = $controller->create($dto);

        $this->assertSame(201, $response->getStatusCode());

        $expectedData = [
            'id' => '1',
            'name' => 'Alice',
            'email' => 'alice@example.com',
        ];

        $expectedJson = json_encode($expectedData);
        $this->assertIsString($expectedJson, 'Expected data could not be converted to JSON');

        $actualJson = $response->getContent();
        $this->assertIsString($actualJson, 'Response content must be a string');

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }

    public function testDeleteReturnsSuccessMessage(): void
    {
        $userService = $this->createMock(UserService::class);
        $userService->expects($this->once())
            ->method('delete')
            ->with('123');

        $controller = new UserController($userService);

        $response = $controller->delete('123');

        $this->assertSame(200, $response->getStatusCode());

        $expectedData = ['message' => 'User deleted successfully'];
        $expectedJson = json_encode($expectedData);
        $this->assertIsString($expectedJson, 'Expected data could not be converted to JSON');

        $actualJson = $response->getContent();
        $this->assertIsString($actualJson, 'Response content must be a string');

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }

    public function testUpdateReturnsUpdatedUser(): void
    {
        $dto = new UpdateUserDto(name: 'New Name', email: 'new@example.com', ip: '127.0.0.1');

        $mockUser = $this->createMock(AbstractUser::class);
        $mockUser->id = '123';
        $mockUser->method('getName')->willReturn('New Name');
        $mockUser->method('getEmail')->willReturn('new@example.com');

        $userService = $this->createMock(UserService::class);
        $userService->expects($this->once())
            ->method('update')
            ->with('123', $dto)
            ->willReturn($mockUser);

        $controller = new UserController($userService);

        $response = $controller->update('123', $dto);

        $this->assertSame(200, $response->getStatusCode());

        $expectedData = [
            'id' => '123',
            'name' => 'New Name',
            'email' => 'new@example.com',
        ];

        $expectedJson = json_encode($expectedData);
        $this->assertIsString($expectedJson, 'Expected data could not be converted to JSON');

        $actualJson = $response->getContent();
        $this->assertIsString($actualJson, 'Response content must be a string');

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }
}
