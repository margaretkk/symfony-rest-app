<?php

namespace App\Service;

use AllowDynamicProperties;
use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[AllowDynamicProperties]
class UserService
{
    public function __construct(private DocumentManager    $dm,
                                private UserRepository     $repository,
                                private ValidatorInterface $validator,
                                HttpClientInterface $httpClient
    ) {
        $this->httpClient = $httpClient;
    }

    /**
     * @throws MongoDBException
     */
    public function getUsers(int $page, int $limit, string $sortBy, string $order): array
    {
        $users = $this->repository->findPaginated($page, $limit, $sortBy, $order);
        $total = $this->repository->count();

        return [
            'data' => $users,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'sort_by' => $sortBy,
                'order' => $order
            ]
        ];
    }

    public function getOne(string $id): ?User
    {
        return $this->repository->findOne($id);
    }

    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    public function create(array $data, string $ip): User
    {
        $user = new User();
        $user->setName($data['name'] ?? '');
        $user->setEmail($data['email'] ?? '');

        $user->setIp($ip);
        $user->setCountry($this->getCountryByIp($ip));

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new \RuntimeException((string)$errors);
        }

        $this->dm->persist($user);
        $this->dm->flush();

        return $user;
    }

    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    public function update(string $id, array $data, string $ip): User
    {
        $user = $this->dm->getRepository(User::class)->find($id);

        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $user->setName($data['name'] ?? $user->getName());
        $user->setEmail($data['email'] ?? $user->getEmail());

        $user->setIp($ip);
        $user->setCountry($this->getCountryByIp($ip));

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new \RuntimeException((string)$errors);
        }

        $this->dm->flush();

        return $user;
    }

    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    public function delete(string $id): void
    {
        $user = $this->dm->getRepository(User::class)->find($id);

        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }
        $this->dm->remove($user);
        $this->dm->flush();
    }

    private function getCountryByIp(?string $ip): string
    {
        if (!$ip) return 'Unknown';

        try {
            $response = $this->httpClient->request(
                'GET',
                "https://www.iplocate.io/api/lookup/$ip"
            );

            $data = $response->toArray();
            return $data['country'] ?? 'Unknown';
        } catch (\Throwable $e) {
            return 'Unknown';
        }
    }
}
