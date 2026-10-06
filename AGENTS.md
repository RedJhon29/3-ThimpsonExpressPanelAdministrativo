# AGENTS.md — Thimpson Express Panel Administrativo

**Identidad:** Panel admin de delivery (Nicaragua). MVC propio en PHP 8.2 sin framework, sin Composer. Base de datos PostgreSQL `thimpsonexpress` vía PDO en `Configuracion/conexion.php`.

---

## Comandos verificables

| Acción | Comando |
|--------|---------|
| Lint PHP | `php -l <archivo>` (ejecutar sobre todos los `.php`; 0 errores actuales) |
| Smoke test HTTP | `curl -s -o /dev/null -w '%{http_code}' http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/<ruta>` |
| Verificar todo | `Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }` + testear las 37 rutas del `$routes` |

**Orden:** lint → smoke test de la ruta modificada.

---

## Arquitectura real (no deducible de nombres)

- **Front controller:** `index.php` con array `$routes` literal (`index.php:20-101`). Agregar ruta = agregar entrada ahí.
- **Autoload:** `Configuracion/app.php:22-31` busca `Modelos/<Clase>.php` y luego `Controladores/<Clase>.php`. Por eso:
  - Controladores = camelCase (`pedidoController`)
  - Modelos = PascalCase (`Pedido`)
  - El nombre de clase **es** el nombre de archivo.
- **Vistas:** cada una incluye `encabezadoAdmin` + `barraLateralAdmin` (líneas 1-2) y `pieAdmin` (última línea). El controlador **debe** setear `$pageTitle` y `$activeMenu` **antes** del include.
- **Detalle:** las vistas `Pedidos/detail.php:5-6` y `Riders/detail.php:5-6` re-leen `$_GET['id']` y re-consultan el modelo aunque el controlador ya lo hizo. Es el patrón vigente.

---

## Convención de UI que se rompe sola

`$activeMenu` debe coincidir con la key que `barraLateralAdmin.php:20` compara (`active`). **24 rutas** lo asignan bien pero **no tienen link en el sidebar** → nunca se highlightean. El sidebar tiene 13 links contra 37 rutas.

---

## Gotchas verificados (deuda conocida, no arreglar aquí)

| Qué | Dónde | Por qué no arreglar en este PR |
|-----|-------|--------------------------------|
| Markup 404 desbalanceado | `index.php:135-137` incluye `encabezadoAdmin` + `Errores/404` + `pieAdmin` saltándose `barraLateralAdmin`; `pieAdmin.php:1-2` cierra `</main></div>` que solo `barraLateralAdmin.php:90,124` abre | Requiere refactor de plantillas; fuera de alcance |
| 9 links `?page=orders` muertos | `Pedidos/index.php`, `Pedidos/detail.php`, `Riders/detail.php` | El router usa path (`index.php:108`), ignora query string. Verificado: `/orders/detail?page=orders` muestra detalle, no lista |
| `var(--warning)` (36 usos) y `var(--dark-band)` (1 uso) no existen | `admin.css:9-31` define 21 tokens; ninguno es `--warning` | Requiere design-system pass; fuera de alcance |

---

## Higiene (regla obligatoria)

**Objetivo:** mantener la arquitectura, la infraestructura y el código limpios, legibles y mantenibles. Avisar cada vez que el código tome un rumbo que después sea difícil de mantener o comprender.

**Reglas concretas:**

1. **Sin archivos huérfanos.** Todo `.php` en `Vistas/`, `Modelos/` o `Controladores/` debe ser referenciado por una ruta, un controlador o un `include`. Si se elimina su único consumidor, se elimina el archivo.
2. **Sin rutas muertas.** Cada entrada de `$routes` en `index.php` debe apuntar a un controlador y método que existan. Si se borra un método, se borra su ruta.
3. **Sin includes rotos.** Todo `include VIEW_PATH . '/...'` debe resolver a un archivo existente.
4. **Sin lógica duplicada.** Si dos vistas comparten campos o markup, se extrae a un partial (`_nombre.php`) y se incluye.
5. **Sin secretos en código.** Credenciales solo en `Configuracion/conexion.php` (desarrollo) o variables de entorno (producción).
6. **Sin SQL suelto en vistas.** Toda consulta va en un Modelo, con prepared statements.
7. **Sin `echo` sin escapar.** Todo dato de BD o de `$_GET`/`$_POST` se imprime con `htmlspecialchars()`.
8. **Sin acciones destructivas por GET.** Borrar, desactivar o cambiar estado siempre por POST + token CSRF.

**Verificación:** correr `scripts/higiene.ps1` antes de cada commit. Si reporta algo, se corrige antes de seguir.

---

## Boundaries (nunca hacer)

- **No agregar PDO/mysqli ni migraciones** asumiendo que hay schema. La BD ya existe (`thimpsonexpress`), pero no hay migraciones versionadas; si una tarea necesita cambiar el schema, es decisión de arquitectura → preguntar.
- **No renombrar `Configuracion/app.php` ni `index.php` sin actualizar ambos:** `BASE_URL` está en `Configuracion/app.php:13` y `$projectPrefix` en `index.php:14`. Cambiar uno rompe el otro.
- **No asumir que `$activeMenu` highlightea:** solo 13 keys existen en el sidebar.
- **No usar `php -S` built-in:** el `.htaccess` requiere `mod_rewrite` de Apache. El servidor de desarrollo es XAMPP en `http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/`.

---

## Referencias

| Tema | Archivo |
|------|---------|
| Config base + autoload | `Configuracion/app.php` |
| Router + rutas | `index.php` |
| Plantillas (header/sidebar/footer) | `Vistas/Plantillas/*.php` |
| Tokens CSS | `Publico/Recursos/css/admin.css:9-31` |
| Modelos (datos estáticos) | `Modelos/*.php` |
| Conexión BD (placeholder) | `Configuracion/conexion.php` |

---

## Notas operativas

- Commits: Conventional Commits (prefijo en inglés, cuerpo en español). Rama única `main`.
- Sin tests, sin CI, sin `.env`, sin README.
- `conexion.php` creado en blanco para futura implementación de BD.