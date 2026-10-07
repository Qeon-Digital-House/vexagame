<?php

namespace Rrq\Vexagame\Events;

/**
 * Fired after every call to the VexaGame API, successful or not.
 *
 * Carries what a request log needs: the request as sent (with `pin` redacted)
 * and what came back. The Authorization header is never included.
 */
class RequestCompleted
{
    public function __construct(
        public readonly string $method,
        public readonly string $url,
        public readonly array $params,
        public readonly ?int $statusCode,
        public readonly ?array $response,
        public readonly ?string $error,
        public readonly float $durationMs,
    ) {
    }

    public function successful(): bool
    {
        return $this->error === null;
    }
}
