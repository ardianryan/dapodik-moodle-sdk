<?php

namespace Smansage\DapodikMoodle\Tests;

use PHPUnit\Framework\TestCase;
use Smansage\DapodikMoodle\DapodikHttpClient;

class BridgeTest extends TestCase
{
    public function testSecurityCrlfPreventionOnNpsn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DapodikHttpClient(
            npsn: "20300001\r\n",
            token: "valid-token"
        );
    }

    public function testSecurityCrlfPreventionOnToken(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DapodikHttpClient(
            npsn: "20300001",
            token: "valid-token\n"
        );
    }

    public function testSecurityPathTraversalPrevention(): void
    {
        $client = new DapodikHttpClient(
            npsn: "20300001",
            token: "valid-token"
        );

        $this->expectException(\InvalidArgumentException::class);
        $client->request('../../etc/passwd');
    }
}
