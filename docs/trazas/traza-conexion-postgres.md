# Traza: conexión PDO a PostgreSQL desde `Configuracion/conexion.php`

**Fecha:** 2026-10-02 · **Proyecto:** 3-ThimpsonExpressPanelAdministrativo · **Estado:** ✅ cumplido

## Resumen ejecutivo

1. Se implementó `obtenerConexion(): PDO`, una conexión única y reutilizable a la base `thimpsonexpress`, cumpliendo el pedido de "el mejor código PHP para crear la conexión".
2. El entorno se verificó contra la BD **viva**: PostgreSQL 16.15 en `127.0.0.1:5432`, extensión `pdo_pgsql` activa, PHP CLI == PHP de Apache (8.2.12, mismo `php.ini`).
3. **La verificación atrapó dos bugs reales antes de declarar nada**: un parámetro DSN inválido y un mal uso de placeholders — ambos corregidos con evidencia empírica.
4. Compuertas pasadas: `php -l` 91/91, guardián de estilo **0 errores**, batería de 14 pruebas sobre la BD y smoke HTTP **sin fuga de credenciales**.
5. Queda **deuda explícita**: las credenciales están en el archivo y el repo tiene remote en GitHub (decisión informada del usuario).

## Criterios de aceptación

| Criterio | Estado | Evidencia |
|---|---|---|
| Conexión real a `thimpsonexpress` | ✅ | prueba viva: 7 columnas y 1 fila leídas de `usuarios` |
| SQL parametrizado nativo | ✅ | `ATTR_EMULATE_PREPARES = false` verificado + query con parámetro devolvió `1` |
| Errores como excepción, no warning silenciosa | ✅ | `ERRMODE_EXCEPTION` + test negativo lanzó `PDOException` |
| Fetch asociativo por defecto | ✅ | `ATTR_DEFAULT_FETCH_MODE = FETCH_ASSOC` |
| No romper el proyecto existente | ✅ | lint 91/91, `/login` → 200 |
| Banner `estilo-codigo` | ✅ | guardián: 0 errores, 0 avisos, 0 notas |
| Sin fugas por HTTP | ✅ | `/Configuracion/conexion.php` → 404 |

---

## Fases

### Fase 1 — Diagnóstico y cuestionamiento del alcance

**Qué se hizo:** antes de escribir una línea, se midió el entorno y se buscó información que ya existiera.

- Confirmación del archivo activo en el editor: `Configuracion/conexion.php`.
- Extensiones PHP: `PDO`, `pdo_pgsql`, `pgsql` habilitadas; PHP **8.2.12** con `php.ini` idéntico para CLI y Apache.
- TCP: `127.0.0.1:5432` responde.
- Búsqueda de credenciales preexistentes en `3-ThimpsonExpressPanelAdministrativo`, `thimpsonexpress/` y `4-ThimpsonExpressAppWeb/` → **cero coincidencias**.

**Decisión clave:** el `AGENTS.md` del proyecto tiene el boundary *"No agregar PDO/mysqli ni migraciones… Si una tarea necesita persistencia, es decisión de arquitectura → **preguntar**"*. Como además no existían credenciales, **no se codificó** hasta tenerlas.

> **Si alguien altera esta parte:** el `AGENTS.md` sigue afirmando *"No existe BD"* — está **desactualizado** (ver Fase 2). Si se actúa sobre ese texto se bloquearían tareas de persistencia que ya son viables.

### Fase 2 — Verificación de credenciales contra la BD viva

**Qué se hizo:** un script efímero (borrado tras ejecutar) intentó conexión, listó bases de datos y tablas.

| Hallazgo | Valor |
|---|---|
| Conexión | ✅ OK |
| Servidor | PostgreSQL **16.15** |
| BD `thimpsonexpress` | ✅ **existe** |
| Tablas | `usuarios` (1 fila) |
| Columnas | `id_usuario:int`, `tipo_usuario:varchar`, `descripcion_usuario:varchar`, `nick_name:varchar`, `clave_usuario:text`, `foto_usuario:varchar`, `ultimo_login:timestamptz` |

**Decisión clave:** el `AGENTS.md` quedó demostrado como desactualizado. **No se editó** — corregir documentación del proyecto no estaba pedido y es cambio aparte.

> **Si alguien altera esta parte:** revalidar contra la BD real antes de asumir que el schema cambió; la traza refleja el estado del 2026-10-02.

### Fase 3 — Decisión de arquitectura sobre secretos

**Qué se hizo:** se ofrecieron 4 estrategias (archivo local fuera de git, `.env` + parser propio, hardcode, variables de entorno), advirtiendo que **el repo tiene remote en GitHub**.

**Decisión del usuario:** credenciales escritas en `Configuracion/conexion.php`.

**Decisión de diseño derivada:** se registró como campo `LÍMITES:` en el banner para que el riesgo quede visible en el propio código, no solo en la conversación.

> **Si alguien altera esta parte:** mover las credenciales a otra estrategia exige cambiar las 5 constantes (`conexion.php:20-24`) y nada más — el resto del archivo no las referencia por valor.

### Fase 4 — Escritura del código

**Archivo:** `Configuracion/conexion.php` (6 líneas → 69 líneas).

- Cabecera con las 4 secciones `ENCABEZADO`/`DETALLES`/`CUERPO`/`FIN` (delimitador de 50 caracteres).
- 5 constantes `SCREAMING_SNAKE` en español (`conexion.php:20-24`).
- `obtenerConexion(): PDO` con **singleton lazy por `static`** (`conexion.php:41-67`) y docblock de función (`conexion.php:26-40`).
- Opciones PDO: `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `ATTR_EMULATE_PREPARES => false`.

**Decisión clave — sin `declare(strict_types=1)`:** grep del proyecto → **cero archivos** lo usan. Aplica la cláusula de precedencia de la skill de estilo (convención existente del proyecto por encima de la guía). Además la firma no recibe parámetros escalares, por lo que no aporta protección.

> **Si alguien altera esta parte:**
> - Cambiar la firma de `obtenerConexion()` → todos los futuros consumidores deben adaptarse (banner `SI SE ALTERA` de la función, `conexion.php:35-36`).
> - Cambiar credenciales/DSN → el panel queda sin datos (banner `SI SE ALTERA` del archivo, `conexion.php:12`).
> - Poner `ATTR_EMULATE_PREPARES => true` → las consultas dejarían de ser nativas: **riesgo de seguridad**.

### Fase 5 — Verificación que atrapó 3 bugs

Esta es la fase que separa "código escrito" de "código que funciona".

**Bug 1 — DSN inválido (atrapado en la primera prueba):**

```
PDOException: SQLSTATE[08006] [7] invalid connection option "charset"
```

`pdo_pgsql` **no admite** `charset` en el DSN. Se quitó `;charset=UTF8`. Verificado después que no hacía falta: `SHOW client_encoding` → **UTF8** por defecto (BD `es_ES`, `server_encoding=UTF8`).

**Bug 2 — Parámetros que llegaban en `null` (el más serio):**

Síntoma: `SELECT count(*) FROM usuarios WHERE 1 = $1` devolvía `0` habiendo 1 fila; `SELECT $1::text` devolvía `null`.

Diagnóstico por variantes (script efímero, ya borrado):

| Placeholder | Resultado |
|---|---|
| `$1` (nativo PostgreSQL) + cualquier forma de bindeo | ❌ siempre `null` — PDO no lo reconoce como marcador |
| `?` (PDO portátil) + `execute([...])` 0-based | ✅ `{"valor":"Ñandú 42"}` |

**El archivo estaba bien; estaba mal mi propio script de pruebas.** PDO traduce internamente `?` → `$N` y ejecuta nativamente. Se corrigió el test, no el entregable.

**Bug 3 — Guardián de estilo:**

Reportó `Función 'obtenerConexion' sin banner ENCABEZADO`. Causa: el guardián sube desde el cierre de la última sección y **rompe en la apertura de `DETALLES`** antes de llegar a `ENCABEZADO` de la cabecera de archivo — un gap entre la skill y la herramienta. Se añadió el docblock de función con contenido **no duplicado** (contrato de la función, no configuración del archivo) para cumplir simultáneamente ambas reglas.

**Corrección menor del propio test:** `fetchColumn() === '1'` falló por comparación estricta int-vs-string → `(int)`.

> **Si alguien altera esta parte:** usar `$1`/`$2` en consultas desde este proyecto **silenciará los parámetros** (no lanzará error — devolverá `null`). Regla práctica: **siempre `?` + `execute([...])`**.

### Fase 6 — Compuertas finales

Ver sección "Pruebas". Todas corridas y en verde.

---

## Archivos afectados

| Archivo:linea | Rol | Qué se rompe si se modifica |
|---|---|---|
| `Configuracion/conexion.php:41` | `obtenerConexion()` — singleton PDO | Cualquier Modelo que lo incluya pierde la conexión; debe seguir devolviendo `PDO` |
| `Configuracion/conexion.php:52` | DSN `pgsql:host…` | Error `SQLSTATE[08006]` si se reintroduce `charset` o se pone un host inválido |
| `Configuracion/conexion.php:62` | `ATTR_EMULATE_PREPARES => false` | Pasarlo a `true` elimina la ejecución nativa de consultas parametrizadas |
| `Configuracion/conexion.php:20-24` | Constantes de credenciales | Panel sin datos; son el único punto de configuración |
| `AGENTS.md` (boundaries) | Prohíbe PDO / afirma "no existe BD" | **Ya desactualizado**: bloquea tareas de persistencia que sí son viables |

**No se modificó ningún otro archivo.** El diff es exactamente ` M Configuracion/conexion.php`.

## Contratos

```
obtenerConexion(): PDO          // sin parámetros, sin retorno nullable
  ├─ devuelve la MISMA instancia durante toda la petición PHP
  ├─ lanza PDOException si el servidor rechaza la conexión o falta la extensión
  └─ exige: require_once previo de Configuracion/conexion.php
```

Constantes exportadas: `SERVIDOR_BASE_DATOS`, `PUERTO_BASE_DATOS`, `NOMBRE_BASE_DATOS`, `USUARIO_BASE_DATOS`, `CLAVE_BASE_DATOS`.

**Sin endpoints HTTP nuevos:** el archivo no está en el array `$routes` y responde **404** si se le pide por web.

## Seguridad aplicada

- **SQL parametrizado nativo** (`ATTR_EMULATE_PREPARES = false` + `?`), verificado con query real.
- **Sin fuga por HTTP:** `/Configuracion/conexion.php` → `404`, sirve la página de error y jamás ejecuta el archivo.
- **Sin secretos en mensajes de error:** PHP 8.2 envuelve la contraseña en `SensitiveParameterValue` en los stack traces (observado en evidencia de la Fase 5).
- **Aviso registrado en banner:** `LÍMITES:` credenciales de desarrollo local escritas en el archivo.

## Pruebas (todas corridas)

```powershell
# 1. Lint del archivo
php -l C:\xampp\htdocs\3-ThimpsonExpressPanelAdministrativo\Configuracion\conexion.php
# => No syntax errors detected

# 2. Lint completo del proyecto
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
# => 91 archivos lintados | Con errores: 0

# 3. Guardián de estilo (SOLO sobre cambios)
pwsh -NoProfile -File ~\.config\opencode\scripts\validar-estilo.ps1 `
  -Ruta C:\xampp\htdocs\3-ThimpsonExpressPanelAdministrativo -SoloNuevos
# => 0 error(es), 0 aviso(s), 0 nota(s)

# 4. Smoke test HTTP
curl -s -o NUL -w '%{http_code}' http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/<ruta>
# => / 302 · /login 200 (9665 bytes) · /index.php 301 · /Configuracion/conexion.php 404
```

**Batería funcional sobre la BD viva — 14 casos:** 13 OK + 1 test negativo (era el resultado deseado).

| Prueba | Resultado |
|---|---|
| Construye y conecta | ✅ PDO |
| Singleton: 2ª llamada = misma instancia | ✅ misma |
| `EMULATE_PREPARES` | ✅ `false` |
| Modo de errores | ✅ excepción |
| Fetch por defecto | ✅ `ASSOC` |
| `client_encoding` | ✅ UTF8 |
| Suma parametrizada `40+2` | ✅ **42** |
| Versión servidor | ✅ PostgreSQL 16.15 |
| Existe tabla `usuarios` | ✅ SÍ |
| Columnas de `usuarios` | ✅ 7 columnas |
| Filas en `usuarios` | ✅ 1 |
| Query parametrizada sobre `usuarios` | ✅ **1** |
| SQL inválido lanza excepción | ✅ `PDOException` (test negativo) |
| Acentos/ñ por bound parameter | ✅ `Ñandú pértigo` |

> Los scripts efímeros de verificación fueron **borrados**; los comandos de arriba son los reproducibles.

## Decisiones y alternativas descartadas

| Alternativa | Por qué se descartó |
|---|---|
| `charset=UTF8` en el DSN | **Falló**: `pdo_pgsql` no lo admite. Verificado que ya viene UTF8 |
| Placeholders `$1`/`$2` nativos | **Falló**: PDO exige `?`; con `$1` los parámetros llegan en `null` sin error |
| `declare(strict_types=1)` | Cero archivos del proyecto lo usan (precedencia: convención existente) |
| `new PDO()` en cada llamada | Reconecta por cada query: lento y agota conexiones → `static` |
| `.env` + parser propio / fuera de git / vars de entorno | **Decisión explícita del usuario**: ir en `conexion.php` |
| Capa de manejo de errores/log personalizada | El proyecto no tiene sistema de logs; añadirlo sería código no pedido |

## Deuda y riesgos pendientes

1. **🔴 Credenciales en texto dentro de un repo con remote en GitHub.** Decisión informada del usuario, pero **no se ha hecho push de este cambio**. Riesgo alto si se sube a un repo público.
2. **🟡 `AGENTS.md` desactualizado** — afirma "No existe BD" y prohíbe PDO; ambas cosas ya no son ciertas.
3. **🟡 `conexion.php` no tiene consumidores** — nadie lo incluye aún; la conexión existe pero no alimenta ningún Modelo (los siguen usando arrays estáticos).
4. **🟡 Gap skill↔guardián** — el guardián no contempla la cabecera de archivo multisección; se resolvió añadiendo docblock de función, pero la incoherencia persiste.
5. **🟡 Contenido de `clave_usuario` no verificado** — se leyó su *tipo* (`text`), no su valor. **No se afirma** que guarde hashes ni texto plano.
6. **🟠 Pendiente heredado:** el header `Server:` no se puede ocultar desde `.htaccess`; requiere `ServerTokens Prod` en `C:\xampp\apache\conf\extra\httpd-default.conf` + reinicio de Apache (**cambio global, requiere aprobación**).

**Experto humano requerido:** decisión de política de secretos para producción (Punto 1) y aprobación para el cambio global de Apache (Punto 6).

## Cómo reproducir

```powershell
# Servicios requeridos: Apache (XAMPP :8080) y PostgreSQL (:5432)
# 1. Abrir el panel
start http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/login
# 2. Lint + guardián (comandos 1-3 de Pruebas)
# 3. Probar la conexión desde PHP CLI con un require_once temporal
php -r "require 'C:/xampp/htdocs/3-ThimpsonExpressPanelAdministrativo/Configuracion/conexion.php'; echo obtenerConexion()->query('SELECT 1')->fetchColumn();"
```
