<?php

namespace App\Services\Payments;

use RuntimeException;

/**
 * A failed CHIP Collect request.
 *
 * The message is built from the HTTP status and CHIP's error code only. It
 * never contains the bearer token, the request body or the response body, so
 * it is safe to log and to pass to AdminNotifier.
 */
class ChipException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 0,
        public readonly ?string $chipCode = null,
        public readonly string $operation = '',
    ) {
        parent::__construct($message);
    }

    public function isNotFound(): bool
    {
        return $this->httpStatus === 404 && $this->operation === 'getPurchase';
    }

    public function isAuthentication(): bool
    {
        return in_array($this->httpStatus, [401, 403], true);
    }

    public function isConnection(): bool
    {
        return $this->httpStatus === 0;
    }
}
