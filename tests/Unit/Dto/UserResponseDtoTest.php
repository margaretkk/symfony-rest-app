<?php

namespace App\Tests\Unit\Dto;

use App\Dto\UserResponseDto;
use PHPUnit\Framework\TestCase;

class UserResponseDtoTest extends TestCase
{
    public function testUserResponseDtoCreatesCorrectly(): void
    {
        $dto = new UserResponseDto(
            id: '1',
            name: 'John',
            email: 'john@example.com'
        );

        $this->assertSame('1', $dto->id);
        $this->assertSame('John', $dto->name);
        $this->assertSame('john@example.com', $dto->email);
    }

    public function testUserResponseDtoSetsEmptyEmailIfNull(): void
    {
        $dto = new UserResponseDto(
            id: '1',
            name: 'John',
            email: null
        );

        $this->assertSame('', $dto->email);
    }
}
