<?php
use PHPUnit\Framework\TestCase;

class AiJsonTest extends TestCase {
    public function testQuitaCercasMarkdown(): void {
        $this->assertSame(['a' => 1], AiJson::decodificar("```json\n{\"a\":1}\n```"));
    }
    public function testTextoNoJsonLanzaExcepcion(): void {
        $this->expectException(InvalidArgumentException::class);
        AiJson::decodificar('Claro, aquí tienes el análisis');
    }
    public function testPuntuacionSeAcotaA0_100(): void {
        $this->assertSame(100, AiJson::analisis(['puntuacion' => 250])['puntuacion']);
        $this->assertSame(0, AiJson::analisis(['puntuacion' => -5])['puntuacion']);
    }
    public function testSinPuntuacionFalla(): void {
        $this->expectException(InvalidArgumentException::class);
        AiJson::analisis(['resumen' => 'x']);
    }
    public function testRecomendacionInvalidaPasaAEnEspera(): void {
        $this->assertSame('en_espera', AiJson::analisis(['puntuacion' => 70, 'recomendacion' => 'quizas'])['recomendacion']);
    }
    public function testListasSeGuardanComoJson(): void {
        $r = AiJson::analisis(['puntuacion' => 70, 'fortalezas' => ['PHP', 'SQL']]);
        $this->assertSame('["PHP","SQL"]', $r['fortalezas']);
    }
    public function testCorreoSeSanitiza(): void {
        $r = AiJson::correo(['asunto' => 'Hola', 'cuerpo_html' => '<p>Hola</p><script>alert(1)</script>']);
        $this->assertStringNotContainsString('<script>', $r['cuerpo']);
    }
    public function testCorreoVacioFalla(): void {
        $this->expectException(InvalidArgumentException::class);
        AiJson::correo(['asunto' => '', 'cuerpo_html' => '']);
    }
}
