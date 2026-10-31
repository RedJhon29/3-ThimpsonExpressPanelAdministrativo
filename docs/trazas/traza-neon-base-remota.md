# Traza: base `thimpsonexpress` replicada en Neon y cadena de conexión fuera del código

**Fecha:** 2026-10-30 · **Proyecto:** 3-ThimpsonExpressPanelAdministrativo · **Estado:** ✅ cumplido (etapa 1 de 3)

## Resumen ejecutivo

1. La base local `thimpsonexpress` (PostgreSQL 16.15) se replicó **idéntica** en Neon: mismas columnas, 5 índices, el mismo registro y la secuencia en 5, verificado consulta por consulta contra ambas bases.
2. La cadena de conexión salió del código: vive en `.env` (ignorado por git y bloqueado por `.htaccess`), con `.env.example` versionado como plantilla.
3. Se creó un rol de aplicación `thimpson_app` con permisos mínimos (DML sí, superusuario y `CREATEDB` no) en vez de usar el rol dueño.
4. El panel **ya consulta Neon** desde Apache: un POST a `/login/authenticate` subió `seq_scan` en Neon (9→10) y dejó intacto el contador local.
5. Compuertas: `php -l` 23/23, `scripts/higiene.ps1` sin problemas, guardián de estilo **0 errores / 0 avisos** en los archivos nuevos, `.env` confirmado como ignorado.

## Criterios de aceptación

| Criterio | Estado | Evidencia |
|---|---|---|
| Base remota idéntica a la local | ✅ | columnas, 5 índices, `md5(clave_usuario)` y `last_value` de secuencia iguales en ambas |
| Misma versión de motor | ✅ | `PostgreSQL 16.15` en local y en Neon |
| Conexión por endpoint directo (sin pooler) | ✅ | API de Neon: `pooler_enabled: false` |
| Cadena fuera del código y fuera de git | ✅ | `git check-ignore -v .env` → `.gitignore:48` |
| Plantilla versionable para clones nuevos | ✅ | `.env.example` no ignorado |
| Rol de app sin privilegios de dueño | ✅ | `rolsuper=f`, `has_database_privilege(CREATE)=f`, `SELECT=t`, `DELETE=t` |
| El panel web usa la base remota | ✅ | `seq_scan` en Neon 9→10 tras un POST real; local sin cambios |
| No romper el proyecto | ✅ | lint 23/23, higiene 5/5 sin problemas, `/login` → HTTP 200 |
| Banner `estilo-codigo` en lo nuevo | ✅ | guardián: 0 error, 0 aviso, 0 nota en los 4 archivos nuevos |

---

## Fases

### Fase 1 — Diagnóstico de la base local y respaldo

**Qué se hizo:** antes de tocar la nube se midió exactamente qué había que copiar.

| Hallazgo | Valor |
|---|---|
| Motor local | PostgreSQL **16.15**, escuchando solo en `127.0.0.1:5432` |
| Autenticación local | `pg_hba.conf` con método `trust` |
| Tablas | `usuarios` (1 fila) |
| Índices | 5: `usuarios_pkey`, `usuarios_nick_name_key`, `idx_usuarios_nick`, `idx_usuarios_tipo`, `idx_usuarios_ultimo_login` |
| Secuencia | `usuarios_id_usuario_seq`, `last_value = 5` |
| Extensiones | solo `plpgsql` |

**Archivo generado:** `C:\Users\DELL\Documents\respaldos\thimpsonexpress_2026-10-30.dump` (formato custom, compresión 9, 4 018 bytes, 13 entradas TOC).

> **Si alguien altera esta parte:** el dump es la fuente de verdad de la migración. Si la base local cambia después de esta fecha, hay que regenerarlo o los dos entornos divergen.

### Fase 2 — Elección de proveedor y de versión de motor

**Decisión clave:** Neon en free tier, no Supabase (se pausa por inactividad) ni exponer la PC con un túnel.

El proyecto ya existía en la consola del usuario, pero **en PostgreSQL 18** con la base vacía `neondb`. Se descartó dejarlo así: un dump de PG 16 restaurado en PG 18 no se puede devolver a PG 16, y la réplica debía viajar en **ambas direcciones**. Como el proyecto no tenía datos, se borró y se recreó:

| Parámetro | Valor final |
|---|---|
| Proyecto | `young-pond-81729558` |
| Región | `aws-us-east-1` |
| Motor | PostgreSQL **16** |
| Base | `thimpsonexpress` |
| Owner | `thimpsonexpress_owner` |
| Endpoint | `ep-long-dawn-b7ebfwl0` (read_write, directo) |
| Pooler | **deshabilitado** |

**Alternativa descartada — el pooler de Neon:** `PDO::ATTR_EMULATE_PREPARES => false` exige prepared statements reales; el pooler en modo transacción no garantiza que `PREPARE` y `EXECUTE` caigan en la misma sesión. Con el endpoint directo el problema no existe.

> **Si alguien altera esta parte:** cambiar a PG 18 o al pooler obliga a revisar `Configuracion/conexion.php` y a regenerar el dump con el `pg_dump` de la versión destino.

### Fase 3 — Restore y verificación de igualdad

**Qué se hizo:** `pg_restore --no-owner --no-privileges` contra la cadena del owner, y después la **misma batería de consultas** contra local y Neon.

```powershell
# Restore (la cadena se lee de un archivo, nunca se imprime)
$cs = (Get-Content 'C:\Users\DELL\.neon-admin-url' -Raw).Trim()
& 'C:\Program Files\PostgreSQL\16\bin\pg_restore.exe' --dbname=$cs --no-owner --no-privileges `
  'C:\Users\DELL\Documents\respaldos\thimpsonexpress_2026-10-30.dump'
```

**Resultado:** 8 objetos creados, 0 errores. Comparación lado a lado:

| Aspecto | Local | Neon | Igual |
|---|---|---|---|
| 8 columnas con tipo, nulabilidad, default y largo | ✅ | ✅ | sí |
| 5 índices con su `indexdef` | ✅ | ✅ | sí |
| Fila `id_usuario=1`, `md5(clave_usuario)` | `cc1d35fb…` | `cc1d35fb…` | sí |
| `usuarios_id_usuario_seq.last_value` | 5 | 5 | sí |

> **Si alguien altera esta parte:** `--no-owner` es deliberado. Sin él, el restore intentaría crear objetos a nombre de `postgres`, que no existe en Neon.

### Fase 4 — Rol de aplicación con permisos mínimos

**Qué se hizo:** en vez de que la app use el rol dueño, se creó `thimpson_app` con contraseña aleatoria de 32 caracteres generada con `RandomNumberGenerator` (alfabeto sin caracteres ambiguos).

```sql
CREATE ROLE thimpson_app LOGIN PASSWORD '<generada>';
GRANT CONNECT ON DATABASE thimpsonexpress TO thimpson_app;
GRANT USAGE ON SCHEMA public TO thimpson_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO thimpson_app;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO thimpson_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO thimpson_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO thimpson_app;
```

**Verificación de privilegios:** `rolsuper = f`, `SELECT = t`, `DELETE = t`, `has_database_privilege(CREATE) = f`.

**Nota:** los `ALTER DEFAULT PRIVILEGES` aplican a objetos futuros **creados por el mismo rol que los ejecuta**. Si en el futuro las tablas se crean con otro rol, hay que repetir los `GRANT` explícitos.

> **Si alguien altera esta parte:** el rol tiene DML pero no DDL. Cualquier migración debe correr con `thimpsonexpress_owner`, no con `thimpson_app`.

### Fase 5 — Cadena de conexión fuera del código

**Archivos y roles:**

| Archivo | Rol | Qué pasa si se altera |
|---|---|---|
| `Configuracion/entorno.php` | lector de `.env` sin Composer (`cargarEntorno()`, línea 34) | si no carga, `conexion.php` cae al respaldo local y el panel ignora Neon |
| `Configuracion/conexion.php` | `obtenerConexion(): PDO` (línea 36), lee `DATABASE_URL` (línea 46) | cambiar el DSN o el parseo deja el panel sin datos |
| `.env` | cadena real, **no versionado** | si se borra, el panel usa `127.0.0.1` |
| `.env.example` | plantilla con marcadores | es la única guía para un clon nuevo |
| `scripts/probar-conexion.php` | prueba que imprime host, base, usuario y versión, **nunca la clave** | deja de servir como verificación previa |
| `scripts/preparar-entorno.ps1` | crea `.env` desde el ejemplo y prueba la conexión | un clon nuevo queda sin arranque guiado |

**Cómo lee la URL `conexion.php`:** `parse_url()` separa host, puerto, base, usuario, clave y query; `sslmode` se extrae de la query (por defecto `require`); el DSN se arma con `sprintf` y `connect_timeout=10`.

**Decisión clave:** se descartó `channel_binding=require` en la URL de la app. Neon lo incluye en la cadena del owner, pero algunas compilaciones de libpq en PHP no lo soportan; con `sslmode=require` el tráfico ya va cifrado.

**Higiene de secretos:**

```
.gitignore:48  .env
.gitignore:49  .env.*
.gitignore:50  !.env.example
```

- `git check-ignore -v .env` → `.gitignore:48:.env`.
- `.htaccess` de la raíz ya bloqueaba dotfiles (línea 38) y la extensión `.env` (línea 44): aunque el archivo esté en el docroot, no se sirve por HTTP.
- La contraseña del rol **no** está en ningún archivo versionado ni en el chat.

> **Si alguien altera esta parte:** tocar `parse_url`/`sslmode` rompe la conexión en silencio (cae al fallback local). Verificar siempre con `php scripts/probar-conexion.php`, que muestra el host realmente usado.

### Fase 6 — Verificación de que Apache usa Neon

**Qué se hizo:** no alcanza con que la CLI conecte; había que probar el camino real del panel.

```powershell
# 1. Leer el CSRF del formulario y postear un usuario inexistente
$html  = curl.exe -s -c $jar "http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/login"
$token = [regex]::Match($html, 'name="csrf_token" value="([^"]+)"').Groups[1].Value
curl.exe -s -b $jar -c $jar -X POST `
  --data-urlencode "csrf_token=$token" `
  --data-urlencode "usuario=zzz_inexistente" `
  --data-urlencode "password=zzz" `
  "http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/login/authenticate"

# 2. Comparar contadores en ambas bases
SELECT seq_scan FROM pg_stat_user_tables WHERE relname='usuarios';
```

**Resultado:** POST → HTTP 302 a `/login`; `seq_scan` en **Neon 9 → 10**; contadores locales **sin cambios**.

**Trampa encontrada:** `pg_stat_user_indexes.idx_scan` no se movió (0→0) porque con una sola fila el planner prefiere seq scan. El contador correcto para esta verificación es `pg_stat_user_tables.seq_scan`.

> **Si alguien altera esta parte:** no usar `idx_scan` para probar que una consulta llegó a la base mientras la tabla tenga pocas filas.

---

## Contratos y configuración

| Elemento | Valor |
|---|---|
| Variable | `DATABASE_URL` |
| Formato | `postgresql://USUARIO:CLAVE@HOST/BASE?sslmode=require` |
| Fallback sin `.env` | `postgresql://postgres@127.0.0.1:5432/thimpsonexpress?sslmode=disable` |
| `sslmode` por defecto | `require` si la URL no lo trae |
| Timeout de conexión | 10 s |
| Errores | `PDO::ERRMODE_EXCEPTION` |
| Fetch | `PDO::FETCH_ASSOC` |
| Prepared statements | nativos (`ATTR_EMULATE_PREPARES => false`) |

## Reglas de negocio y seguridad aplicadas

- **Nunca credenciales en el repositorio:** `.env` ignorado + `.htaccess` bloqueando dotfiles.
- **Nunca credenciales en la app móvil:** las apps móviles no hablan con la base; hablarán con la API.
- **Mínimo privilegio:** `thimpson_app` no es superusuario, no crea bases y no tiene DDL.
- **SQL parametrizado:** los 4 modelos siguen usando `obtenerConexion()` con placeholders; la firma no cambió.
- **Sin cambios de schema:** la migración fue copia, no DDL nueva.

## Pruebas realizadas (evidencia)

| Comando | Resultado |
|---|---|
| `pg_restore --list` del dump | 13 entradas TOC, formato CUSTOM, PG 16.15 |
| `psql -d <neon> -c "SELECT version()"` | `PostgreSQL 16.15 … aarch64-unknown-linux-gnu` |
| Comparación de columnas / índices / datos / secuencia | idénticos en local y Neon |
| `php -l` sobre 23 archivos | 0 errores |
| `php scripts/probar-conexion.php` | `CONECTADO`, `PostgreSQL 16.15`, 1 fila |
| `scripts/higiene.ps1` | 5/5 secciones, sin problemas |
| `validar-estilo.ps1 -SoloNuevos` | 0 errores, 0 avisos, 0 notas |
| `git check-ignore -v .env` | `.gitignore:48:.env` |
| POST real al login + `seq_scan` | Neon 9→10, local sin cambios |
| `curl /login` | HTTP 200 |

## Decisiones y alternativas descartadas

| Decisión | Alternativa descartada | Motivo |
|---|---|---|
| Neon | Supabase free | se pausa por inactividad y la app quedaría caída |
| Neon | Túnel a la PC | depende de que la PC esté encendida |
| PG 16 | Dejar el proyecto en PG 18 | los dumps no vuelven a PG 16 |
| Endpoint directo | Pooler | rompe prepared statements nativos |
| Rol `thimpson_app` | Usar el owner | la app tendría DDL y privilegios de más |
| `.env` + parser propio | Composer + `vlucas/phpdotenv` | el proyecto no usa Composer |
| `sslmode=require` | `channel_binding=require` | incompatibilidades con libpq de PHP |

## Deuda y riesgos pendientes

1. **Rotación de la clave del `postgres` local y `pg_hba.conf` en `trust`** — fase 0 omitida por decisión del usuario. La base local sigue abierta a cualquier proceso de la máquina.
2. **`.env` no está en ningún gestor de secretos.** Se copia a mano entre máquinas; un clon nuevo necesita que alguien le pase la cadena.
3. **`AGENTS.md` es público en GitHub y documenta el login `admin` / `Admin12345`.** Hay que cambiar esa clave antes de publicar el panel.
4. **Sin migraciones versionadas.** La réplica se hizo con dump; el próximo cambio de schema no tiene historial.
5. **Solo el panel está conectado.** Los otros repos (`4-ThimpsonExpressAppWeb`, apps móviles) siguen sin base.
6. **Los 8 repos son públicos.** Cualquier secreto que se cuele en un commit queda expuesto aunque se borre después.

## Cómo reproducir

```powershell
# 1. Crear .env desde la plantilla (si no existe)
pwsh -File scripts/preparar-entorno.ps1

# 2. Pegar la cadena real en .env (DATABASE_URL="postgresql://…")

# 3. Verificar conexión (imprime host y versión, nunca la clave)
php scripts/probar-conexion.php

# 4. Compuertas del proyecto
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
.\scripts\higiene.ps1
```

**Herramientas de PostgreSQL:** `psql`, `pg_dump` y `pg_restore` **no** están en `PATH`; usar `C:\Program Files\PostgreSQL\16\bin\`.

## Archivos tocados en esta etapa

| Archivo | Estado |
|---|---|
| `Configuracion/entorno.php` | nuevo |
| `Configuracion/conexion.php` | reescrito |
| `.env.example` | nuevo |
| `.env` | nuevo, **no versionado** |
| `scripts/probar-conexion.php` | nuevo |
| `scripts/preparar-entorno.ps1` | nuevo |
| `.gitignore` | modificado (3 líneas de secretos) |
| `AGENTS.md` | modificado (sección Base de datos, comandos, higiene, deuda, referencias) |
| `docs/trazas/traza-neon-base-remota.md` | este documento |
