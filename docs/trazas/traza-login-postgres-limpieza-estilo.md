# Traza: Login funcional contra PostgreSQL + limpieza de la deuda de estilo

**Proyecto:** Thimpson Express Panel Administrativo
**Fecha:** 2026-10-02 (ampliada el 2026-10-04/05 con fases 8, 9 y 10)
**Estado del objetivo:** cumplido (0 errores en ambos modos del guardián; 10/10 pruebas HTTP;
login, botón de salida y los 3 spinners tipo rueda verificados en navegador real)

## Resumen ejecutivo

Se habilitó el login real contra PostgreSQL (antes roto por `obtenerConexion()` inexistente),
se purgó el código muerto (71 archivos) y se eliminaron los **207 errores** del guardián de
estilo → **0 errores, 0 avisos** en los modos `-SoloNuevos` y total. Se corrigieron **2 bugs
del propio guardián** (Fase 7), **el bloqueo de la salida con `Enter`** que impedía llegar al
panel (Fase 8), se añadió el **botón de cierre de sesión con overlay** en la esquina superior
derecha (Fase 9) y se reemplazaron los **3 spinners** por rueditas circulares amarillo/blanco,
aprovechando el hallazgo del `border-radius: 0px !important` global para ganar el empate
(Fase 10). Todo quedó verificado con `php -l` (20/20), 10 pruebas HTTP y navegador real
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
| `php -l` sin errores | ✅ (20/20) |
| UI del panel intacta | ✅ (dashboard 200, CSS 200, sin cambios de HTML visible) |

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
- **`git` sin commitear:** 91 cambios (20 M + 71 D) **aún no commiteados**; además `main` está
  `ahead 1` (el commit `517d5b3` sigue sin subir). Requiere **aprobación explícita**.
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
