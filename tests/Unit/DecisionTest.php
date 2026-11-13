<?php
use PHPUnit\Framework\TestCase;

class DecisionTest extends TestCase {
    public function testCorreoSegunUmbral(): void {
        $this->assertSame('preseleccion', Decision::tipoCorreo(80));
        $this->assertSame('preseleccion', Decision::tipoCorreo(100));
        $this->assertSame('agradecimiento', Decision::tipoCorreo(79));
        $this->assertSame('no_seleccionado', Decision::estadoCandidato(0));
    }
    public function testConfianzaGlobalEsElMinimo(): void {
        $this->assertSame(0.7, Decision::confianzaGlobal(['numero_documento' => 0.9, 'nombres' => 0.7, 'apellidos' => 0.95]));
    }
    public function testCampoIlegibleBajaConfianzaACero(): void {
        $this->assertSame(0.0, Decision::confianzaGlobal(['numero_documento' => 0.9, 'nombres' => 0.9]));
    }
    public function testDocumentoValidado(): void {
        $this->assertSame('validado', Decision::estadoDocumento(0.9, ['doc_ok' => true, 'nombre_ok' => true]));
    }
    public function testConfianzaBajaVaARevisionManual(): void {
        $this->assertSame('revision_manual', Decision::estadoDocumento(0.5, ['doc_ok' => true, 'nombre_ok' => true]));
    }
    public function testDocumentoRechazadoCuandoNadaCoincide(): void {
        $this->assertSame('rechazado', Decision::estadoDocumento(0.9, ['doc_ok' => false, 'nombre_ok' => false]));
    }
    public function testCoincidenciaParcialVaARevisionManual(): void {
        $this->assertSame('revision_manual', Decision::estadoDocumento(0.9, ['doc_ok' => true, 'nombre_ok' => false]));
        $this->assertSame('revision_manual', Decision::estadoDocumento(0.9, ['doc_ok' => null, 'nombre_ok' => true]));
    }
}
