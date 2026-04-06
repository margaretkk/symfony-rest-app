<?php

namespace App\ArgumentResolver;

use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserDTOResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {}

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable<object>
     * @throws ReflectionException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if (!$type || !str_starts_with($type, 'App\Dto')) {
            return [];
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($request->getContent(), true) ?? [];

        /** @var array<string, mixed> $routeParams */
        $routeParams = $request->attributes->get('_route_params', []);
        $data = array_merge($data, $routeParams);

        /** @var array<string, mixed> $queryParams */
        $queryParams = $request->query->all();
        $data = array_merge($data, $queryParams);

        /** @var class-string<object> $type */
        assert(class_exists($type));

        $dto = new $type(...$this->mapToConstructor($type, $data));

        if (property_exists($dto, 'ip')) {
            $dto->ip = $request->getClientIp();
        }

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        yield $dto;
    }

    /**
     * Maps data array to constructor parameters of a class
     *
     * @param class-string<object> $class
     * @param array<string, mixed> $data
     * @return array<mixed>
     * @throws ReflectionException
     */
    private function mapToConstructor(string $class, array $data): array
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return [];
        }

        $params = [];

        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            $params[] = $data[$name] ?? null;
        }

        return $params;
    }

}
