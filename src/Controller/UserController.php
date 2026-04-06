<?php

namespace App\Controller;

use App\Document\AbstractUser;
use App\Document\User;
use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Dto\UserFilterDto;
use App\Dto\UserResponseDto;
use App\Service\UserService;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Throwable;

class UserController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    #[OA\Get(
        path: "/api/users",
        description: "List of users with pagination and sorting",
        summary: "Get users list",
        parameters: [
            new OA\Parameter(
                name: 'sort_by',
                description: 'Search term',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'John')
            ),
            new OA\Parameter(
                name: 'order',
                description: 'Sort order: asc or desc',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'asc')
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 20)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of users',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/UserResponseDto')),
                        new OA\Property(property: 'total', type: 'integer'),
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    #[Route('/api/users', methods: ['GET'])]
    public function list(UserFilterDto $filterDto): JsonResponse
    {
        $result = $this->userService->getUsers($filterDto);

        /** @var User[] $users */
        $users = $result['data'];

        $data = array_map(fn(AbstractUser $user) => new UserResponseDto($user->id, $user->name, $user->email), $users);

        return new JsonResponse([
            'items' => $data,
            'total' => $result['total'],
            'page' => $filterDto->page,
            'limit' => $filterDto->limit
        ]);
    }

    #[Route('/api/users/{id}', methods: ['GET'])]
    #[OA\Get(
        path: "/api/users/{id}",
        description: "Get one user.",
        summary: "Get one user by id",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "User ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "User found",
                content: new OA\JsonContent(ref: UserResponseDto::class)
            ),
            new OA\Response(response: 404, description: "User not found")
        ]

    )]
    public function getOne(string $id): JsonResponse
    {
        $user = $this->userService->getOne($id);

        return new JsonResponse(new UserResponseDto($user->id, $user->name, $user->email));
    }

    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    #[Route('/api/users', methods: ['POST'])]
    #[OA\Post(
        path: "/api/users",
        description: "Create new user with data.",
        summary: "Create new user",
        requestBody: new OA\RequestBody(
            description: "Data fot new user",
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateUserDto::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User created successfully',
                content: new OA\JsonContent(ref: new Model(type: UserResponseDto::class))
            )
        ]
    )]
    public function create(CreateUserDto $dto): JsonResponse
    {
        $user = $this->userService->create($dto);

        return new JsonResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ], 201);
    }

    /**
     * @throws MappingException
     * @throws Throwable
     * @throws MongoDBException
     * @throws LockException
     */
    #[Route('/api/users/{id}', requirements: ['id' => '.+'], methods: ['PUT'])]
    #[OA\Put(
        path: "/api/users/{id}",
        description: "Updated user data.",
        summary: "Update existing user data.",
        requestBody: new OA\RequestBody(
            description: "Data fot new user",
            required: true,
            content: new OA\JsonContent(ref: new Model(type: UpdateUserDto::class))
        ),
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "User ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User created successfully',
                content: new OA\JsonContent(ref: new Model(type: UserResponseDto::class))
            ),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    public function update(string $id, UpdateUserDto $dto): JsonResponse
    {
        $user = $this->userService->update($id, $dto);

        return new JsonResponse([
            'id' => $user->id,
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ]);
    }

    /**
     * @throws Throwable
     * @throws MappingException
     * @throws MongoDBException
     * @throws LockException
     */
    #[Route('/api/users/{id}', requirements: ['id' => '.+'], methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/users/{id}",
        description: "Delete user by ID.",
        summary: "Delete user",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "User ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "User deleted successfully.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Deleted")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "User not found.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string")
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function delete(string $id): JsonResponse
    {
        $this->userService->delete($id);

        return new JsonResponse(['message' => 'User deleted successfully']);
    }
}
