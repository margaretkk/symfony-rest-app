<?php

namespace App\Service;

use AllowDynamicProperties;
use App\Document\AbstractUser;
use App\Document\User;
use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Dto\UserFilterDto;
use App\Repository\UserRepositoryInterface;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

#[AllowDynamicProperties]
class UserService
{
    public function __construct(private UserRepositoryInterface $repositoryInterface,
                                private ?IpLocateService        $ipService = null
    ) {}


    /**
     * @param UserFilterDto $filterDto
     * @return array{
     *     data: User[],
     *     total: int
     * }
     */
    public function getUsers(UserFilterDto $filterDto): array
    {
        $users = $this->repositoryInterface->findPaginated(
            $filterDto->page ?? 1,
            $filterDto->limit ?? 10,
            $filterDto->sortBy ?? 'name',
            $filterDto->order ?? 'acs'
        );
        $total = $this->repositoryInterface->count();

        return [
            'data' => $users,
            'total' => $total,
        ];
    }

    public function getOne(string $id): AbstractUser
    {
        $user = $this->repositoryInterface->find($id);

        if ($user === null) {
            throw new NotFoundHttpException(
                "User not found"
            );
        }
        return $user;
    }

    /**
     * @throws Throwable
     */
    public function create(CreateUserDto $dto): AbstractUser
    {
        $user = new User();
        $user->setName($dto->name);
        $user->setEmail($dto->email);
        $user->setIp($dto->ip);

        if ($this->ipService !== null) {
            $user->setCountry($this->ipService->getCountryByIp($dto->ip));
        } else {
            $user->setCountry('Unknown');
        }

        $this->repositoryInterface->save($user);

        return $user;
    }

    /**
     * @throws Throwable
     */
    public function update(string $id, UpdateUserDto $dto): AbstractUser
    {
        $user = $this->repositoryInterface->find($id);

        if ($user === null) {
            throw new NotFoundHttpException(
                "User not found"
            );
        }

        if ($dto->name !== null) $user->setName($dto->name);
        if ($dto->email !== null) $user->setEmail($dto->email);

        if ($dto->ip !== null && $this->ipService !== null) {
            $user->setIp($dto->ip);
            $user->setCountry($this->ipService->getCountryByIp($dto->ip));
        }

        $this->repositoryInterface->save($user);

        return $user;
    }

    /**
     * @throws Throwable
     */
    public function delete(string $id): void
    {
        $user = $this->repositoryInterface->find($id);

        if ($user === null) {
            throw new NotFoundHttpException(
                "User not found"
            );
        }

        $this->repositoryInterface->delete($user);
    }
}
