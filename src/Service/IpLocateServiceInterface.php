<?php

namespace App\Service;

interface IpLocateServiceInterface
{
    public function getCountryByIp(?string $ip): string;
}
