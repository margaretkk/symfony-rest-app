<?php

namespace App\Repository;

use App\Document\AbstractUser;
use App\Document\User;

interface UserRepositoryInterface
{
    /**
     * @param int $page
     * @param int $limit
     * @param string $sortBy
     * @param string $order
     * @return User[] Returns array of User objects
     */
    public function findPaginated(int $page, int $limit, string $sortBy, string $order): array;
    public function count(): int;
    public function find(string $id): ?AbstractUser;
    public function save(object $user): void;
    public function delete(object $user): void;

}
