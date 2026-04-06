<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

class UserFilterDto
{
    public function __construct(
        #[OA\Property(description: "Sort field (name or email)", example: "name")]
        public ?string $sortBy = null,

        #[OA\Property(description: "Sort order: asc or desc", example: "asc")]
        public ?string $order = null,

        #[OA\Property(description: "Page number from 1", example: "1")]
        public ?int $page = 1,

        #[OA\Property(description: "Users number on page, 50 max", example: "20")]
        public ?int $limit = 50
    ) {}
}
