<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserResponseDto",
    title: "User Response",
    description: "DTO for user response"
)]
class UserResponseDto
{
    #[OA\Property(description: "User ID", example: '1')]
    public string $id;

    #[OA\Property(description: "User name", example: "John")]
    public string $name;

    #[OA\Property(description: "User email", example: "john@example.com")]
    public string $email;
    public function __construct(string $id, string $name, ?string $email)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email ?? '';
    }
}
