<?php

namespace App\Document;

abstract class AbstractUser
{
    public string $id;
    public string $name = '';
    public ?string $email = null;
    protected ?string $ip = null;
    protected ?string $country = null;

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }
    public function getIp(): ?string { return $this->ip; }
    public function setIp(?string $ip): self { $this->ip = $ip; return $this; }
    public function getCountry(): ?string { return $this->country; }
    public function setCountry(?string $country): self { $this->country = $country; return $this; }
}
