<?php
declare(strict_types=1);

namespace Nargotik\EarlyHints\Tests;

use Nargotik\EarlyHints\EarlyHints;
use PHPUnit\Framework\TestCase;

final class EarlyHintsTest extends TestCase
{
    public function testSends103AndLinks(): void
    {
        $emitter = new ArrayHeaderEmitter();
        $eh = new EarlyHints($emitter, true);

        $ok = $eh->send([
            ['href' => '/a.css', 'rel' => 'preload', 'as' => 'style'],
            ['href' => '/a.js',  'rel' => 'preload', 'as' => 'script', 'crossorigin' => 'anonymous']
        ]);

        $this->assertTrue($ok);

        $all = \array_map(fn($l) => $l['header'], $emitter->lines);
        $this->assertContains('Status: 103 Early Hints', $all);
        $this->assertContains('HTTP/1.1 103 Early Hints', $all);
        $this->assertContains('Link: </a.css>; rel=preload; as=style', $all);
        $this->assertContains('Link: </a.js>; rel=preload; as=script; crossorigin="anonymous"', $all);
    }

    public function testIdempotent(): void
    {
        $emitter = new ArrayHeaderEmitter();
        $eh = new EarlyHints($emitter, true);

        $this->assertTrue($eh->send([['href' => '/a.css', 'rel' => 'preload', 'as' => 'style']]));
        $count = \count($emitter->lines);
        $this->assertTrue($eh->send([['href' => '/b.css', 'rel' => 'preload', 'as' => 'style']]));
        $this->assertSame($count, \count($emitter->lines), 'Second call should be a no-op');
    }

    public function testTooLateIfHeadersSent(): void
    {
        $emitter = new ArrayHeaderEmitter();
        $emitter->headersSent = true;
        $eh = new EarlyHints($emitter);
        $this->assertFalse($eh->send([['href' => '/a.css', 'rel' => 'preload', 'as' => 'style']]));
    }
}
