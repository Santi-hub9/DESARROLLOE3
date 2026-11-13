<?php
use PHPUnit\Framework\TestCase;

class BackoffTest extends TestCase {
    public function testEsperaCreciente(): void {
        $this->assertSame([30, 120, 600, 1800, 1800], array_map([Backoff::class, 'segundos'], [1, 2, 3, 4, 9]));
    }
}
