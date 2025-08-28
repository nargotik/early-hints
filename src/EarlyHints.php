<?php
declare(strict_types=1);

namespace Nargotik\EarlyHints;

/**
 * Minimal, idempotent Early Hints sender for PHP-FPM/Apache/CGI.
 *
 * Usage:
 *   EarlyHints::global()->send([
 *      ['href' => '/assets/app.css', 'rel' => 'preload', 'as' => 'style'],
 *      ['href' => '/assets/app.js',  'rel' => 'preload', 'as' => 'script'],
 *   ]);
 */
final class EarlyHints
{
    /** @var HeaderEmitter */
    private HeaderEmitter $emitter;

    /** @var bool */
    private bool $sent = false;
    private ?bool $assumeSupported;

    public function __construct(?HeaderEmitter $emitter = null, ?bool $assumeSupported = null)
    {
        $this->emitter = $emitter ?? new PhpHeaderEmitter();
        $this->assumeSupported = $assumeSupported;
    }

    /**
     * Returns a lazily created, per-request singleton.
     */
    public static function global(): self
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Whether we should attempt to send Early Hints in this environment.
     * Conservative: no CLI, no headers already sent, allow override via env.
     */
    public function supported(): bool
    {
        if ($this->assumeSupported !== null) {
            return $this->assumeSupported;
        }

        if (\PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg') {
            return false;
        }
        if (\getenv('EARLY_HINTS_DISABLE') === '1') {
            return false;
        }
        if ($this->emitter->headersSent()) {
            return false;
        }
        // We can't truly detect upstream proxy/browser support here.
        // Best-effort: allow by default, it's harmless if dropped.
        return true;
    }

    /**
     * Send a 103 + Link headers. Idempotent within the request.
     *
     * @param array<int,array<string,string>> $links
     *   Each link must include 'href', optional: rel, as, crossorigin, fetchpriority, type, media, imagesrcset, imagesizes
     *
     * @return bool true if sent (or already sent), false if not supported/too late.
     */
    public function send(array $links): bool
    {
        if ($this->sent) {
            return true; // idempotent
        }
        if (!$this->supported()) {
            return false;
        }

        // Some SAPIs accept both forms; second doesn't replace due to "false".
        $this->emitter->header('Status: 103 Early Hints', true);
        $this->emitter->header('HTTP/1.1 103 Early Hints', true);

        foreach ($links as $l) {
            $line = $this->formatLink($l);
            if ($line !== null) {
                $this->emitter->header('Link: ' . $line, false);
            }
        }

        $this->emitter->flush();
        $this->sent = true;
        return true;
    }

    /**
     * Build a RFC-compliant Link header value.
     * @param array<string,string> $l
     */
    private function formatLink(array $l): ?string
    {
        if (empty($l['href'])) {
            return null;
        }

        // Token-ish params: we won't quote "rel" and "as"; quoted-string others.
        $parts = ['<' . $l['href'] . '>'];

        $tokenParams = ['rel', 'as'];
        foreach ($l as $k => $v) {
            if ($k === 'href' || $v === '') {
                continue;
            }
            if (\in_array($k, $tokenParams, true)) {
                $parts[] = $k . '=' . $v;
            } else {
                $parts[] = $k . '="' . $this->escape($v) . '"';
            }
        }
        return \implode('; ', $parts);
    }

    private function escape(string $v): string
    {
        // Basic escaping for quoted-string: backslash-quote
        return \str_replace(['\\', '"'], ['\\\\', '\"'], $v);
    }
}
