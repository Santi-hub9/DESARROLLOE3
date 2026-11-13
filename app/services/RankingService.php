<?php
// AUTOMATIZACIÓN 3 (antes: workflow n8n "Ranking")
class RankingService {
    static function ejecutar(array $p): array { DB::run('CALL sp_actualizar_ranking()'); return ['ranking' => 'actualizado']; }
}
