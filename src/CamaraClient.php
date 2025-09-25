<?php

namespace InfiniteLoop\CamaraClient;

class CamaraClient
{
    public function sayHello(string $name = 'World'): string
    {
        return "Hello, {$name}! This is from the CamaraClient package.";
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getPackageInfo(): array
    {
        return [
            'name' => 'CamaraClient',
            'vendor' => 'InfiniteLoop',
            'version' => $this->getVersion(),
            'description' => 'A simple Hello World Composer package'
        ];
    }
}