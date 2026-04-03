<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(collection: "users")]
class User
{
    #[MongoDB\Id]
    public ?string $id = null;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    public string $name;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[MongoDB\Field(type: 'string', nullable: true)]
    private ?string $ip = null;

    #[MongoDB\Field(type: 'string', nullable: true)]
    private ?string $country = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name; return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email; return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;
        return $this;
    }
}
