<?php

namespace App\Service;

use AllowDynamicProperties;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AllowDynamicProperties]
class IpLocateService implements IpLocateServiceInterface
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    public function getCountryByIp(?string $ip): string
    {
        if (!$ip) return 'Unknown';

        try {
            $response = $this->httpClient->request(
                'GET',
                "https://www.iplocate.io/api/lookup/$ip"
            );

            $data = $response->toArray();
            if (isset($data['country']) && is_string($data['country'])) {
                return $data['country'];
            }
            return 'Unknown';
        } catch (\Throwable $e) {
            return 'Unknown';
        }
    }

}
