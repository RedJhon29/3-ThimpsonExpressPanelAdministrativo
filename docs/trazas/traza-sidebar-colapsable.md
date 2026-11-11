# Traza — Sidebar colapsable del Panel Thimpson Express

## Resumen ejecutivo

Se agregó un botón chevron pegado al borde derecho del sidebar (solo escritorio, ≥992px) que colapsa/expande el menú. En modo colapsado el sidebar muestra solo los iconos de los ítems (44px, centrados), con tooltips flotantes al hover/focus y `aria-label` dinámico; nada de la funcionalidad existente se pierde. El estado se persiste en `localStorage` (`panel.sidebar.colapsado`) y se aplica durante el parseo (script anti-parpadeo), por lo que sobrevive la navegación y los reinicios del navegador. El modo off-canvas del móvil quedó intacto: todo el CSS de colapso vive dentro de `@media (min-width: 992px)` y el botón se oculta debajo de ese ancho. Verificado con lint, higiene, guardián de estilo y pruebas E2E en navegador real.

## Objetivo y criterios de aceptación

| Criterio | Estado |
|---|---|
| Botón pequeño para colapsar/expandir el sidebar | ✓ Chevron de 28px en el borde derecho |
| Colapsado: solo iconos de las opciones existentes | ✓ 76px, ítems de 44px centrados |
| Sin perder funcionalidades (menús, submenús, etc.) | ✓ Navegación, modal Configuraciones y DataTables intactos; patrones listos para submenús futuros (flyout) |
| Sin dañar el UI | ✓ Mismo sistema de tokens, radios cuadrados, animación sincronizada sidebar/main |
| Persistencia entre páginas (decisión del usuario) | ✓ localStorage + script anti-parpadeo |
| Tooltips al hover colapsado (decisión del usuario) | ✓ Bootstrap Tooltip con `container:'body'` + respaldo nativo |
| Off-canvas móvil intacto | ✓ Verificado E2E a 375px |

## Fase 1 — Estructura HTML (barraLateralAdmin.php)

- Botón `#colapsarSidebar` como primer hijo del `<aside>`: `position: absolute; right: -14px; top: 20px`, anclado al aside (`position: fixed`), con `aria-controls`, `aria-expanded`, `aria-label` y `title`.
- Etiquetas de los 3 ítems envueltas en `span.sidebar-etiqueta` (Dashboard, Usuarios, Configuraciones): única fuente del texto visible, del tooltip y del `aria-label` colapsado. Íconos con `aria-hidden`.
- Marca envuelta en `div.sidebar-marca` para poder ocultarla sin selectores estructurales.
- Script anti-parpadeo tras el `</aside>`: aplica `admin-colapsado` al `<body>` durante el parseo leyendo `localStorage` (solo si la pantalla es escritorio), sin esperar la cadena de librerías del pie.
- **Si alguien altera esta parte** → si cambia la key `panel.sidebar.colapsado` o el breakpoint `992px` hay que tocar también `prepararColapsoSidebar()` en pieAdmin.php; si se quita `span.sidebar-etiqueta` de un ítem, ese ítem queda mudo en modo colapsado (sin tooltip ni aria-label).

## Fase 2 — Estilos (admin.css)

- Tokens nuevos: `--sidebar-ancho: 280px` y `--sidebar-ancho-colapsado: 76px`; los tres `280px` hardcodeados (sidebar, admin-main, media query móvil) pasaron a tokens. Ahora son 28 tokens.
- Scroll del sidebar movido del aside (`overflow-y: auto` → fuera) al `.sidebar-nav` (`min-height: 0; overflow-y: auto`): cabecera y pie quedan fijos y el botón del borde puede sobresalir (`right: -14px`) sin recorte. El aside pasó de `min-height` a `height: 100vh`.
- Bloque "Botón de colapso": cuadrado 28px (respeta el reset global de radios), chevron rota 180° con `body.admin-colapsado` (el ícono correcto no depende del JS).
- Bloque "Sidebar colapsado": TODO dentro de `@media (min-width: 992px)` — ancho 76px, `margin-left` del main, `display: none` para marca/etiquetas/títulos de sección, logo centrado e ítems de 44px centrados.
- Skin del tooltip (`.tooltip { --bs-tooltip-* }`) alineado al tema del panel.
- Media query ≤991.98px: `.sidebar-colapsar { display: none }` (en móvil manda el off-canvas).
- **Si alguien altera esta parte** → sacar una regla del bloque `@media (min-width: 992px)` rompe el off-canvas del móvil (la clase colapsada se aplicaría también en pantallas chicas); borrar `min-height: 0` del nav revive el scroll completo del aside y recorta el botón del borde.

## Fase 3 — Comportamiento (pieAdmin.php)

- `prepararColapsoSidebar()` (la llama `inicializarPanel()` tras la carga de librerías):
  - Captura los ítems (`.sidebar-link, .sidebar-config`), sus etiquetas, sus `title`/`aria-label` originales, y crea instancias de `bootstrap.Tooltip` (`placement: 'right'`, `container: 'body'`) si Bootstrap cargó.
  - `aplicarColapso(colapsar, guardar)`: vuelca el estado en la clase del body, `aria-expanded`/`title`/`aria-label` del botón, enable/hide+disable de los tooltips, `aria-label` de los ítems (la etiqueta visible desaparece) y `title` nativo de respaldo sin Bootstrap. Solo el click del usuario escribe en `localStorage` (`guardar=true`); la carga inicial y los cambios de breakpoint aplican, no guardan.
  - Re-sincronización con `matchMedia('(min-width: 992px)')` al cambiar el tamaño: al entrar a móvil quita la clase y apaga tooltips; al volver a escritorio restaura lo persistido.
- **Si alguien altera esta parte** → cambiar la key o el breakpoint sin tocar el script de barraLateralAdmin rompe la persistencia (parpadeo o estado perdido); los tooltips dependen de que los ítems conserven `span.sidebar-etiqueta`.

## Contratos

| Elemento | Contrato |
|---|---|
| Key de persistencia | `localStorage['panel.sidebar.colapsado']` = `'1'` \| `'0'` (definida en DOS archivos) |
| Clase de estado | `body.admin-colapsado` (solo tiene efecto visual ≥992px) |
| Breakpoint | 992px (mismo que el off-canvas existente) |
| Elementos | `#colapsarSidebar` (botón), `.sidebar-etiqueta` (etiquetas), `.sidebar-link`/`.sidebar-config` (ítems con tooltip) |
| Tokens | `--sidebar-ancho`, `--sidebar-ancho-colapsado` |

## Reglas respetadas

- Sin código nuevo en rutas PHP ni consultas: cambio puramente de plantilla/CSS/JS.
- Accesibilidad: `aria-expanded` en el botón, `aria-label` dinámico en los ítems colapsados (el ícono con `aria-hidden` no los nombra solo), tooltips con trigger hover+focus, botón `<button type="button">` focusable.
- El off-canvas móvil y su toggle `#toggleSidebar`/`#closeSidebar` quedaron intocados en lógica.
- Los formularios ocultos de borrado de DataTables no se tocaron (boundary del proyecto).
- El reset global de `border-radius` se respetó: el botón nuevo es cuadrado como el resto del panel.

## Pruebas realizadas (todas corridas)

| Comando | Resultado |
|---|---|
| `php -l` sobre barraLateralAdmin.php, pieAdmin.php, encabezadoAdmin.php | "No syntax errors detected" |
| `.\scripts\higiene.ps1` | "Sin problemas de higiene" (5/5 chequeos, 23 archivos linteados) |
| `pwsh ... validar-estilo.ps1 -Ruta . -SoloNuevos` | 0 errores, 3 avisos (los 3 preexistentes de banners largos del formato del proyecto) |
| Smoke: `curl` a /login y /dashboard | 200 y 302→login (sesión requerida, esperado) |
| E2E Playwright (login real admin) | Colapso: clase en body, 76px, etiquetas `none`, ítems 44px centrados (38=38), ícono rotado 180°, aria-labels asignados, localStorage `'1'` |
| E2E tooltip al hover | "Usuarios" visible en `body > .tooltip`, fuera del recorte del aside |
| E2E persistencia | Navegación a /usuarios carga colapsado sin parpadeo; tras cerrar y reabrir el navegador sigue colapsado |
| E2E expansión | 280px, etiquetas visibles, `aria-label` removidos, title original de Configuraciones restaurado |
| E2E móvil 375px | Botón oculto, off-canvas translateX(-280) con etiquetas, hamburguesa abre, X cierra |
| E2E breakpoint | Vuelta a 1440px restaura el colapso persistido solo con el listener de matchMedia |
| Consola del navegador | 0 errores, 0 warnings |

Capturas de evidencia: `%TEMP%\opencode\sidebar-colapsado.png`, `sidebar-colapsado-tooltip.png`, `sidebar-final-colapsado.png`, `sidebar-regresion-final.png`.

## Decisiones y alternativas descartadas

- **Chevron en el borde** (elección del usuario) sobre botón en el header o en el topbar: viaja con el borde, no ocupa espacio del header colapsado.
- **Tooltips Bootstrap con `container: 'body'`** sobre tooltip CSS puro: el CSS puro quedaría recortado por el overflow del sidebar; sobre `title` nativo permanente: duplicaría con el tooltip estilizado.
- **`matchMedia` + listener de cambio** sobre CSS only: apaga los tooltips al pasar a móvil (donde la etiqueta vuelve a estar visible) y restaura el estado al volver a escritorio.
- **Scroll movido al nav** sobre dejar `overflow-y` en el aside: único modo de que el botón del borde sobresalga sin recorte; además fija cabecera y pie.
- **Estado visual dentro de `@media (min-width: 992px)`** sobre reglas globales + overrides: garantía estructural de que el móvil nunca se ve afectado, en vez de confiar en el orden/specificity.
- **Cierre de banners JS con `*/` solo** (formato que el guardián reconoce y que ya usaba `inicializarPanel`): corrige también el falso positivo preexistente en `etiquetarCamposDataTables`.

## Deuda / riesgos

- La key `panel.sidebar.colapsado` está en dos archivos (documentado en AGENTS.md y en los banners): es el único contrato duplicado del cambio.
- Submenús futuros (Riders/Pedidos/Socios): en modo colapsado deben salir como flyout (el patrón del tooltip). Hoy no existen submenús, así que no hay código especulativo para ellos — se documentó la vía de integración en vez de escribirla a ciegas.
- El chequeo E2E del respaldo sin Bootstrap (CDN y local caídos) se verificó por diseño (rama simple con `title` nativo), no con un escenario real de doble caída.

## Cómo reproducir

1. XAMPP corriendo (`http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/`).
2. Iniciar sesión (`admin` / `Admin12345`).
3. Click en el chevron del borde derecho del sidebar → colapsa; hover sobre un ícono → tooltip.
4. Navegar entre Dashboard y Usuarios → el estado persiste; F5 → sin parpadeo.
5. DevTools en modo responsive <992px → el botón desaparece y el off-canvas sigue funcionando con la hamburguesa.

---

FECHA: 2026-11-10 | ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
