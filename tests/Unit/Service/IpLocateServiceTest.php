<?php

namespace App\Tests\Unit\Service;

use App\Service\IpLocateService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class IpLocateServiceTest extends TestCase
{
    public function testGetCountryByIpNull(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $service = new IpLocateService($http);

        $this->assertSame('Unknown', $service->getCountryByIp(null));
    }

    public function testGetCountryByIpSucceed(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['country' => 'Ukraine']);

        $http = $this->createMock(HttpClientInterface::class);
        $http->method('request')->willReturn($response);

        $service = new IpLocateService($http);

        $this->assertSame('Ukraine', $service->getCountryByIp('1.2.3.4'));
    }

    public function testGetCountryByIpHttpException(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $http->method('request')->willThrowException(new \Exception('Fail'));

        $service = new IpLocateService($http);

        $this->assertSame('Unknown', $service->getCountryByIp('1.2.3.4'));
    }

}
