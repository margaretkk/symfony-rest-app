<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class UpdateUserDto
{
    public function __construct(
        #[OA\Property(description: "New name of the user", example: "New John")]
        public ?string $name = null,

        #[OA\Property(description: "New email of the user", example: "john_new@gmail.com")]
        #[Assert\Email]
        public ?string $email = null,

        #[OA\Property(description: "IP address", example: "192.168.0.6")]
        public ?string $ip = null
    ) {}
}
