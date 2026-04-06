<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(collection: "users")]
class User extends AbstractUser
{
    #[MongoDB\Id]
    public string $id;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    public string $name;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email;

    #[MongoDB\Field(type: 'string', nullable: true)]
    public ?string $ip = null;

    #[MongoDB\Field(type: 'string', nullable: true)]
    public ?string $country = null;
}
