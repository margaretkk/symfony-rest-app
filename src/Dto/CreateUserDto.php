<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class CreateUserDto
{
    public function __construct(
        #[OA\Property(description: "Name of the user", example: "John")]
        #[Assert\NotBlank]
        public string $name,

        #[OA\Property(description: "Email of the user", example: "john@gmail.com")]
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[OA\Property(description: "IP address", example: "127.0.0.1")]
        public ?string $ip = null
    ) {}
}
