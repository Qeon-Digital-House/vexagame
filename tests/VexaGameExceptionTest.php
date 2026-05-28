<?php

namespace Rrq\Vexagame\Tests;

use PHPUnit\Framework\TestCase;
use Rrq\Vexagame\Exceptions\VexaGameException;

class VexaGameExceptionTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $request = ['code' => 'FF5', 'customer_no' => '123'];
        $response = ['code' => 400, 'message' => 'Error'];

        $exception = new VexaGameException('Test error', 400, $response, $request);

        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals(400, $exception->getStatusCode());
        $this->assertEquals($response, $exception->getResponse());
        $this->assertEquals($request, $exception->getRequest());
    }

    public function testConstructorWithNullParams(): void
    {
        $exception = new VexaGameException('Error', 500);

        $this->assertEquals('Error', $exception->getMessage());
        $this->assertEquals(500, $exception->getStatusCode());
        $this->assertNull($exception->getResponse());
        $this->assertNull($exception->getRequest());
    }

    public function testIsInsufficientBalance(): void
    {
        $exception = new VexaGameException('Saldo tidak mencukupi', 400);
        $this->assertTrue($exception->isInsufficientBalance());
    }

    public function testIsProductOutOfStock(): void
    {
        $exception = new VexaGameException('Maaf stok produk sudah habis', 400);
        $this->assertTrue($exception->isProductOutOfStock());
    }

    public function testIsInvalidCredentials(): void
    {
        $exception = new VexaGameException('Invalid credentials!', 400);
        $this->assertTrue($exception->isInvalidCredentials());
    }

    public function testIsProductNotFound(): void
    {
        $exception = new VexaGameException('Product not found', 404);
        $this->assertTrue($exception->isProductNotFound());
    }

    public function testHelpersReturnFalseForUnrelatedMessage(): void
    {
        $exception = new VexaGameException('Some random error', 500);

        $this->assertFalse($exception->isInsufficientBalance());
        $this->assertFalse($exception->isProductOutOfStock());
        $this->assertFalse($exception->isInvalidCredentials());
        $this->assertFalse($exception->isProductNotFound());
    }

    public function testIsException(): void
    {
        $exception = new VexaGameException('Error', 400);
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
