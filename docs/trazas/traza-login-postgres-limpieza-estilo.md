# Traza: Login funcional contra PostgreSQL + limpieza de la deuda de estilo

**Proyecto:** Thimpson Express Panel Administrativo
**Fecha:** 2026-10-02 (ampliada el 2026-10-04/05 con las fases 8 a 14)
**Estado del objetivo:** cumplido (0 errores en ambos modos del guardián; 10/10 pruebas HTTP;
login con overlay de 2 s y doble arco, **panel 100 % offline con fallback automático
CDN → local → CDN y alertas de Alertify**, botón de salida con confirmación + spinner de
1200 ms y redirección con sesión destruida, verificados en navegador real)

## Resumen ejecutivo

Se habilitó el login real contra PostgreSQL (antes roto por `obtenerConexion()` inexistente),
se purgó el código muerto (71 archivos) y se eliminaron los **207 errores** del guardián de
estilo → **0 errores, 0 avisos** en los modos `-SoloNuevos` y total. Se corrigieron **2 bugs
del propio guardián** (Fase 7), **el bloqueo de la salida con `Enter`** que impedía llegar al
panel (Fase 8), se añadió el **botón de cierre de sesión con overlay** en la esquina superior
derecha (Fase 9) y se reemplazaron los **3 spinners** por rueditas circulares amarillo/blanco,
aprovechando el hallazgo del `border-radius: 0px !important` global para ganar el empate
(Fase 10). Las fases 11-14 completaron la **experiencia de carga, salida y resiliencia**:
overlay de login con doble arco y fin del splash (Fase 11), **SweetAlert/Alertify locales**
(Fase 12), **DataTables local en español + alerta de confirmación al cerrar sesión** con
spinner de 1200 ms que destruye la sesión (Fase 13) y **migración offline total de los 9
CDN + Google Fonts con fallback en cadena y alertas de recursos fallidos** (Fase 14). Todo
quedó verificado con `php -l` (lint global 0 errores), 10 pruebas HTTP y navegador real
(Chrome for Testing). **Ningún cambio alteró la UI visible más allá de lo pedido.**

---

## 1. Objetivo y criterios de aceptación

| Criterio | Estado |
|---|---|
| Login funcional contra BD real (usuario `admin`) | ✅ |
| `obtenerConexion()` disponible en la web | ✅ |
| Código muerto fuera del árbol de trabajo | ✅ (71 archivos) |
| Guardián `-SoloNuevos` → 0 errores / 0 avisos | ✅ |
| Guardián modo total → 0 errores | ✅ (207 → 0) |
| `php -l` sin errores | ✅ (lint global: 0 errores) |
| UI del panel intacta | ✅ (dashboard 200, CSS 200, sin cambios de HTML visible) |
| DataTables operativo en español **sin CDN** | ✅ (4 archivos locales, 0 peticiones a `cdn.datatables.net`) |
| Cerrar sesión pide confirmación (Sí/Cancelar) en **los 2 botones** | ✅ |
| Al confirmar: spinner idéntico al login, duración corta, sesión destruida | ✅ (1200 ms → `/login` medido en 1656 ms; sesión invalidada) |
| Con internet → CDN; sin internet → local; sin local → reintento CDN | ✅ (3 escenarios E2E: online / offline / sin locales) |
| Fallo total → alerta de Alertify que especifica el recurso | ✅ (alerta agrupada con nombre, secuencia y ruta) |
| Panel usable sin conexión (0 CDN obligatorios) | ✅ (los 9 plugins + fuentes locales; tiles OSM pendientes de red por naturaleza) |

---

## 2. Fases (cronológico real)

### Fase 1 — Habilitar la conexión (raíz del fallo)

**Problema:** `obtenerConexion()` devolvía *"undefined function"* en la web porque
`Configuracion/conexion.php` nunca se cargaba.

- **`Configuracion/app.php:1`** → añadido `require_once __DIR__ . '/conexion.php';` antes del
  autoload. Es **perezoso**: no abre conexión hasta que se invoca.
- **Si alguien altera esta parte →** desaparecen `obtenerConexion()`, `PDO` y **todo el login**
  (el panel vuelve a caer en "undefined function" en cualquier consulta).

### Fase 2 — Esquema y credenciales

- **Migración aplicada** (una sola vez, ver §9): se añade `estado_usuario` a `usuarios`
  (`varchar(20) NOT NULL DEFAULT 'activo'`), que es lo que permite bloquear cuentas.
- **Mapa de columnas → sesión** (decisión, ver §7):

| Columna BD | Clave de sesión |
|---|---|
| `id_usuario` | `user_id` |
| `descripcion_usuario` | `user_name` |
| `nick_name` | `user_nick` |
| `tipo_usuario` | `user_role` |

- **Si alguien altera este mapa →** rompe `loginController::authenticate()` y cualquier vista
  que lea `$_SESSION` (hoy solo `index.php`, que usa `user_id`).

### Fase 3 — Modelo de autenticación

- **`Modelos/loginModel.php`** — reescrito con PDO:
  - `buscarPorNickName(string): ?array`
  - `verificarClave(string,string): bool` → `password_verify()` contra el hash Argon2id de la BD
  - `registrarUltimoLogin(int): void` → usa `now()` de PostgreSQL
- **Contrato:** todos los placeholders son `?` + `execute([...])` (nunca `$1`, y el DSN va sin
  `charset=UTF8` para no chocar con la codificación del servidor PG).
- **Si alguien altera esta parte →** se rompe el login completo; el cambio de placeholders a
  sintaxis `$n` produciría un error silencioso de PDO.

### Fase 4 — Controlador de sesión

- **`Controladores/loginController.php`** — `index()`, `authenticate()`, `logout()`:
  - Los mensajes "Credenciales inválidas" / "Cuenta desactivada" **solo** se revelan **después**
    de validar la clave (evita enumerar usuarios).
  - `session_regenerate_id(true)` en el login (fijación de sesión).
  - `TODO(2026-Q4)` documentado para límite de intentos (ver §8).
- **Si alguien altera esta parte →** cambia toda la entrada al panel; revisar el guard de sesión
  de `index.php` y la lista de rutas públicas.

### Fase 5 — Limpieza (71 archivos fuera)

- **`index.php`** → `$routes` recortado a rutas de autenticación + `/dashboard`.
- **26 controladores** borrados (todo salvo `loginController`, `panelController`).
- **21 modelos** borrados (todo salvo `loginModel`, `Panel`, `Pedido`, `Motorizado`).
- **24 carpetas de `Vistas/`** borradas (cada una con exactamente 1 archivo, los reportados).
- **Verificación previa al borrado:** grep en los 20 archivos vivos → **0 referencias** a las
  carpetas borradas; los archivos borrados estaban trackeados en git (recuperables).
- **Si alguien altera esta parte →** las vistas restantes siguen incluyéndose por
  `VIEW_PATH`; ninguna referencia rota. Los 404 de rutas borradas son el comportamiento esperado.

### Fase 6 — Banners de estilo (207 → 0)

- **Cabeceras y funciones** en: `index.php`, `Configuracion/app.php`, `Modelos/{Pedido,
  Motorizado,Panel}.php`, `Controladores/panelController.php`, las 3 plantillas, `Errores/404`,
  `Panel/index`, `Auth/login` (+ `togglePassword`), `Pedidos/{index,detail}` (+ 4 funciones JS),
  `Riders/{index,detail,tracking}` (+ 4 funciones JS) y `Publico/Recursos/css/admin.css`.
- **Cierres corregidos:** los cierres de DETALLES medían 49 `=` (la referencia `conexion.php`
  tiene 50) → corregidos con script.
- **Si alguien altera esta parte →** solo comentarios: **cero impacto funcional**. El guardián
  marcará el archivo como "nuevo sin cabecera" si se borra el banner.

### Fase 7 — Corrección de 2 bugs del guardián

Ambos se descubrieron **verificando**, no especulando:

| Bug | Evidencia | Fix |
|---|---|---|
| El cierre de sección HTML `====...====-->` no se reconocía → contaba hasta EOF (19/40/59/125 líneas falsas) | La skill §1 manda ese cierre | Añadido `^\s*=+-->\s*$` como cierre |
| El `break` al ver `/*` ocurría **antes** de comprobar si la línea era `ENCABEZADO` → banners con forma `/*====ENCABEZADO====` no validaban (falso negativo por indentación 8) | Fixture indent4 pasa / indent8 falla | `EsLineaDeSeccion 'ENCABEZADO'` evaluado **antes** del `break` |

- **Si alguien altera esta parte →** el guardián puede volver a marcar falsos positivos/negativos
  en **todos** los proyectos; verificar siempre con los fixtures de §9.

### Fase 8 — Fix: login no avanzaba al panel con `Enter`

**Síntoma reportado:** "intento iniciar sesión y solo se queda cargando y no pasa al panel".

- **Causa raíz** (`Vistas/Auth/login.php`, bloque `keydown`): presionar Enter en la contraseña
  disparaba un `submit` **sintético** (`new Event('submit')`, no trusted) → corría el listener
  del form (deshabilita botón + activa spinner) pero el navegador **nunca enviaba** el POST.
  Comparaba además contra `id === 'email'`, cuando el campo real es `usuario` (código muerto).
- **Fix:** se **eliminó ese bloque completo** (el navegador ya envía con Enter nativamente).
- **Si alguien altera esta parte →** reintroducir un `dispatchEvent('submit')` manual vuelve a
  bloquear el envío. El listener de `submit` (spinner) **sí** debe conservarse.
- **Verificación:** los 3 caminos en navegador real (Enter en contraseña / Enter en usuario /
  clic) → todos a `/dashboard`; clave mala → 302 + mensaje.

### Fase 9 — Botón de cierre de sesión (esquina superior derecha)

- **`barraLateralAdmin.php`**: `<a id="logoutBtn">` con `bi-power` al final del bloque derecho
  del topbar (junto al avatar) + `<div id="logoutOverlay">` con spinner y "Saliendo del sistema".
- **`admin.css`**: `.topbar-logout` (círculo 36px, `--destructive`, hover con tinte rojo),
  `.logout-overlay` (`position:fixed; inset:0; z-index:2000`, `display:none` hasta `.show`).
  En `@media (max-width: 767.98px)`: **solo** `.topbar-logout` pasa a `position:fixed; top:10px;
  right:12px` para que no quede fuera del viewport (ver §8).
- **`pieAdmin.php`**: listener → `preventDefault()` → `.show` → `window.location.href` a los
  **700 ms**. Al ser un `<a href>` real, **si el JS falla la salida sigue funcionando**.
- **Si alguien altera esta parte →** quitar el `<a href>` deja la salida dependiente 100% del JS;
  subir el `z-index` por debajo de 2000 tapa el overlay; quitar el `?` del `getElementById`
  rompe páginas sin topbar (el login no incluye estas plantillas).

---

### Fase 10 — Spinners tipo rueda (amarillo y blanco)

**Pedido (2026-10-05):** "en lugar de un cuadro que gire quiero que sean circulares, rueditas
de color amarillo y blanco". El proyecto tiene exactamente **3 spinners**.

- **Causa del "cuadro":** `admin.css:46-49` declara
  `*, *::before, *::after { border-radius: var(--radius) !important }` con `--radius: 0px` →
  **anula el `border-radius: 50%` de `.spinner-border` (Bootstrap)** en todo el panel.
  Verificado midiendo la OM del browser: ninguna declaración de radio sobrevivía al `*`.
- **Componente nuevo** (al final de `admin.css`): `.spinner-rueda` = anillo de 8 segmentos de
  45° (`conic-gradient` con `var(--primary)`/`#FFFFFF`) recortado con
  `mask: radial-gradient(farthest-side, ...)` + `@keyframes giroRueda` (360°, 1 s lineal
  infinito) + `border-radius: 50% !important` (gana el empate por especificidad vs. el `*`).
- **Los 3 usos** (tamaño por `--rueda-size`/`--rueda-grosor` inline): `encabezadoAdmin.php`
  (splash, 2rem), `barraLateralAdmin.php` (overlay de salida, 2.6rem), `login.php`
  (botón "Ingresando...", 1.1rem). **No queda ningún `spinner-border`** en el proyecto.
- **Evidencia:** los 3 miden `border-radius: 50%` en la OM (32px / 17.6px / 41.6px) y las 3
  capturas muestran anillos circulares; el `prefers-reduced-motion` global (`admin.css:769`)
  ya detiene `giroRueda`.
- **Si alguien altera este componente →** los 3 estados de carga vuelven al cuadro:
  re-medicar `getComputedStyle` y volver a capturar splash + botón + overlay.

---

### Fase 11 — Overlay de login + doble arco concéntrico (y fin del splash)

**Pedido (2026-10-05):** (a) pantalla de carga de 3 s sobre el login (translúcida, spinner,
"Ingresando al sistema"); (b) tras medir en navegador real **dos cargas seguidas** (overlay 3 s
+ splash ≈ 1,2 s) el usuario eligió **quitar el splash del panel** ("solo uno"); (c) el spinner
del overlay debía ser **dos semicírculos concéntricos girando en sentidos opuestos** (amarillo
afuera →, blanco adentro ←); (d) del mismo día, **bajar el efecto a 2 s**.

- **Overlay** (`admin.css:848-868`, `login.php`): `.login-overlay` = `fixed inset:0`,
  `z-index:2500`, `--background` al 78 % + `backdrop-filter: blur(4px)`; `.show` → `flex`.
  `enviarLogin()` garantiza **≥ 2000 ms reales** (`Math.max(0, 2000 - transcurrido)`; era
  3000 y se bajó a 2000 el mismo día); éxito →
  `location.href` a la URL que devuelve el servidor; **credenciales inválidas →
  `document.write(await r.text())`** porque `loginController::index()` hace `unset()` del flash
  `login_error` (un GET extra lo borraría); red/5xx → cierra overlay y reactiva el botón
  (arregla de paso el bug del botón disabled eterno); sin JS → form nativo sin overlay.
- **Doble arco** (final de `admin.css`): `.spinner-doble` con dos `.doble-arco` absolutos;
  semicírculos de 180° exactos vía `border-color: X X transparent transparent` (top+right);
  `border-radius: 50% !important` (gana al reset `*`). Círculo de **96 px (6rem)** con trazo
  fino por pedido de elegancia: amarillo 5 px `inset:0` + `giroRueda` (horario →); blanco
  3 px `inset:17px` + `giroRuedaInversa` (clave nueva: `rotate(-360deg)`, antihorario ←).
  Radios 48 px vs 31 px ⇒ el amarillo rodea al blanco con 12 px de aire.
- **Splash eliminado sin restos:** HTML en `encabezadoAdmin.php`, JS `load`+800 ms en
  `pieAdmin.php` (su banner QUÉ HACE pasa a "helper global de toast") y CSS
  `.splash-screen`/`.splash-content`. `.spinner-rueda` **sigue vivo** en el overlay de logout
  y en el botón de login ⇒ no se tocó su componente.
- **Si alguien altera esta parte →** quitar el `inset:0` del amarillo rompe la concenricidad;
  un `border-color` de un solo lado deja arcos de 90° en vez de 180°; quitar el `!important`
  del radio vuelve al cuadro por el reset `*` de `admin.css:46-49`.
- **Evidencia (navegador real, muestreo cada 180 ms):** Δángulo **+75°** (amarillo, derecha)
  y **−75°** (blanco, izquierda); diámetro **96 px** con grosores **5 px / 3 px**; overlay
  **2288 ms** con el efecto a 2 s (antes 3117 ms a 3 s; inválido: 2462 ms); `#splashScreen`
  ya **no existe** en `/dashboard`; consola 0 errores;
  `php -l` 20/0; gate `-SoloNuevos` 0/0/0 (4 archivos modificados); smoke `/login` sin
  sesión: `spinner-doble` presente, `spinner-rueda` ×1 (botón), sin `splashScreen`.

---

### Fase 12 — SweetAlert y Alertify locales (fin de 2 CDN)

**Pedido (2026-10-05):** "descargá los plugins de sweetalert en las carpetas de los demás
plugins como bootstrap, después los implementamos". **Hallazgo:** no existe carpeta de
plugins locales — todo el panel carga **15 referencias CDN** (bootstrap, jQuery, leaflet,
select2, datatables, chart.js, sweetalert2, alertify). Con `question` el usuario acotó:
**SweetAlert en `Publico/Recursos/sweetalert/`** y **Alertify en `Publico/Recursos/alertify/`**.

- **Bajados con `curl -L` desde el mismo URL que ya usaba el panel**, por lo que son
  **byte-idénticos** a los servidos por el CDN (cero cambio de comportamiento):
  `sweetalert2.min.css` (30 251 B) + `sweetalert2.min.js` (79 150 B, resoluble del `@11`) y
  `alertify.min.css` (21 417 B) + `alertify.min.js` (36 978 B, v1.13.1). Licencias
  incluidas en los banners de cada archivo.
- **4 referencias cambiadas** al patrón `BASE_URL` de `admin.css:35`: `encabezadoAdmin.php`
  (2 `<link>`) y `pieAdmin.php` (2 `<script>`). **0 referencias CDN** restantes de estos dos
  plugins; `login.php` no los usa. Quedan **11 CDN** de otros plugins por decisión de alcance.
- **Si alguien altera esta parte →** mover las carpetas exige actualizar esas 4 rutas;
  volver al CDN no cambia el comportamiento (mismo archivo de origen).
- **Evidencia:** las 4 rutas locales responden `200` con su content-type; en navegador
  `Swal` = `function` y `alertify` = `object`; popup de SweetAlert y toast de Alertify
  renderizados con sus estilos (capturas); **0 peticiones** a `cdn.jsdelivr.net` de estos
  plugins; consola sin errores; `php -l` 20/0; gate `-SoloNuevos` 0/0/0 (2 archivos M +
  2 carpetas nuevas).

### Fase 13 — DataTables local en español + cierre de sesión con alerta suave

**Pedido (2026-10-05):** 1) "descargá e instalá en `Publico/Recursos/` los plugins de
DataTables con su CSS, JS y traducción en español, listos para usarse"; 2) "al pulsar
cerrar sesión que aparezca una alerta suave de confirmación con botón de confirmar y
cancelar; al confirmar, el spinner de saliendo del sistema idéntico al del login pero
durando menos, y redireccionar al login destruyendo la sesión por completo". Con
`question` el usuario fijó la duración en **1200 ms** (vs 2000 ms del login).

- **DataTables local** (`Publico/Recursos/datatables/`): `jquery.dataTables.min.js`
  (87 221 B, v1.13.7 — misma versión que el CDN), `dataTables.bootstrap5.min.js`
  (2 358 B), `dataTables.bootstrap5.min.css` (12 163 B) e `i18n/es-ES.json`
  (10 700 B, del repo oficial `DataTables/Plugins`; el CDN no sirve i18n). Tres
  referencias CDN → `BASE_URL` en `encabezadoAdmin.php` y `pieAdmin.php`.
- **Hallazgo (detectado en prueba, corregido):** el JSON oficial **no trae `paginate`
  de raíz** y el renderer Bootstrap 5 lee el texto de ese nodo ⇒ los botones salían
  `Previous/Next`. Solución: `paginate` en español inline junto a `language.url`
  (`pieAdmin.php`), con comentario que documenta el porqué.
- **Cierre de sesión:** `prepararCierreSesion()` cablea **los 2 botones**
  (`#logoutBtn` y `.sidebar-logout` — este último antes salía directo, sin alerta) con
  SweetAlert2 (`question`, `Sí, salir`/`Cancelar`, tema oscuro del panel, botón
  confirmación `--primary` con texto `#131517` para contraste ≥7:1). Al confirmar,
  `mostrarOverlaySalida()` muestra `#logoutOverlay` con el **mismo `spinner-doble`**
  del login (96 px, arcos amarillo/blanco), espera **1200 ms**, ejecuta
  `fetch('/logout')` (destruye sesión: cookie + `session_destroy`) y navega a la URL
  final (`/login`).
- **admin.css:** `.logout-overlay` alineado al `.login-overlay` (z-index 2500, gap 18,
  `color-mix` 78 % + blur 4 px, texto 15 px) y nueva regla
  `.swal2-confirm.swal-confirmar-salida`.
- **Evidencia:** `php -l` 24/0 (lint global: 0 errores) · gate `-SoloNuevos` **0/0/0** ·
  4/4 archivos locales responden 200 y **0 peticiones** a `cdn.datatables.net` ·
  E2E Playwright: DataTables en español (`Anterior/Siguiente`, `Buscar:`,
  `Mostrando 1 a 5 de 5 registros`), **Cancelar** permanece en el panel, **Sí, salir**
  → overlay con spinner 96×96/2 arcos → `/login` en **1656-1657 ms** con sesión
  destruida (dashboard posterior → 302 a login), 0 errores de consola.
- **Gotcha del entorno:** las capturas de pantalla de Playwright pintaron un **overlay
  fantasma** (falso positivo) mientras `isVisible()`/DOM decían `display:none`; la
  verificación se hizo con asserts duros de la API de Playwright, no con imágenes.

---

### Fase 14 — Migración offline total de CDN con fallback en cadena

**Pedido (2026-10-05):** "hay que migrar los CDN a offline, pero con un código que verifique:
si hay internet use las CDN, si no detecta internet use los archivos locales, y si no
encuentra los locales que vuelva a buscar las CDN, y si no encuentra ninguno que muestre
alertas con Alertify que especifiquen el problema". Con `question` el usuario cerró:
**offline total (plugins + tipografías)** y **mecanismo por fallo de carga**
(CDN → local → CDN → alerta) en lugar de detección previa con `navigator.onLine`.

- **Descargas (34 archivos nuevos):** bootstrap css/js, bootstrap-icons css + 2 fuentes,
  jQuery 3.7.1, Leaflet css/js + 5 imágenes, Select2 css/js, Chart.js — estructura de
  carpetas **idéntica a la del paquete original** para no reescribir rutas relativas; y
  Google Fonts con UA de Chrome: `google-fonts.css` con **16 woff2** referenciados y
  **0 URLs externas** restantes.
- **Nuevo módulo `Publico/Recursos/js/gestorPlugins.js`** (deep module): handler global en
  **modo captura** del evento `error` (no burbujea, se captura en `document`) que recorre
  la secuencia `data-pasos` declarada por recurso en el HTML; API `cargarNiveles()`
  (3 niveles con `Promise.all`, **un fallo aislado no corta la cadena**), `alListo()`
  (callbacks tras libs + DOM, reemplaza a `DOMContentLoaded` para lo que usa libs) y
  `reportarFallos()` (**una sola alerta** de Alertify con nombre, secuencia y ruta de cada
  recurso; `alert()` nativo si Alertify faltara).
- **Hallazgo de pruebas (crítico):** en Chromium **mutar el `src` de un `<script>` fallido
  NO reanuda la carga** (verificado con spy de eventos: `SIN EVENTO tras mutación`); los
  `<script>` se reemplazan por un **nodo nuevo** en la misma posición. Los `<link>` sí
  recargan mutando `href` (los5 CSS CDN pasaron a local con `SPY-LOAD` y reglas reales).
- **Carrera `DOMContentLoaded` detectada:** `Panel/index.php` ejecutaba `new Chart(...)`
  en `DOMContentLoaded`; con carga asíncrona Chart podría no existir aún → migrado a
  `gestorPlugins.alListo(...)`. Las demás vistas usan solo DOM vanilla o handlers de click
  posteriores (sin cambio).
- **`admin.css` fuera de gestión:** su error espurio durante la navegación generaba una
  alerta de fallo falsa (carga perfecta verificada: 1 petición, 200, 22 847 B).
- **Secuencias:** los9 de CDN → `cdn,local,cdn` (con internet, CDN-first como pidió el
  usuario; verificado `origenJquery = code.jquery.com` en el E2E online); los ya locales
  (DataTables/SweetAlert/Alertify) → `local,cdn` como red de seguridad.
- **Fuera de alcance (aviso al usuario):** los **tiles de OpenStreetMap** son datos online,
  no un plugin: Leaflet los necesita del servidor de tiles con o sin internet.
- **Evidencia — 3 escenarios E2E Playwright:**
  1. **Online:** todas las libs desde CDN, `fallosCargaPlugins = []`, tabla "Mostrando 1 a
     5 de 5", chart 256×256, 0 errores de consola.
  2. **Offline simulado** (abort a jsdelivr/unpkg/code.jquery/datatables/fonts ×6): los9
     scripts en LOCAL (paso1), CSS con reglas reales (1298/2052/65/124/104), tabla y chart
     OK, `fallos = []`, 0 errores propios.
  3. **Sin locales** (CDN + jquery/bootstrap/bootstrap-icons locales abortados): alerta de
     Alertify con **4 recursos** (`cdn -> local -> cdn` + ruta local de cada uno) y la
     cadena siguió cargando el resto.
  - **Regresión logout:** alerta → spinner 96 px → **1258 ms** → `/login`.
  - `php -l` global 0 errores · guardián `-SoloNuevos` **0/0/0**.

---

## 3. Archivos afectados

| Archivo (punto central) | Rol | Si se modifica… |
|---|---|---|
| `Configuracion/app.php:31` | `require` de la conexión | Desaparece `obtenerConexion()` → cae el login |
| `Configuracion/conexion.php` | Singleton PDO | Se corta toda la BD |
| `Modelos/loginModel.php` | Consultas de auth | Se rompe el ingreso al panel |
| `Controladores/loginController.php` | Flujo login/logout | Se rompe la sesión |
| `index.php:20` (`$routes`) | Router + guard | Rutas huérfanas o rutas públicas indebidamente abiertas |
| `Modelos/{Pedido,Motorizado,Panel}.php` | Datos del dashboard | Se vacía el dashboard |
| `Controladores/panelController.php` | Render del dashboard | Si se quitan variables, `Vistas/Panel/index.php` falla |
| `Vistas/Plantillas/*.php` | Layout completo | Se desbordan **todas** las vistas |
| `Publico/Recursos/css/admin.css` | Tema del panel | Cambia la UI de todo el panel |
| `Publico/Recursos/datatables/*` | DataTables local + i18n es-ES | Si se mueven, actualizar las 3 rutas `BASE_URL` de encabezado/pie |
| `Publico/Recursos/js/gestorPlugins.js` | Fallback CDN↔local + cadena + alertas | Si se rompe, ningún recurso gestionado se carga |
| `Publico/Recursos/{bootstrap,bootstrap-icons,jQuery,leaflet,select2,chartjs,fonts}/` | Copias offline de los 9 CDN + fuentes | Si se mueven, actualizar `data-local` y las rutas de la cadena |
| `Vistas/Plantillas/pieAdmin.php` | Cargas del panel + DataTables + cierre de sesión | Se rompe el idioma de las tablas y la alerta de salida |
| `scripts/validar-estilo.ps1:131,161` | Guardián | Afecta a **todos** los proyectos |

---

## 4. Contratos

**Rutas (las 4 que quedan en `$routes`):**

| Ruta | Método | Éxito | Fallo |
|---|---|---|---|
| `/login` | GET | 200 | — |
| `/login/authenticate` | POST | 302 → `/dashboard` | 302 → `/login` + mensaje |
| `/logout` | GET | 302 → `/login` | — |
| `/dashboard` | GET | 200 (con sesión) | 302 → `/login` (sin sesión) |

**Payload de login:** `usuario` (string), `password` (string).

**Respuestas de error:** `Credenciales inválidas` (clave mal) y `Cuenta desactivada` (clave
correcta pero `estado_usuario ≠ 'activo'`). Cualquier otra ruta responde **404** (con sesión) o
**302** (sin sesión, el guard de `index.php` tiene prioridad).

---

## 5. Seguridad aplicada

- SQL **siempre parametrizado** (`?` + `execute([...])`), sin concatenación.
- Contraseñas con `password_verify()` sobre el hash **Argon2id** de la BD (nunca en texto plano).
- `session_regenerate_id(true)` al autenticar (fijación de sesión).
- Mensajes de error que **no** revelan si el usuario existe.
- Sin credenciales ni secretos escritos en el código ni en este documento.

---

## 6. Pruebas realizadas (corridas, con evidencia)

```powershell
# Sintaxis — 20 archivos, 0 errores
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }

# Gate de estilo — 0 errores, 0 avisos
pwsh -NoProfile -File "C:\Users\DELL\.config\opencode\scripts\validar-estilo.ps1" `
     -Ruta "C:\xampp\htdocs\3-ThimpsonExpressPanelAdministrativo" -SoloNuevos
# -> Resumen: 0 error(es), 0 aviso(s), 1 nota(s)   [la nota es el TODO con dueño]

# Deuda total — 0 errores (antes: 207)
pwsh -NoProfile -File "...\validar-estilo.ps1" -Ruta "C:\xampp\htdocs\3-ThimpsonExpressPanelAdministrativo"
# -> Resumen: 0 error(es), 0 aviso(s), 1 nota(s) | ARCHIVOS DEL PROYECTO: 21
```

**Resultado de las 10 pruebas HTTP (todas pasaron):**

| # | Prueba | Esperado | Obtenido |
|---|---|---|---|
| 1 | `GET /login` | 200 | 200 |
| 2 | `POST` credenciales válidas | 302 | 302 |
| 3 | `GET /dashboard` con sesión | 200 | **200, 20403 bytes, 0 Fatal/Warning/Notice** |
| 4 | `GET .../admin.css` | 200 | 200 |
| 5 | `POST` clave incorrecta | 302 | 302 + mensaje visible |
| 6 | 7 rutas borradas con sesión | 404 | **404 ×7** |
| 7 | `GET /logout` | 302 | 302 |
| 8 | `/dashboard` sin sesión | 302 | 302 |
| 9 | `/` sin sesión | 302 | 302 |
| 10 | `GET /login` tras logout | 200 | 200 |

**Regresión de los fixes del guardián** (fixture en `Temp\opencode\fixture-guardian`):

| Fixture | Esperado | Obtenido |
|---|---|---|
| `bueno.php` (sin banner de función) | sigue marcándose | ✅ marcado |
| `bueno-html.php` (cierre HTML de la skill) | 0 avisos | ✅ 0 |
| `malo-html.php` (DETALLES de 8 líneas) | aviso | ✅ aviso |
| `indent4.php` / `indent8.php` | ambos pasan | ✅ ambos pasan |

**Pruebas de la Fase 8 (login con `Enter`) y Fase 9 (botón de salida) — navegador real:**

| Prueba | Esperado | Obtenido |
|---|---|---|
| Enter en **contraseña** (pre-fix) | llega al panel | ❌ pegado en `/login`, **sin POST**, spinner eterno |
| Enter en **contraseña** (post-fix) | `/dashboard` | ✅ `/dashboard` |
| Enter en **usuario** | `/dashboard` | ✅ `/dashboard` |
| **Clic** en "Iniciar Sesión" | `/dashboard` | ✅ `/dashboard` |
| Consola del navegador | 0 errores | ✅ 0 |
| Overlay en reposo | oculto | ✅ `display:none` |
| Overlay al salir | visible con texto | ✅ `flex` + "Saliendo del sistema" + z-index 2000 |
| Salida del topbar | cierra sesión | ✅ → `/login` |
| Mismo flujo a **375px** | botón visible y funcional | ✅ `x=327, right=363` dentro del viewport → `/login` |
| Sin JS (degradación) | sale igual | ✅ es `<a href>` real a `/logout` |

**Pruebas de la Fase 11 (overlay de login, doble arco, fin del splash) — navegador real:**

| Prueba | Esperado | Obtenido |
|---|---|---|
| Overlay visible al enviar | `.show` → pantalla completa | ✅ captura con arcos + texto |
| Duración del overlay | ≥ 2000 ms | ✅ **2288 ms** hasta `/dashboard` (antes 3117 ms con 3000) |
| Giro amarillo (Δ en 180 ms) | > 0 (derecha) | ✅ **+75°** |
| Giro blanco (Δ en 180 ms) | < 0 (izquierda) | ✅ **−75°** |
| Grosor de los arcos | amarillo > blanco, fino y visible | ✅ 5 px / 3 px sobre círculo de 96 px |
| Credenciales inválidas | vuelve a `/login` con mensaje | ✅ 2462 ms + "Credenciales inválidas" |
| `#splashScreen` en `/dashboard` | ya no existe | ✅ `false` + captura directa |
| Spinner tras login | **uno solo** (el overlay) | ✅ secuencia: overlay → dashboard sin splash |
| Consola del navegador | 0 errores | ✅ 0 |

**Pruebas de la Fase 12 (plugins locales) — navegador real y HTTP:**

| Prueba | Esperado | Obtenido |
|---|---|---|
| Descarga de los 4 archivos | JS/CSS reales con licencia | ✅ 168 KB en total |
| Rutas locales vía `BASE_URL` | 4/4 con `200` | ✅ 200 + content-type correcto |
| `typeof Swal` / `alertify` | `function` / `object` | ✅ `function` / `object` |
| Render real | popup + toast con estilos | ✅ capturas `plugin-swal-local.png` y `plugin-alertify-local.png` |
| Peticiones CDN de estos plugins | 0 | ✅ **0** (4/4 salen de `Publico/Recursos/`) |
| Referencias CDN en plantillas | 0 | ✅ grep = 0 |
| Consola / `php -l` / gate | 0 / 20-0 / 0-0-0 | ✅ ✅ ✅ |

---

## 7. Decisiones y alternativas descartadas

| Decisión | Alternativa descartada | Motivo |
|---|---|---|
| Mapear sesión con columnas reales | Crear `user_email` en la BD | La columna no existe y **nadie la consume**; crearla era trabajo muerto |
| Borrar las 24 vistas huérfanas | Ponerles banner (63 errores sobre código muerto) | Ruido permanente sobre vistas sin controlador; el usuario eligió esta opción explícitamente |
| Conservar `Pedidos`/`Riders` con banner | Borrarlos también | Son módulos centrales con avance de UI del usuario |
| Corregir el guardián | Cambiar mis banners para acomodar el bug | La skill (fuente única) manda ese formato; acomodarse perpetuaba el defecto |
| `TODO` con dueño para el límite de intentos | Implementarlo ya | No estaba pedido; queda como deuda trazable |

---

## 8. Deuda y riesgos pendientes

- **`Controladores/loginController.php:68`** — `TODO(2026-Q4)`: sin límite de intentos de login
  → expuesto a fuerza bruta. Requiere decisión de arquitectura (¿contador por IP? ¿bloqueo?).
- **`git`:** **`40eaffd`** `feat: añadir overlay de login de 3 s con doble arco y quitar el
  splash` (5 archivos, +169/−39), **`e4c642d`** `style: agrandar el doble arco del login y
  afinar su trazo` (2 archivos, +17/−16), **`75aac60`** `chore: descargar SweetAlert y
  Alertify a Publico/Recursos` (7 archivos, 4 de ellos nuevos) y **`1eeac3d`** `style:
  reducir el efecto de carga del login a 2 segundos` (2 archivos) **subidos** a
  `origin/main`; verificado `HEAD` == `origin/main`.
- **`git`:** **`41b64b4`** `fix: blindar lectura de $error en vista de login`,
  **`5dc01c5`** `docs: registrar en la traza el commit del efecto de 2 segundos`,
  **`553dc30`** `feat: DataTables local…` (8 archivos, +428/−24), **`ec671ce`**, **`64e81ea`**,
  **`1942dfd`** (docs de la Fase 13) y **`0bfa44c`** `feat: migrar los 9 CDN y Google Fonts
  a offline con fallback en cadena` (37 archivos, +1658/−54, 34 de ellos nuevos) — todos
  **subidos** a `origin/main`. Verificado `HEAD == origin/main`.
- **Vistas sin controlador:** `Pedidos/*` y `Riders/*` quedaron huérfanas a propósito (el
  usuario las conservó). Al reconstruir sus controladores hay que setear `$pageTitle` y
  `$activeMenu` antes del include.
- **Deuda conocida NO tocada** (documentada en `AGENTS.md`): marcado 404 desbalanceado,
  9 links `?page=…` muertos, `var(--warning)` inexistente.
- **`editRider()`** se define en `Riders/index.php` pero se invoca desde `Riders/detail.php`:
  si esa vista se abre sola, el botón Editar falla (documentado en su banner).
- **`git`:** ambos commits están en `origin/main`: `1a57f3b` (login + limpieza + botón de
  salida) y `2fcc3e6` (Fase 10, spinners: 5 archivos, +73/−7). Verificado `HEAD == origin/main`.
- **`admin.css:46-49`** — `*, *::before, *::after { border-radius: var(--radius) !important }`
  con `--radius: 0px` **anula todo radio** que no lleve `!important` (incluye
  `.spinner-border` de Bootstrap). No se tocó: afecta a toda la UI. `.spinner-rueda` lo
  compensa con `!important` propio; corregir la raíz implica migrar ese reset a `:where()`
  y revisar los componentes que hoy dependan de ese comportamiento.
- **Contraste del spinner en el botón de login:** los segmentos amarillos van sobre fondo
  `--primary` (amarillo) ⇒ solo los blancos resaltan. Si no convence, variantes: segmentos
  `--primary`/`--foreground` solo en ese uso, o aumentar `--rueda-grosor`.

---

## 9. Cómo reproducir

```powershell
# 1. Migración de columna (una sola vez, en la BD 'thimpsonexpress')
#    ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS estado_usuario
#        varchar(20) NOT NULL DEFAULT 'activo';

# 2. Servidor (XAMPP con mod_rewrite; NO usar php -S)
#    http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/login

# 3. Verificación completa
php -l (todos los .php)                                  # 20/20
pwsh -NoProfile -File ".../scripts/validar-estilo.ps1" -Ruta "<proy>" -SoloNuevos   # 0/0
pwsh -NoProfile -File ".../scripts/validar-estilo.ps1" -Ruta "<proy>"               # 0/0
# + las 10 pruebas HTTP de §6
```

**Hechos confirmados** (corridos): sintaxis, guardián en ambos modos, 10 pruebas HTTP,
regresión de fixtures. **Supuestos no verificados:** que el `TODO` de límite de intentos se
implemente en futuro; que las vistas `Pedidos`/`Riders` funcionen al reconstruir sus
controladores (hoy devuelven 404, que es lo esperado).
