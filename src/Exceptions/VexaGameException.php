<?php

namespace Rrq\Vexagame\Exceptions;

use Exception;

class VexaGameException extends Exception
{
    protected int $statusCode;
    protected ?array $response;
    protected ?array $request;

    public function __construct(
        string $message = '',
        int $statusCode = 0,
        ?array $response = null,
        ?array $request = null
    ) {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->response = $response;
        $this->request = $request;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function getRequest(): ?array
    {
        return $this->request;
    }

    public function isInsufficientBalance(): bool
    {
        return str_contains($this->message, 'Saldo tidak mencukupi');
    }

    public function isProductOutOfStock(): bool
    {
        return str_contains($this->message, 'stok produk sudah habis');
    }

    public function isInvalidCredentials(): bool
    {
        return str_contains($this->message, 'Invalid credentials');
    }

    public function isProductNotFound(): bool
    {
        return str_contains($this->message, 'Product not found');
    }
}
