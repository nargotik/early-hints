<?php
declare(strict_types=1);

namespace Nargotik\EarlyHints;

if (!\function_exists(__NAMESPACE__ . '\\early_hints')) {
    /**
     * Functional shortcut: returns whether hints were sent (or already sent).
     * @param array<int,array<string,string>> $links
     */
    function early_hints(array $links): bool
    {
        return EarlyHints::global()->send($links);
    }
}
