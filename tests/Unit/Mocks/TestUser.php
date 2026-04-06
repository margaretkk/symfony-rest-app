<?php

namespace App\Tests\Unit\Mocks;

use App\Document\AbstractUser;

class TestUser extends AbstractUser
{
    public function __construct(string $id, string $name = '', ?string $email = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }
}
