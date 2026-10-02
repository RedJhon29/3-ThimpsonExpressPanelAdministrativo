# Traza — .htaccess con rutas amigables y ocultamiento de rutas reales

## Resumen ejecutivo

Se reescribió `C:\xampp\htdocs\3-ThimpsonExpressPanelAdministrativo\.htaccess` (8 → 61 líneas) pasando de un rewrite mínimo de 3 reglas a un front controller con bloqueo de rutas internas, apagado de listado de directorios y cabeceras de endurecimiento. La línea base confirmó 4 fugas activas, la más grave `/.git/config` sirviendo el repo completo. Se cerraron 13 vectores de acceso directo respondiendo 404 (no 403) para no confirmar existencia. Las 37 rutas de `$routes` y todos los assets siguen operativos: 25/25 verificaciones exactas, 0 regresiones. `php -l` en verde sobre 91 archivos. Quedó pendiente el header `Server:`, que Apache no permite ocultar desde `.htaccess`.

---

## 1. Objetivo y criterios de aceptación

**Objetivo:** que el `.htaccess` implemente rutas amigables y seguridad, de modo que la URL muestre `/login` y no se pueda deducir la ruta real del archivo que la atiende.

| # | Criterio | Estado | Evidencia |
|---|---|---|---|
| 1 | URL amigable sin `index.php` visible | ✅ | `/login` → `200`; `/index.php/login` → `301` a `/login` |
| 2 | Ruta real de carpetas interna no accesible en directo | ✅ | `Configuracion/`, `Modelos/`, `Vistas/`, `Controladores/` → `404` |
| 3 | Repo Git no expuesto | ✅ | `/.git/config`, `/.git/HEAD`, `/.git/index` → `404` |
| 4 | Sin listado de directorios | ✅ | `/Publico/`, `/Publico/Recursos/`, `/Vistas/` → `404` |
| 5 | Sin fingerprinting de versión en páginas de error | ✅ | cuerpo del 404 sin `<address>Apache/2.4.58...` |
| 6 | Sin `X-Powered-By` en la respuesta | ✅ | ausente en 200, 301, 302 y 404 |
| 7 | Las 37 rutas del router siguen funcionando | ✅ | 0 rotas (37/37 → `200`/`302`) |
| 8 | Assets estáticos siguen sirviendo | ✅ | `admin.css` → `200 text/css 18239 bytes` |
| 9 | Cabeceras de endurecimiento en todos los estados | ✅ | presentes en 200, 301, 302 y 404 |
| 10 | Ocultar el header `Server:` | ❌ | no posible desde `.htaccess` (ver §8) |

---

## 2. Fases en orden cronológico real

### Fase 1 — Diagnóstico y línea base

**Archivo:** ninguno modificado. Se leyó `index.php`, `.htaccess`, `Configuracion/app.php` y `C:\xampp\apache\conf\httpd.conf`.

Comando de línea base:

```powershell
$base='http://localhost:8080/3-ThimpsonExpressPanelAdministrativo'
$rutas = @('/login','/Configuracion/app.php','/.git/config','/Publico/','/AGENTS.md',
           '/Modelos/Pedido.php','/Vistas/Pedidos/detail.php','/.htaccess','/index.php/login')
foreach ($r in $rutas) {
  $code = & curl.exe -s -o NUL -w '%{http_code}' "$base$r"
  Write-Output ("{0,-42} -> {1}" -f $r, $code)
}
```

Resultado ANTES del cambio:

| Ruta | Código | Hallazgo |
|---|---|---|
| `/.git/config` | `200` | repo Git completo expuesto |
| `/Publico/` | `200` | `Index of /3-ThimpsonExpressPanelAdministrativo/Publico` |
| `/Vistas/Pedidos/detail.php?id=1` | `200` (313 bytes) | vista renderizándose **sin controller y sin autenticación** |
| `/Configuracion/app.php`, `/Modelos/Pedido.php` | `200` | PHP ejecutable en directo |
| `/AGENTS.md` | `200` | documentación interna descargable |
| Pie de página Apache | — | `Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12` |

**Decisión clave:** el gate de autenticación de `index.php:118-125` vive *dentro* del router, así que cualquier archivo PHP servido directo lo elude. El bloqueo tiene que ocurrir en Apache, antes del rewrite.

### Fase 2 — Delimitación del terreno

**Archivos leídos, ninguno modificado.**

Verificaciones que condicionaron el diseño:

- `httpd.conf:273` → `AllowOverride All` (habilita `Options`, `Header`, `ServerSignature` en `.htaccess`).
- `httpd.conf:120,163` → `mod_headers` y `mod_rewrite` cargados.
- `httpd.conf:266` → `Options Indexes` a nivel servidor: **el listado estaba encendido por defecto**.
- Inventario de extensiones: 91 `.php`, 1 `.css`, 1 `.md`, 1 `.gitignore`. **Cero `.json`, `.sql`, `.js`.**

**Decisión clave:** el inventario real demostró que bloquear `.md`/`.sql`/`.env`/`.bak` globalmente no rompe ningún asset. Si se hubiera encontrado un `.js` o `.json` fuera de `Publico/`, la lista de bloqueos tendría que haberse restringido a las carpetas internas.

### Fase 3 — Especificación de alcance con el usuario

Dos preguntas antes de escribir una sola línea, porque ambas decisiones cambian comportamiento de forma irreversible y no eran deducibles de la petición:

1. **Alcance del bloqueo** → eligió *Completo* (carpetas MVC + `.git` + docs + extensiones sensibles + listado, todo en `404`).
2. **Cabeceras HTTP** → eligió *Seguras, sin riesgo* (descartó CSP estricta por riesgo de romper JS/CSS inline).

### Fase 4 — Implementación

**Archivo:** `.htaccess` (reescritura completa, 8 → 61 líneas).

Bloques y la razón de cada uno:

| Líneas | Bloque | Por qué existe |
|---|---|---|
| 18-19 | `Options -Indexes` | `httpd.conf` trae `Indexes` encendido; declarativo y barato |
| 22 | `ServerSignature Off` | quita el `<address>` con versión del cuerpo de los errores |
| 25-27 | 3 cabeceras `always set` | `always` para que apliquen también en 301/302/404 |
| 30-32 | `X-Powered-By` en 3 variantes | la primera (`always unset`) sola **no** funcionó; ver §5 |
| 35 | carpetas MVC → `404` | el bloqueo debe ir ANTES del rewrite, si no se sirve el archivo |
| 38 | `^\.` → `404` | un solo patrón cubre `.git`, `.env`, `.gitignore`, `.htaccess` |
| 41 | docs del repo → `404` | `AGENTS.md` entrega el mapa de la arquitectura |
| 44-45 | extensiones sensibles + `~` | respaldos y volcados en cualquier nivel |
| 48-50 | directorio sin `index.php` → `404` | `Options -Indexes` solo da `403`; este bloque lo convierte en `404` |
| 53-54 | canonical de `index.php` → `301` | evita que el nombre real del front controller aparezca en la URL |
| 57-59 | rewrite al router | **sin cambios**: idéntico al original |

**Si alguien altera esta parte →** quitar la línea 55-57 (el bloque de carpetas MVC) reabre la fuga de `Vistas/` que renderiza sin sesión; mover el rewrite del router (57-59) *encima* de los bloqueos los inutiliza, porque `RewriteCond !-f` los haría pasar; borrar las líneas 53-54 devuelve `index.php` visible en la URL.

### Fase 5 — Corrección de cabeceras por evidencia

Iteración empírica sobre las respuestas HTTP reales:

- `Header always unset X-Powered-By` **solo** → el header **seguía apareciendo**. Se añadieron `unset` (sin `always`) y `edit`. Resultado: **ausente** ✅
- `Header unset Server` → no funcionó.
- `Header always unset Server early` → no funcionó.
- `Header edit Server ^.*$ ""` → no funcionó.
- `Header always edit Server ^.*$ ""` → no funcionó.

**Decisión clave:** se eliminaron las 4 líneas de `Server` en lugar de dejarlas. Código que no hace nada es peor que no tenerlo: da falsa seguridad a quien lea el archivo en el futuro.

### Fase 6 — Verificación

Ver §7.

---

## 3. Archivos afectados

| Archivo | Rol | Línea central | Qué se rompe si se modifica |
|---|---|---|---|
| `.htaccess` | front controller + bloqueo de rutas | `:59` (rewrite final) | romper cualquier `RewriteCond` deja el sitio en `500` o elude los bloqueos |
| `index.php` | **no modificado** | `:17` (`$projectPrefix`) | si cambia el prefijo, hay que rehacer la regex de `THE_REQUEST` en `.htaccess:53` |
| `Configuracion/app.php` | **no modificado** | `:13` (`BASE_URL`) | ídem: la tercera copia del prefijo duro |
| `Vistas/Auth/login.php` | **no modificado** | `:191` (`action`) | si el `action` pasara a contener `index.php`, el `301` de `.htaccess:53` convertiría el `POST` en `GET` y se perdería el body |

**Solo 1 archivo del repo fue modificado** (`git status --short` → ` M .htaccess`).

---

## 4. Contratos

**Rutas que ahora responden `404` (antes `200`/`403`):**

```
/Configuracion/*          /Modelos/*          /Vistas/*          /Controladores/*
/.git/*                   /.gitignore         /.env              /*.bak  /*~
/AGENTS.md                /README*            /CHANGELOG*        /LICENSE*
/ cualquier directorio sin index.php
```

**Redirección nueva:**

```
GET /3-ThimpsonExpressPanelAdministrativo/index.php/login  → 301 → .../login
GET /3-ThimpsonExpressPanelAdministrativo/index.php        → 301 → .../
Guardada en .htaccess:53 con RewriteCond %{THE_REQUEST}
```

**Cabeceras presentes en 200, 301, 302 y 404:**

```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
(X-Powered-By: ausente)
```

**`403` inocuo en `.ht*`:** Apache responde `403` a `/.htaccess` **y también a `/.htfoo` (inexistente)**, verificado. Ese `403` no confirma la existencia de nada.

---

## 5. Reglas de seguridad aplicadas

- **Bloqueo en Apache, antes del router** — única forma de cerrar el hueco: el gate de sesión de `index.php:118` nunca se ejecuta si el archivo se sirve directo.
- **`404` en lugar de `403`** para las rutas internas: `403` confirma que el recurso existe; `404` no.
- **`RewriteCond %{THE_REQUEST}`** en la canonical de `index.php`: sin ella, el rewrite interno del router produciría `index.php` en la segunda ronda y entraría en **bucle de redirección infinito**. Es la trampa más peligrosa del archivo.
- **Guardia de directorios** con `%{REQUEST_FILENAME}/index.php !-f` para distinguir la raíz (tiene `index.php`, se sirve) de `Publico/` (no tiene, da `404`).
- **Sin CSP ni HSTS**: descartadas por el usuario. CSP estricta rompe el JS inline que usan las vistas (`onclick`, `window.location.href`); HSTS no aporta en `localhost`.

---

## 6. Pruebas realizadas

**Todas corridas, ninguna supuesta.**

### 6.1 Las 37 rutas del `$routes`

```powershell
# Esperado: 200 ó 302 en todas; NUNCA 500 (error de sintaxis) ni 404 (rewrite roto)
$rutas = @('/','/login','/login/authenticate','/logout','/dashboard','/orders','/orders/detail',
  '/riders','/riders/detail','/riders/tracking','/services','/marketplace','/cms','/landing-editor',
  '/chatbot','/clients','/finance','/conversations','/subscribers','/reviews','/ratings',
  '/promotions','/zones','/notifications','/reports','/audit','/settings','/admin-users',
  '/devices','/support','/subscriptions','/pricing','/whatsapp','/openwa',
  '/orders/detail?id=1','/riders/detail?id=1','/no-existe-xyz')
foreach ($r in $rutas) {
  $code = & curl.exe -s -o NUL -w '%{http_code}' "$base$r"
  if ($code -notin @('200','302')) { Write-Output "FALLO $r -> $code" }
}
```

**Estado: PASA.** `rutas rotas: 0`.

### 6.2 Batería de fugas + activos + canónicas (25 verificaciones)

**Estado: PASA.** `Fallos: 0` — 13 fugas → `404`, 2 canónicas → `301`, rutas → `302`/`200`, `admin.css` → `200`.

### 6.3 Lint PHP

```powershell
Get-ChildItem -LiteralPath $root -Filter *.php -Recurse | ForEach-Object { php -l $_.FullName }
```

**Estado: PASA.** `Archivos PHP lintados: 91` / `Archivos con error: 0`.

### 6.4 Guardián de estilo

```powershell
& "$env:USERPROFILE\.config\opencode\scripts\validar-estilo.ps1" -Ruta <proyecto> -SoloNuevos
```

**Estado: CORRIÓ — exit 0, `0 error(es), 0 aviso(s)`**, pero reporta `ARCHIVOS MODIFICADOS: 0` aunque `git status` muestra ` M .htaccess`.

**Motivo:** `validar-estilo.ps1:36` → `$EXTENSIONES = @('.php','.html','.css','.js','.ts','.sql','.py')`. **`.htaccess` no está en la lista**, así que queda fuera de su alcance por diseño.

**Advertencia de invocación:** debe correrse con **`pwsh`** (PowerShell 7). Invocarlo como `powershell -NoProfile -File ...` (Windows PowerShell 5.1) rompe el parseo con `ParserError` porque 5.1 lee los UTF-8 sin BOM como ANSI. Fue un error de invocación, **no** un bug del guardián.

Como el guardián no cubre `.htaccess`, el banner se validó a mano contra la skill: 4 secciones, delimitadores de 50 caracteres, DETALLES con 6 líneas de contenido, fechas y autor completos.

### 6.5 `graphify update .`

**No ejecutado:** `Test-Path graphify-out` → `False`. No aplica.

---

## 7. Cómo reproducir

```powershell
# Servidor (XAMPP, Apache debe estar corriendo; NO usar php -S, rompe mod_rewrite)
http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/

# Verificación rápida de las 3 fases de la prueba
$base='http://localhost:8080/3-ThimpsonExpressPanelAdministrativo'
& curl.exe -s -o NUL -w '%{http_code}' "$base/login"                  # 200
& curl.exe -s -o NUL -w '%{http_code}' "$base/.git/config"           # 404
& curl.exe -s -o NUL -w '%{http_code}' "$base/Publico/Recursos/css/admin.css"  # 200

# Reversión completa
git checkout -- .htaccess
```

---

## 8. Deuda, riesgos y experto humano requerido

| # | Riesgo / deuda | Impacto | Acción necesaria |
|---|---|---|---|
| 1 | **Header `Server: Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12` sigue filtrándose** en todas las respuestas | fingerprinting del stack | Requiere `ServerTokens Prod` en `C:\xampp\apache\conf\extra\httpd-default.conf` + **reinicio de Apache**. **Es un cambio GLOBAL**: afecta a todos los sitios de XAMPP, no solo a este. No se hizo por no estar pedido explícitamente. **Requiere aprobación.** |
| 2 | **Ocultar rutas ≠ seguridad** | el `301`/`404` no protege contra nada real | Si esto pasa a producción, la seguridad auténtica es: auth en servidor, validación de entrada, y **no publicar `.git`**. |
| 3 | **`.git/` sigue dentro de `htdocs/`** | alto, si se despliega así | Excluirlo del despliegue. El bloqueo del `.htaccess` protege solo mientras Apache sirva la carpeta. |
| 4 | **`301` cacheados agresivamente** | durante desarrollo puede molestar si se revierte | Limpiar caché del navegador o usar `302`/`308` en dev. |
| 5 | **Tercera copia del prefijo** `/3-ThimpsonExpressPanelAdministrativo` | ya existía (`index.php:17`, `app.php:13`); se suma el peso de la regex `.htaccess:53` | Renombrar el proyecto exige cambiar 3 lugares. Deuda preexistente, **no introducida aquí**. |
| 6 | **El guardián de estilo no cubre `.htaccess`** (`validar-estilo.ps1:36`, `$EXTENSIONES`) | los cambios en archivos de Apache no se auditán automáticamente | Si se quiere cobertura, agregar `''.htaccess''` a `$EXTENSIONES` y extender el patrón de delimitadores para el prefijo `#` (hoy solo acepta `/*` y `<!--`). Toca la skill `estilo-codigo` → seguir su cadena de mantenimiento (SKILL → generar-snippets → generar-emmet → instalar). |
| 7 | **~49 links `?page=...` muertos en las vistas** | preexistente, documentado en `AGENTS.md` | **No relacionado con este cambio**: el router ignora query string desde antes. Verificado que empeora 0 de ellos. |
| 8 | `DirectoryIndex` asume que `index.php` es el único índice válido | si algún día se agrega `Publico/index.php`, se serviría directo sin pasar por el router | Mantener `index.php` solo en la raíz. |

**Experto humano requerido:** decisión sobre `ServerTokens Prod` (punto 1) — es el único pendiente que no se puede resolver tocando solo este proyecto.
