<?php

namespace App\Tests\Unit\ArgumentResolver;

use App\ArgumentResolver\UserDTOResolver;
use App\Dto\CreateUserDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserDTOResolverTest extends TestCase
{
    /**
     * @throws \ReflectionException
     * @throws \JsonException
     */
    public function testResolveCreatesDto(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);

        $validator->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $resolver = new UserDTOResolver($validator);


        $data = ['name' => 'John', 'email' => 'john@example.com',];
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['REMOTE_ADDR' => '127.0.0.1'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $argument = new ArgumentMetadata(
            name: 'dto',
            type: CreateUserDto::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null
        );

        $results = iterator_to_array($resolver->resolve($request, $argument));

        $this->assertCount(1, $results);
        $dto = $results[0];

        $this->assertInstanceOf(CreateUserDto::class, $dto);
        $this->assertSame('John', $dto->name);
        $this->assertSame('john@example.com', $dto->email);
        $this->assertSame('127.0.0.1', $dto->ip);
    }

    public function testResolveThrowsOnValidationError(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation(
                    message: 'Invalid email',
                    messageTemplate: '',
                    parameters: [],
                    root: null,
                    propertyPath: 'email',
                    invalidValue: 'invalid'
                )
            ]));

        $resolver = new UserDTOResolver($validator);

        $request = new Request(query: ['name' => 'John', 'email' => 'invalid']);

        $argument = new ArgumentMetadata(
            name: 'dto',
            type: CreateUserDto::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null
        );

        $this->expectException(BadRequestHttpException::class);

        iterator_to_array($resolver->resolve($request, $argument));
    }

    public function testResolveIgnoresNonDto(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $resolver = new UserDTOResolver($validator);

        $request = new Request();
        $argument = new ArgumentMetadata(
            name: 'dto',
            type: 'SomeOtherClass',
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null
        );

        $results = iterator_to_array($resolver->resolve($request, $argument));
        $this->assertEmpty($results);
    }

}
