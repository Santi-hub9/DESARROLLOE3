# TALENTIA – Workflows de n8n

## 0. Preparación
### 0.1 n8n en Docker
```
docker run -d --name n8n -p 5678:5678 ^
  -e N8N_SECURE_COOKIE=false ^
  -e WEBHOOK_URL=http://localhost:5678/ ^
  -e N8N_BLOCK_ENV_ACCESS_IN_NODE=false ^
  -e TALENTIA_UMBRAL=80 ^
  --add-host=host.docker.internal:host-gateway ^
  -v n8n_data:/home/node/.n8n docker.n8n.io/n8nio/n8n
```
(En Linux/macOS reemplaza `^` por `\`.) Abre http://localhost:5678.

### 0.2 Credenciales (Settings → Credentials)
| Nombre | Tipo | Valores |
|---|---|---|
| `TALENTIA Webhook Key` | Header Auth | Name: `X-TALENTIA-KEY` · Value: el mismo `N8N_SECRET` de `config.php` |
| `TALENTIA MySQL` | MySQL | Host `host.docker.internal` · Port 3306 · DB `talentia` · usuario SQL dedicado con permisos mínimos y contraseña única |
| `OpenAI` | Header Auth | Name `Authorization` · Value `Bearer sk-...` |
| `SMTP Talentia` | SMTP | Gmail: smtp.gmail.com, 465, SSL, contraseña de aplicación (o Mailpit/MailHog local para pruebas) |

### 0.3 Variables
- `TALENTIA_UMBRAL` = 80 (variable de entorno, se lee con `{{$env.TALENTIA_UMBRAL}}`).
- URL base del PHP desde Docker: `http://host.docker.internal/talentia/public`.

> MySQL de XAMPP debe aceptar conexiones del contenedor: en `my.ini` verifica que no exista `bind-address=127.0.0.1`
> (o comentarla) y reinicia MySQL. Permite Apache/MySQL en el firewall de Windows.

---
## AUTOMATIZACIÓN 1 – Analizador inteligente de CV
Nombre: `TALENTIA 1 - Analizar CV` · Webhook: `POST /webhook/talentia-analizar-cv`

| # | Nodo | Tipo | Configuración clave |
|---|---|---|---|
| 1 | Webhook CV | Webhook | POST · Path `talentia-analizar-cv` · Auth: Header Auth · **Respond: Immediately** |
| 2 | Marcar procesando | MySQL (Execute Query) | `UPDATE hojas_vida SET estado_procesamiento='procesando' WHERE id={{$json.body.hoja_vida_id}}; ` y otro `UPDATE candidatos SET estado='en_analisis' ...` |
| 3 | Descargar PDF | HTTP Request | GET `{{$('Webhook CV').item.json.body.pdf_url}}` · Response Format: **File** |
| 4 | Extraer texto | Extract From File | Operation: *Extract From PDF* |
| 5 | Preparar prompt | Code (JS) | Limpia texto, limita a 12 000 caracteres, arma el prompt (abajo) |
| 6 | Llamar IA | HTTP Request | POST `https://api.openai.com/v1/chat/completions` · JSON body (abajo) · Credencial OpenAI |
| 7 | Parsear respuesta | Code (JS) | `JSON.parse(choices[0].message.content)` y valida rangos |
| 8 | Guardar análisis | MySQL (Insert) | Tabla `analisis_ia` |
| 9 | Actualizar estados | MySQL (Execute Query) | `UPDATE hojas_vida SET estado_procesamiento='analizado' ...; UPDATE candidatos SET estado=IF(p>=80,'preseleccionado','no_seleccionado')` |
| 10 | Disparar Auto. 2 | HTTP Request | POST `http://localhost:5678/webhook/talentia-enviar-correo` body `{hoja_vida_id}` |
| 11 | Disparar Auto. 3 | HTTP Request | POST `http://localhost:5678/webhook/talentia-actualizar-ranking` (en paralelo desde el nodo 9) |
| E | Error Trigger (workflow aparte o *Error Workflow*) | – | `UPDATE hojas_vida SET estado_procesamiento='error'` + INSERT en `logs_automatizacion` |

Alternativa "n8n detecta el nuevo registro": añade un **Schedule Trigger** (cada 2 min) → MySQL
`SELECT h.id hoja_vida_id ... FROM hojas_vida h WHERE estado_procesamiento='pendiente'` → conecta al nodo 2.
Recupera CVs si n8n estaba apagado cuando se subió el PDF.

### Entrada (PHP → n8n)
```json
{
  "hoja_vida_id": 12,
  "candidato_id": 7,
  "nombre": "Ana Torres",
  "email": "ana@correo.com",
  "vacante": "Desarrollador Web PHP",
  "requisitos": "Mínimo 2 años con PHP y MySQL...",
  "pdf_url": "http://host.docker.internal/talentia/public/index.php?page=api&action=cv&id=12&token=..."
}
```

### Nodo 6 – Body (JSON)
```json
{
  "model": "gpt-4o-mini",
  "temperature": 0.2,
  "response_format": { "type": "json_object" },
  "messages": [
    { "role": "system", "content": "{{ $json.system }}" },
    { "role": "user",   "content": "{{ $json.user }}" }
  ]
}
```
(En el nodo 5 construye `system` y `user` y en el 6 usa *Body → Using JSON* con expresión `={{ JSON.stringify({...}) }}` para evitar problemas de comillas.)

### Prompt del sistema
```
Eres un reclutador senior. Evalúa la hoja de vida frente a la vacante. Responde SOLO un JSON válido con esta forma:
{"puntuacion":0-100,"resumen":"","anios_experiencia":0,"experiencia":"","habilidades_tecnicas":[],
 "formacion":[],"certificaciones":[],"idiomas":[{"idioma":"","nivel":""}],
 "fortalezas":[],"debilidades":[],"recomendacion":"contratar|entrevistar|en_espera|descartar","justificacion":""}
Rúbrica de la puntuación: experiencia relevante 35, habilidades técnicas 30, formación 15, certificaciones 10, idiomas 10.
No inventes datos que no estén en el CV. Responde en español.
```
Prompt de usuario: `VACANTE: {titulo}\nREQUISITOS: {requisitos}\n\nHOJA DE VIDA:\n{texto}`

### Salida de la IA
```json
{
  "puntuacion": 87,
  "resumen": "Desarrolladora full stack con 4 años en PHP y MySQL.",
  "anios_experiencia": 4.0,
  "experiencia": "Empresa X (2021-2025): APIs REST en Laravel...",
  "habilidades_tecnicas": ["PHP","MySQL","Laravel","Git","Docker"],
  "formacion": ["Ingeniería de Sistemas - Universidad Nacional"],
  "certificaciones": ["Zend PHP Certified"],
  "idiomas": [{"idioma":"Inglés","nivel":"B2"}],
  "fortalezas": ["Dominio de PHP","Experiencia en APIs"],
  "debilidades": ["Sin experiencia en cloud"],
  "recomendacion": "entrevistar",
  "justificacion": "Cumple casi todos los requisitos técnicos."
}
```

### Nodo 7 (Code) – parseo seguro
```js
const raw = $input.first().json.choices[0].message.content;
const a = JSON.parse(raw);
const n = Math.max(0, Math.min(100, Math.round(Number(a.puntuacion) || 0)));
const rec = ['contratar','entrevistar','en_espera','descartar'].includes(a.recomendacion) ? a.recomendacion : 'en_espera';
const w = $('Webhook CV').first().json.body;
return [{ json: {
  hoja_vida_id: w.hoja_vida_id, candidato_id: w.candidato_id, puntuacion: n, recomendacion: rec,
  resumen: a.resumen || '', anios_experiencia: Number(a.anios_experiencia) || 0, experiencia: a.experiencia || '',
  habilidades: JSON.stringify(a.habilidades_tecnicas || []), formacion: JSON.stringify(a.formacion || []),
  certificaciones: JSON.stringify(a.certificaciones || []), idiomas: JSON.stringify(a.idiomas || []),
  fortalezas: JSON.stringify(a.fortalezas || []), debilidades: JSON.stringify(a.debilidades || []),
  justificacion: a.justificacion || '', modelo_ia: 'gpt-4o-mini'
}}];
```
Nodo 8: Insert en `analisis_ia`, mapeando cada campo con el mismo nombre de columna.

**Gemini (alternativa):** HTTP POST `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=API_KEY`,
body `{"contents":[{"parts":[{"text":"<system+user>"}]}],"generationConfig":{"responseMimeType":"application/json","temperature":0.2}}`;
la respuesta está en `candidates[0].content.parts[0].text`.

---
## AUTOMATIZACIÓN 2 – Correo automático personalizado
Nombre: `TALENTIA 2 - Correo` · Webhook: `POST /webhook/talentia-enviar-correo`

| # | Nodo | Configuración |
|---|---|---|
| 1 | Webhook Correo | POST · path `talentia-enviar-correo` · Header Auth |
| 2 | Consultar puntuación | MySQL: `SELECT c.id candidato_id, c.nombres, c.email, v.titulo vacante, a.hoja_vida_id, a.puntuacion, a.fortalezas, a.debilidades, a.recomendacion FROM analisis_ia a JOIN hojas_vida h ON h.id=a.hoja_vida_id JOIN candidatos c ON c.id=h.candidato_id JOIN vacantes v ON v.id=c.vacante_id WHERE a.hoja_vida_id={{$json.body.hoja_vida_id}} ORDER BY a.id DESC LIMIT 1` |
| 3 | IF puntuación | `{{$json.puntuacion}}` ≥ `{{$env.TALENTIA_UMBRAL}}` (Number) |
| 4a | Set preselección (true) | `tipo=preseleccion`, `instruccion="Redacta un correo cálido de PRESELECCIÓN: felicita, menciona 2 fortalezas, indica que RR.HH. contactará para agendar entrevista."` |
| 4b | Set agradecimiento (false) | `tipo=agradecimiento`, `instruccion="Redacta un correo empático y respetuoso de AGRADECIMIENTO: no seleccionado por ahora, sugiere 1-2 mejoras basadas en debilidades, invita a postular de nuevo. NO menciones la puntuación numérica."` |
| 5 | IA redacta correo | HTTP OpenAI (igual que Auto. 1) pidiendo JSON `{"asunto":"","cuerpo_html":""}` con nombre, vacante, fortalezas y debilidades |
| 6 | Parsear | Code: JSON.parse, une con datos del candidato |
| 7 | Send Email | SMTP · To `{{$json.email}}` · Subject `{{$json.asunto}}` · HTML `{{$json.cuerpo_html}}` · **Settings → On Error: Continue (using error output)** |
| 8a | Registrar OK | MySQL INSERT `correos_enviados` (estado `enviado`, `fecha_envio=NOW()`) |
| 8b | Registrar fallo | MySQL INSERT con estado `fallido`, `error_mensaje={{$json.error.message}}` |

Entrada: `{ "hoja_vida_id": 12 }` · Salida registrada:
```json
{ "tipo":"preseleccion","destinatario":"ana@correo.com","asunto":"¡Felicitaciones Ana, avanzas en el proceso!",
  "cuerpo":"<p>Hola Ana...</p>","estado_envio":"enviado","fecha_envio":"2026-10-09 10:32:11" }
```

---
## AUTOMATIZACIÓN 3 – Ranking automático
Nombre: `TALENTIA 3 - Ranking` · Webhook: `POST /webhook/talentia-actualizar-ranking`

| # | Nodo | Configuración |
|---|---|---|
| 1 | Webhook Ranking | POST · path `talentia-actualizar-ranking` · Header Auth |
| 2 | Recalcular | MySQL Execute Query: `CALL sp_actualizar_ranking();` |
| 3 | Top 10 | MySQL: `SELECT r.posicion, c.nombres, r.puntuacion, r.estado FROM ranking r JOIN candidatos c ON c.id=r.candidato_id ORDER BY r.posicion LIMIT 10` |
| 4 | Log | MySQL INSERT en `logs_automatizacion` (workflow `ranking`, mensaje "Ranking actualizado") |

Salida:
```json
[{"posicion":1,"nombres":"Ana","puntuacion":92,"estado":"preseleccionado"},
 {"posicion":2,"nombres":"Luis","puntuacion":85,"estado":"preseleccionado"}]
```
El dashboard PHP lee `ranking` y consulta `api&action=stats` cada 10 s: se refleja sin recargar.

---
## Pruebas rápidas
1. Activa los 3 workflows (interruptor *Active*) para usar la URL `/webhook/` (no `/webhook-test/`).
2. Prueba el webhook: `curl -X POST http://localhost:5678/webhook/talentia-actualizar-ranking -H "X-TALENTIA-KEY: tu-clave" -H "Content-Type: application/json" -d "{}"`
3. Sube un PDF desde la web y revisa *Executions* en n8n.
