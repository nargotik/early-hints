<?php
declare(strict_types=1);

namespace Nargotik\EarlyHints;

interface HeaderEmitter
{
    /**
     * Send a header line.
     * @param string $header
     * @param bool $replace
     * @return void
     */
    public function header(string $header, bool $replace = true): void;

    /**
     * Flush output to client if possible.
     * @return void
     */
    public function flush(): void;

    /**
     * Returns true if headers have already been sent.
     */
    public function headersSent(): bool;
}