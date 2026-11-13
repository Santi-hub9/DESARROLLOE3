<?php
use PHPUnit\Framework\TestCase;

class DocMatcherTest extends TestCase {
    public function testIgnoraTildesYMayusculas(): void {
        $this->assertSame('jose perez nunez', DocMatcher::normalizar('JOSÉ PÉREZ NÚÑEZ'));
    }
    public function testNombreCompletoCoincide(): void {
        $this->assertSame(1.0, DocMatcher::similitudNombre('Juan Perez', 'PEREZ GOMEZ JUAN CARLOS'));
    }
    public function testToleraUnErrorDeOcr(): void {
        $this->assertSame(1.0, DocMatcher::similitudNombre('Maria Gonzalez', 'GONZALES MARIA'));
    }
    public function testNombreDistintoNoCoincide(): void {
        $this->assertLessThan(0.75, DocMatcher::similitudNombre('Juan Perez', 'LUIS RAMIREZ'));
    }
    public function testDocumentoConPuntos(): void {
        $r = DocMatcher::comparar(['numero_documento' => '1.234.567.890', 'nombres' => 'ANA', 'apellidos' => 'RUIZ'],
            ['documento_identidad' => '1234567890', 'nombres' => 'Ana', 'apellidos' => 'Ruiz']);
        $this->assertTrue($r['doc_ok']);
        $this->assertTrue($r['nombre_ok']);
    }
    public function testDocumentoDistinto(): void {
        $r = DocMatcher::comparar(['numero_documento' => '999', 'nombres' => 'ANA', 'apellidos' => 'RUIZ'],
            ['documento_identidad' => '1234567890', 'nombres' => 'Ana', 'apellidos' => 'Ruiz']);
        $this->assertFalse($r['doc_ok']);
    }
    public function testSinDocumentoRegistradoNoEsComparable(): void {
        $r = DocMatcher::comparar(['numero_documento' => '123'], ['documento_identidad' => null, 'nombres' => 'A', 'apellidos' => 'B']);
        $this->assertNull($r['doc_ok']);
    }
}
