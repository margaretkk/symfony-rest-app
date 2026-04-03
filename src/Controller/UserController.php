<?php

namespace App\Controller;

use App\Service\UserService;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Throwable;

class UserController extends AbstractController
{
    public function __construct(private readonly UserService $userService,) {}

    /**
     * @throws MongoDBException
     */
    #[OA\Get(
        path: "/api/users",
        description: "List of users with pagination and sorting",
        summary: "Get users list",
        parameters: [
            new OA\Parameter(
                name: "page",
                description: "Page number from 1",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "limit",
                description: "Users number on page, 50 max",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 10)
            ),
            new OA\Parameter(
                name: "sort_by",
                description: "Sort field (name or email)",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", default: "name")
            ),
            new OA\Parameter(
                name: "order",
                description: "Sort order: asc or desc",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string", default: "asc")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Users list",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", description: "User ID", type: "string"),
                            new OA\Property(property: "name", description: "User name", type: "string"),
                            new OA\Property(property: "email", description: "User email", type: "string")
                        ]
                    )
                )
            )
        ]
    )]
    #[Route('/api/users', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(50, (int)$request->query->get('limit', 10));

        $sortBy = $request->query->get('sort_by', 'name');
        $order = $request->query->get('order', 'asc');

        $result = $this->userService->getUsers($page, $limit, $sortBy, $order);

        return $this->json($result);
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
                description: "User has been found.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "string"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "email", type: "string")
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
    public function getOne(string $id): JsonResponse
    {
        $user = $this->userService->getOne($id);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        return $this->json($user);
    }

    #[Route('/api/users', methods: ['POST'])]
    #[OA\Post(
        path: "/api/users",
        description: "Create new user with data.",
        summary: "Create new user",
        requestBody: new OA\RequestBody(
            description: "Data fot new user",
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com")
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User created successfully.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "string"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "email", type: "string")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 400,
                description: "User creating failed",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string")
                    ],
                    type: "object"
                )
            ),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ip = $request->getClientIp() ?? '0.0.0.0';

        try {
            $user = $this->userService->create($data, $ip);

            return $this->json([
                'id' => (string)$user->id,
                'name' => $user->name,
                'email' => $user->email,
            ], 201);
        } catch (MongoDBException|Throwable $e) {
            return $this->json(['error' => 'User creation failed',], 400);
        }
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
            description: "Data for update existing user data.",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Updated"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "updated@example.com")
                ],
                type: "object"
            )
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
                description: "User successfully updated.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "string"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "email", type: "string")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 400,
                description: "Validation failure or not acceptable data",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string")
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
    public function update(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ip = $request->getClientIp() ?? '0.0.0.0';
        try {
            $user = $this->userService->update($id, $data, $ip);

            return $this->json([
                'id' => (string)$user->id,
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
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
                response: 400,
                description: "Error when deleting user.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string")
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
        try {
            $this->userService->delete($id);

            return $this->json(['message' => 'Deleted']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
