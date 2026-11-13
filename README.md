# TALENTIA – Reclutamiento inteligente (Tercer corte, Ruta A)
Aplicación web en PHP + MySQL con **motor de automatización propio** (cola + worker, sin n8n) y **módulo de visión** para verificar documentos.

## Qué hay
| Carpeta | Contenido |
|---|---|
| `app/core` | cola (`Queue`), cliente de IA con plan B (`LLM`), auditoría, correo |
| `app/services` | los 3 flujos migrados + verificación de documentos + reglas puras |
| `bin/worker.php` | el motor (cron o `--loop`) |
| `bin/evaluar_vision.php` | mide el % de aciertos con fotos reales |
| `tests/Unit` | pruebas PHPUnit (`composer test`) |
| `database/` | `talentia.sql` + `migracion_corte3.sql` |
| `docs/` | documento técnico (borrador) y guía de despliegue |

## Instalación rápida
Ver `docs/DESPLIEGUE.md` (Docker en servidor, o XAMPP local).

## Medir el módulo de visión (mínimo 20 fotos reales)
1. Toma 20+ fotos con el celular a cédulas (con permiso; idealmente las de tu grupo/familia) y cópialas a `tests/vision/imagenes/`.
2. Copia `tests/vision/ground_truth.example.csv` a `tests/vision/ground_truth.csv` y escribe los datos reales de cada foto.
3. `php bin/evaluar_vision.php` (añade `--externo` solo si tienes permiso para enviarlas a Gemini).
4. Pega los porcentajes en el documento técnico.

## Variables de entorno
Ver `.env.example`. Las claves nunca van en el código.
