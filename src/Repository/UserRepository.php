<?php

namespace App\Repository;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;

readonly class UserRepository
{
    public function __construct(private DocumentManager $dm) {}

    /**
     * @throws MongoDBException
     */
    public function findPaginated(int $page = 1, int $limit = 10, string $sortBy = 'name', string $order = 'asc'): array {
        $skip = ($page - 1) * $limit;

        $direction = strtolower($order) === 'desc' ? -1 : 1;

        return $this->dm->createQueryBuilder(User::class)
            ->sort($sortBy, $direction)
            ->skip($skip)
            ->limit($limit)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * @throws MongoDBException
     */
    public function count(): int
    {
        return $this->dm->createQueryBuilder(User::class)
            ->count()
            ->getQuery()
            ->execute();
    }

    public function findOne(string $id): ?User
    {
        return $this->dm->getRepository(User::class)->find($id);
    }

}
