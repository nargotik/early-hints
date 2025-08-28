<?php
declare(strict_types=1);

namespace Nargotik\EarlyHints\Tests;

use Nargotik\EarlyHints\HeaderEmitter;

final class ArrayHeaderEmitter implements HeaderEmitter
{
    /** @var array<int,array{header:string,replace:bool}> */
    public array $lines = [];
    public bool $headersSent = false;

    public function header(string $header, bool $replace = true): void
    {
        if ($this->headersSent) {
            throw new \RuntimeException('Headers already sent');
        }
        $this->lines[] = ['header' => $header, 'replace' => $replace];
    }

    public function flush(): void {}
    public function headersSent(): bool { return $this->headersSent; }
}
