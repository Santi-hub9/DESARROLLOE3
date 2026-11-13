# Despliegue (servidor accesible por internet)
La guía exige que todo corra en un servidor. Opción recomendada: **una máquina virtual gratuita con Docker** (por ejemplo Oracle Cloud Free Tier u otro VPS). Verifica las condiciones vigentes del proveedor antes de elegir: los planes gratuitos cambian.

## Pasos (VM Ubuntu con IP pública)
1. Instala Docker: `curl -fsSL https://get.docker.com | sh`
2. Abre los puertos 80 (y 443 si usas dominio/HTTPS) en el firewall del proveedor.
3. Clona el repositorio: `git clone <url> && cd talentia`
4. `cp .env.example .env` y edita: `GEMINI_API_KEY`, `CRON_TOKEN` (aleatorio y largo), `MAIL_DRIVER`/`SMTP_*` (o deja `log`) y define un `DB_ROOT_PASS` fuerte. Docker Compose no arranca si falta esa contraseña.
5. `docker compose up -d --build`  (levanta MariaDB, la app y el **worker**; importa los dos SQL automáticamente).
6. Entra a `http://IP-DEL-SERVIDOR/` · admin: `admin@talentia.local` / `Admin123*` → **cámbiala**.
7. (Opcional, plan B local) `docker compose --profile local-ai up -d` y luego `docker compose exec ollama ollama pull llama3.1` y `ollama pull llama3.2-vision`. Pone `OLLAMA_URL=http://ollama:11434` en `.env`. Necesita bastante RAM; en máquinas pequeñas usa un modelo más liviano o déjalo solo como respaldo.

## Si el hosting no permite un proceso en segundo plano
Usa el endpoint `GET /index.php?page=api&action=tick&token=TU_CRON_TOKEN` y llámalo cada minuto con un cron externo gratuito (p. ej. cron-job.org). Procesa hasta 3 tareas por llamada.

## HTTPS
Para producción real pon HTTPS (Caddy o Nginx con Let's Encrypt delante). Necesitas un dominio.

## Desarrollo local en XAMPP
```
composer install
copy .env.example .env         (MAIL_DRIVER=log, OLLAMA_URL vacío si no usas Ollama)
phpMyAdmin: importar database/talentia.sql y luego database/migracion_corte3.sql
C:\xampp\php\php.exe bin\worker.php --loop      (otra ventana de consola)
composer test
```
Si ya tenías la base del corte anterior, importa solo `migracion_corte3.sql`.
