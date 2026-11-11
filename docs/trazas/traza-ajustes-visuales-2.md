<!--====================ENCABEZADO====================
TRAZA: ajustes visuales 2 — hamburguesa, buscador y favicon
ARCHIVO: docs/trazas/traza-ajustes-visuales-2.md
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: documenta cómo se implementó el objetivo de
    tres ajustes visuales pedidos por el usuario.
VINCULADO A: sigue el formato de la skill traza-objetivo;
    antecesora: traza-sidebar-colapsable.md.
FECHA: 2026-11-10 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================-->

<!--================CUERPO DEL CÓDIGO=================-->

# Traza — Ajustes visuales 2: hamburguesa del sidebar, buscador más amplio y favicon

**Resumen ejecutivo:** se reemplazó el chevron del botón de colapso por la hamburguesa
clásica (`bi-list`) y se movió dentro del sidebar, separado 12px del borde derecho; en
modo colapsado baja centrado bajo la cabecera como ítem de riel de 44px. El buscador
del topbar pasó de 560px/8×14 a 640px/10×16. Se creó un favicon "T" en ámbar
(SVG + ICO de 3 tamaños) declarado en los dos `<head>` del proyecto, eliminando el
404 de `/favicon.ico`. Todo verificado con lint, higiene, guardián de estilo, smoke
HTTP y E2E real en navegador (consola sin errores).

## Criterios de aceptación

1. Botón de colapso con las tres rayitas clásicas, NO superpuesto al borde del sidebar,
   con aire propio, ordenado, y sin romper: navegación, tooltips, off-canvas móvil,
   persistencia localStorage.
2. Cuadro de búsqueda del topbar más largo y con más padding interno.
3. Favicon con la "T" que mata el `GET /favicon.ico 404`.

## Fase 1 — Favicon (SVG + ICO + declaración)

**Archivos:**
- `Publico/Recursos/img/favicon.svg` (nuevo): T sobre `#FBB03B`, rejilla 64, esquinas
  rectas (`--radius: 0px` del diseño). Barra x 12→52 y 15→25; vástago x 27→37, y 25→49.
- `scripts/generar-favicon.php` (nuevo): genera `favicon.ico` con la MISMA rejilla 64
  escalada a 16/32/48 px (GD dibuja la T con dos `imagefilledrectangle`; PNG envueltos
  en el contenedor ICO). Se autoverifica al correr: cabecera ICO + 3 muestras de color.
- `Vistas/Plantillas/encabezadoAdmin.php:26-29` y `Vistas/Auth/login.php:26-29`:
  `<link rel="icon">` para `.ico` (`sizes="any"`, respaldo universal) y `.svg`
  (`type="image/svg+xml"`, el que usan Chrome/Edge/Firefox). Ambos vía `urlAsset()`.

**Decisión clave:** los navegadores piden `/favicon.ico` en la raíz del servidor SOLO
cuando la página no declara icono. Declarar `rel="icon"` apagó esa petición: el 404
desaparece sin tocar la raíz de XAMPP (donde viviría un favicon compartido por todos
los proyectos). Alternativa descartada: solo SVG (Safari y navegadores viejos no lo
soportan de forma confiable).

**Si alguien altera esta parte →** si cambia la geometría de la T hay que repetirla en
el SVG y en las constantes `BARRA_*`/`VASTAGO_*` del generador (rejilla 64 compartida);
si se borra el `.ico`, regenerarlo con `php scripts/generar-favicon.php` (requiere GD,
disponible en este XAMPP).

## Fase 2 — Botón hamburguesa del sidebar

**Archivos:**
- `Vistas/Plantillas/barraLateralAdmin.php:30-39`: `<i class="bi bi-list">` reemplaza
  al chevron; comentario del DOM actualizado.
- `Publico/Recursos/css/admin.css:287-321`: `.sidebar-colapsar` deja de anclarse con
  `right: -14px` (mitad fuera del aside) y pasa a `left: calc(100% - 40px)` — 28px de
  botón + 12px de aire, TODO dentro del sidebar. El anclaje porcentual con `left`
  hace que el botón se deslice suave mientras el ancho del aside hace su transición.
  Transiciones nuevas: `top`, `left`, `width`, `height` (mismo bezier que el ancho del
  aside) + `font-size` en el icono.
- `Publico/Recursos/css/admin.css:365-383` (dentro del bloque `min-width: 992px`):
  en colapsado el botón baja centrado bajo la cabecera como ítem de riel de 44×44
  (`top: 76px`, `left: calc(50% - 22px)`, icono a 18px) y `.sidebar-nav` reserva el
  renglón con `padding-top: 52px`.
- Se ELIMINÓ la regla de rotación 180° del chevron: la hamburguesa es simétrica y vale
  para ambos estados.
- `Publico/Recursos/css/admin.css:1247`: sigue oculto en móvil (`display: none`
  ≤991.98px), intacto.

**Decisión clave:** el riel colapsado (76px) no admite el botón junto al logo centrado
(logo 36px + gap 12 + botón 28 = 84px > 76px), y en la esquina superior derecha
también colisiona (logo x 20→56 vs botón x 36→64). Se descartó: achicar el logo,
descentrarlo y mover el botón al footer. Ganó "ítem de riel centrado bajo la
cabecera": mismo patrón táctil de 44px que Dashboard/Usuarios.

**Contrato de geometría (los tres números viajan juntos):**
cabecera de 68px de alto → `top: 76px`; riel de 76px → `left: calc(50% - 22px)`;
botón de 44px → `padding-top: 52px` del nav (11px de aire con el primer ítem,
verificado). **Si cambia el alto de la cabecera o el ancho del riel, recalcular los
tres.**

**Si alguien altera esta parte →** si se toca `.sidebar-header` (padding/alto del
logo) o `--sidebar-ancho-colapsado`, el botón colapsado queda desalineado o montado
sobre el primer ítem; el JS (`prepararColapsoSidebar` de pieAdmin.php) NO cambió y no
hay que tocarlo: la forma de la hamburguesa no depende del estado.

## Fase 3 — Buscador del topbar

**Archivo:** `Publico/Recursos/css/admin.css:514-527` (`.topbar-search`):
`padding: 8px 14px` → `10px 16px` (más aire interno y más alto sin tocar tipografía);
`max-width: 560px` → `640px` (más largo donde el topbar tiene espacio; sigue
encogiendo con `flex` y sigue oculto ≤991.98px).

**Si alguien altera esta parte →** nada dependiente: son valores estéticos aislados
del resto del topbar (`.admin-topbar-izq` sigue siendo `flex: 1 1 auto` con
`min-width: 0`).

## Fase 4 — Verificación (todo corrido, evidencia en la sesión)

| Comprobación | Comando | Resultado |
|---|---|---|
| Lint PHP (4 tocados) | `php -l <archivo>` | Sin errores |
| Lint proyecto completo | higiene chequeo 1 | 24 archivos, sin errores |
| Higiene total | `.\scripts\higiene.ps1` | "Sin problemas de higiene" |
| Guardián de estilo | `validar-estilo.ps1 -SoloNuevos` | 0 errores, 1 aviso preexistente |
| Favicon por HTTP | `curl favicon.ico` / `favicon.svg` | 200 y 200 |
| Generador ICO | `php scripts/generar-favicon.php` | 532 bytes; esquina/barra/vástago OK |
| E2E expandido | Playwright, 1440×900 | icono `bi-list`; botón 28×28, top 20, 13px de aire al borde, dentro del aside, sin tocar el logo; buscador 640px, padding 10px 16px |
| E2E colapsado | Playwright | botón 44×44, top 76, centrado (±1px), sin tocar logo, 11px de aire con primer ítem, nav padding 52px, `aria-expanded=false` |
| Regresión tooltip | hover en ítem colapsado | Tooltip "Usuarios" visible en body |
| E2E ciclo | expandir de vuelta | top 20, 13px de aire, `aria-expanded=true`, localStorage '0' |
| E2E móvil 375px | resize + toggles | botón oculto; off-canvas abre (left 0) y cierra (left −280); etiquetas visibles |
| Consola | `browser_console_messages` | 0 errores, 0 advertencias |
| Red favicon | `browser_network_requests` | `.svg` y `.ico` → 200; ya no aparece la petición a `/favicon.ico` |
| Capturas | `.playwright-mcp/ajustes2-*.png` | expandido / colapsado / móvil (carpeta ignorada por git) |

## Riesgos y deuda

- **Geometría acoplada del botón colapsado** (ver contrato de la Fase 2): es el único
  costo del diseño; quedó documentado en el AGENTS.md.
- La T del favicon es un dibujo de rectángulos, no la tipografía Space Grotesk real:
  los favicones no cargan fuentes externas. Aceptado a cambio de nitidez garantizada.
- `favicon.ico` es un binario versionado: la fuente de verdad para regenerarlo es el
  script (no se edita a mano).

## Cómo reproducir

```powershell
php -l Vistas\Plantillas\encabezadoAdmin.php
php -l Vistas\Auth\login.php
php -l Vistas\Plantillas\barraLateralAdmin.php
php scripts\generar-favicon.php
.\scripts\higiene.ps1
curl.exe -s -o NUL -w '%{http_code}' http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/Publico/Recursos/img/favicon.ico
# Navegador: http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/login (admin / Admin12345)
```

<!--FIN-->
