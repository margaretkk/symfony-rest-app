<?php

namespace App\Repository;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\MongoDBException;

readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(private DocumentManager $dm) {}

    /**
     * @param int $page
     * @param int $limit
     * @param string $sortBy
     * @param string $order
     * @return User[]  Returns array of User objects
     * @throws MongoDBException
     */
    public function findPaginated(int $page = 1, int $limit = 10, string $sortBy = 'name', string $order = 'asc'): array
    {
        $skip = ($page - 1) * $limit;

        $direction = strtolower($order) === 'desc' ? -1 : 1;

        $query = $this->dm->createQueryBuilder(User::class)
            ->sort($sortBy, $direction)
            ->skip($skip)
            ->limit($limit)
            ->getQuery();

        $result = $query->execute();

        $users = [];
        if (is_iterable($result)) {
            foreach ($result as $user) {
                if ($user instanceof User) {
                    $users[] = $user;
                }
            }
        }

        return $users;
    }

    /**
     * @throws MongoDBException
     */
    public function count(): int
    {
        $result = $this->dm->createQueryBuilder(User::class)
            ->count()
            ->getQuery()
            ->execute();

        if (is_int($result)) {
            return $result;
        }

        if (is_array($result) && isset($result['count']) && is_numeric($result['count'])) {
            return (int) $result['count'];
        }

        return 0;
    }

    /**
     * @throws MappingException
     * @throws LockException
     */
    public function find(string $id): ?User
    {
        return $this->dm->getRepository(User::class)->find($id);
    }

    /**
     * @throws \Throwable
     * @throws MongoDBException
     */
    public function save(object $user): void
    {
        $this->dm->persist($user);
        $this->dm->flush();
    }

    /**
     * @throws MongoDBException
     * @throws \Throwable
     */
    public function delete(object $user): void
    {
        $this->dm->remove($user);
        $this->dm->flush();
    }
}
