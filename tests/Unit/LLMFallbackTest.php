<?php
use PHPUnit\Framework\TestCase;

// Plan B: si el primer proveedor falla (cuota agotada, red), responde el siguiente.
class LLMFallbackTest extends TestCase {
    protected function setUp(): void { Audit::$activo = false; }
    protected function tearDown(): void { LLM::$fake = []; Audit::$activo = true; }

    public function testUsaElSegundoProveedorSiFallaElPrimero(): void {
        LLM::$fake = [
            'gemini' => fn() => throw new RuntimeException('Gemini respondió HTTP 429 (cuota agotada)'),
            'ollama' => fn() => ['{"puntuacion":70}', 'modelo-local'],
        ];
        $r = LLM::generar('s', 'u');
        $this->assertSame('ollama', $r['proveedor']);
        $this->assertSame(70, $r['datos']['puntuacion']);
    }
    public function testJsonInvalidoTambienPasaAlSiguiente(): void {
        LLM::$fake = ['a' => fn() => ['texto libre', 'm1'], 'b' => fn() => ['{"ok":true}', 'm2']];
        $this->assertSame('b', LLM::generar('s', 'u')['proveedor']);
    }
    public function testSiTodosFallanLanzaErrorClaro(): void {
        LLM::$fake = ['a' => fn() => throw new RuntimeException('caído'), 'b' => fn() => throw new RuntimeException('sin red')];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Todos los proveedores de IA fallaron');
        LLM::generar('s', 'u');
    }
}
