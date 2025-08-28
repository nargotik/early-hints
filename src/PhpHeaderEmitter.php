<?php
declare(strict_types=1);

namespace Nargotik\EarlyHints;

final class PhpHeaderEmitter implements HeaderEmitter
{
    public function header(string $header, bool $replace = true): void
    {
        @\header($header, $replace);
    }

    public function flush(): void
    {
        // Avoid notices in SAPIs that don't support it.
        if (\function_exists('flush')) {
            @\flush();
        }
    }

    public function headersSent(): bool
    {
        return \headers_sent();
    }
}
