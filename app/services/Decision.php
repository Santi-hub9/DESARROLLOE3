<?php
// Reglas de negocio puras (sin base de datos ni IA): fáciles de probar con PHPUnit.
class Decision {
    static function tipoCorreo(int $puntuacion, int $umbral = 80): string { return $puntuacion >= $umbral ? 'preseleccion' : 'agradecimiento'; }
    static function estadoCandidato(int $puntuacion, int $umbral = 80): string { return $puntuacion >= $umbral ? 'preseleccionado' : 'no_seleccionado'; }

    // Confianza global = la del campo requerido menos confiable (un campo ilegible baja todo a 0)
    static function confianzaGlobal(array $conf): float {
        $min = 1.0;
        foreach (['numero_documento', 'nombres', 'apellidos'] as $k) $min = min($min, (float)($conf[$k] ?? 0));
        return round(max(0.0, min(1.0, $min)), 3);
    }

    // Decisión sobre el documento. Si la confianza es baja o hay coincidencia parcial, lo decide una persona.
    static function estadoDocumento(float $confianza, array $match, float $umbral = 0.75): string {
        if ($confianza < $umbral || $match['doc_ok'] === null) return 'revision_manual';
        if ($match['doc_ok'] && $match['nombre_ok']) return 'validado';
        if (!$match['doc_ok'] && !$match['nombre_ok']) return 'rechazado';
        return 'revision_manual';
    }
}
