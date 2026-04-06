<?php

namespace App\Tests\Unit\Service;

use App\Document\User;
use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Dto\UserFilterDto;
use App\Repository\UserRepositoryInterface;
use App\Service\IpLocateService;
use App\Service\UserService;
use App\Tests\Unit\Mocks\TestUser;
use Doctrine\ODM\MongoDB\MongoDBException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserServiceTest extends TestCase
{
    public function testGetUsers(): void
    {
        $user1 = new TestUser('1', 'Alice', 'alice@example.com');
        $user2 = new TestUser('2', 'Bob', 'bob@example.com');

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->method('findPaginated')->willReturn([$user1, $user2]);
        $repository->method('count')->willReturn(2);

        $service = new UserService($repository);

        $filterDto = new UserFilterDto(sortBy: 'name', order: 'asc', page: 1, limit: 10);

        $result = $service->getUsers($filterDto);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertCount(2, $result['data']);
        $this->assertSame(2, $result['total']);

        $this->assertSame('Alice', $result['data'][0]->getName());
        $this->assertSame('Bob', $result['data'][1]->getName());
    }

    public function testGetOne(): void
    {
        $mockUser = new TestUser('123', 'Alice', 'alice@example.com');

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('find')
            ->with('123')
            ->willReturn($mockUser);

        $service = new UserService($repository);

        $result = $service->getOne('123');

        $this->assertSame($mockUser, $result);
        $this->assertSame('123', $result->getId());
        $this->assertSame('Alice', $result->getName());
        $this->assertSame('alice@example.com', $result->getEmail());
    }

    public function testGetOneNotFound(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())->method('find')->with('1')->willReturn(null);

        $service = new UserService($repository);

        $this->expectException(NotFoundHttpException::class);

        $service->getOne('1');
    }

    /**
     * @throws MongoDBException
     * @throws \Throwable
     */
    public function testCreateUser(): void
    {
        $dto = new CreateUserDto(name: 'John', email: 'john@gmail.com', ip: '127.0.0.1');

        $repository = $this->createMock(UserRepositoryInterface::class);

        $repository->method('save')
            ->willReturnCallback(fn(User $user) => $user);

        $ipService = $this->createMock(IpLocateService::class);
        $ipService->method('getCountryByIp')->willReturn('UA');

        $service = new UserService($repository, $ipService);

        $user = $service->create($dto);

        $this->assertSame('John', $user->getName());
        $this->assertSame('john@gmail.com', $user->getEmail());
    }

    public function testCreateUserIpNullReturnsUnknown(): void
    {
        $dto = new CreateUserDto(name: 'John', email: 'john@gmail.com');

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->method('save')
            ->willReturnCallback(fn(User $user) => $user);


        $service = new UserService($repository);

        $dto = new CreateUserDto('Alice', 'alice@example.com', null);

        $user = $service->create($dto);

        $this->assertSame('Alice', $user->getName());
        $this->assertSame('Unknown', $user->getCountry());
    }

    public function testUpdateUser(): void
    {
        $dto = new UpdateUserDto(name: 'New John', email: 'john_new@gmail.com', ip: '192.168.0.6');

        $user = new TestUser(id: '1', name: 'Old', email: 'old@gmail.com');


        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->method('find')->willReturn($user);

        $ipResolver = $this->createMock(IpLocateService::class);
        $ipResolver->method('getCountryByIp')->willReturn('Ukraine');

        $service = new UserService($repository, $ipResolver);

        $updated = $service->update('1', $dto);

        $this->assertSame('New John', $updated->getName());
        $this->assertSame('john_new@gmail.com', $updated->getEmail());
        $this->assertSame('192.168.0.6', $updated->getIp());
        $this->assertSame('Ukraine', $updated->getCountry());
    }

    public function testUpdateNotFound(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())->method('find')->with('1')->willReturn(null);

        $ipService = $this->createMock(IpLocateService::class);

        $service = new UserService($repository, $ipService);

        $this->expectException(NotFoundHttpException::class);

        $dto = new UpdateUserDto;

        $service->update('1', $dto);
    }
    public function testDelete(): void
    {
        $mockUser = new TestUser('123', 'Alice', 'alice@example.com');

        $repository = $this->createMock(UserRepositoryInterface::class);

        $repository->expects($this->once())
            ->method('find')
            ->with('123')
            ->willReturn($mockUser);

        $repository->expects($this->once())
            ->method('delete')
            ->with($mockUser);

        $service = new UserService($repository);

        $service->delete('123');
    }

    public function testDeleteNotFound(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('find')
            ->with('1')
            ->willReturn(null);

        $service = new UserService($repository);

        $this->expectException(NotFoundHttpException::class);

        $service->delete('1');
    }
}
