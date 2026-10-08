# AGENTS.md — Thimpson Express Panel Administrativo

**Identidad:** panel admin de delivery (Nicaragua). MVC propio en PHP 8.2, sin framework, sin Composer. PostgreSQL `thimpsonexpress` vía PDO en `Configuracion/conexion.php`. **La base tiene una sola tabla: `usuarios`.**

> ## ⚠️ Recordatorio: 2 cosas esperando al usuario
>
> Last checked **2026-10-08**. El usuario pidió que se le recuerden en cada sesión nueva. Si las resolvés, **borrá esta caja**.
>
> ### 1. Auditoría de usuarios — falta tu OK
> **Bloqueada a propósito:** sería la **primera DDL del proyecto** y no hay migraciones versionadas. `AGENTS.md` prohíbe cambiar el schema sin preguntar, así que no se puede cerrar sola.
>
> Propuesta que quedó sobre la mesa:
> ```sql
> auditoria_usuarios(
>   id, id_usuario, accion,
>   datos_antes jsonb, datos_despues jsonb,
>   id_usuario_accion, ip, creado_en
> )
> ```
> + un `auditoriaModel`, invocado desde los 4 actions (`guardar`, `actualizar`, `eliminar`, `toggleEstado`).
> Nunca se registra `clave_usuario` ni el hash.
>
> ### 2. PAT de GitHub sin rotar — solo puede hacerlo el usuario
> Un token personal se usó para pushear y quedó embebido en la URL remota. Ya se sacó de `.git/config` y se movió a Windows Credential Manager, así que **el repositorio está limpio**, pero **el token sigue vigente** y hay que revocarlo:
> 1. GitHub → *Settings* → *Developer settings* → *Personal access tokens*
> 2. Revocar el token viejo
> 3. Después: `gh auth login`

---

## Comandos verificables

| Acción | Comando |
|--------|---------|
| Lint PHP | `Get-ChildItem -Recurse -Filter *.php \| ForEach-Object { php -l $_.FullName }` (21 archivos) |
| Higiene del proyecto | `.\scripts\higiene.ps1` (5 chequeos: lint, rutas muertas, includes, huérfanos, métodos) |
| Smoke test HTTP | `curl -s -o /dev/null -w '%{http_code}' http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/<ruta>` |
| Imágenes huérfanas | `php scripts/limpiar-fotos-huerfanas.php` |
| Autoload | `php -r 'require "Configuracion/app.php"; var_dump(class_exists("imagenesModel"));'` |

**Orden:** lint → higiene → smoke test de la ruta tocada → navegador.

Servidor: XAMPP en `http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/`. **No usar `php -S`**: el `.htaccess` necesita `mod_rewrite` de Apache.

---

## Arquitectura real

- **Front controller:** `index.php` con el mapa literal `$routes` (`index.php:95-110`). Agregar ruta = agregar entrada ahí. Hoy son **11 rutas**.
- **Autoload:** `Configuracion/app.php:42` busca `Modelos/<Clase>.php` y luego `Controladores/<Clase>.php`. El nombre de clase **es** el nombre de archivo.

| Carpeta | Convención | Archivos |
|----------|-----------|----------|
| `Controladores/` | camelCase + `Controller` | `loginController`, `panelController`, `usuariosController` |
| `Modelos/` | camelCase + `Model` | `imagenesModel`, `loginModel`, `panelModel`, `usuariosModel` |

- **Vistas:** cada una incluye `encabezadoAdmin` + `barraLateralAdmin` y cierra con `pieAdmin`. El controlador setea `$pageTitle` y `$activeMenu` **antes** del include. `pieAdmin` cierra el `<main>` y el `div.admin-main` que abre la barra lateral: si una vista no incluye la barra lateral, el HTML queda desbalanceado.
- **Sidebar:** 2 enlaces (`Dashboard`, `Usuarios`). Las categorías `Plataforma`, `Gestión` y `Sistema` están vacías a propósito.

---

## Imagenes: `imagenesModel`

Un solo modelo maneja las imágenes de todos los módulos. Es la pieza a reutilizar, no a duplicar.

| Elemento | Valor |
|----------|-------|
| Raíz | `Publico/Recursos/uploads/` (debe quedar bajo `Publico/`; el `.htaccess` raíz solo sirve esa carpeta) |
| Default | `default/default.jpg` — versionada en git |
| Carpeta por registro | `<modulo>/<id>_<nick>_<AAAAMMDD>/` |
| Módulos permitidos | `usuarios`, `conductores`, `socio-conductores`, `negocios` (lista blanca) |
| Protección | `Publico/Recursos/uploads/.htaccess` desactiva la ejecución de scripts |
| Git | las carpetas de usuario se ignoran; `default/` y `.htaccess` se versionan |

**Alta de un módulo nuevo:** `imagenesModel::guardar($archivo, 'conductores', $id, $nombre)`. No escribir lógica de archivos en otro sitio.

Para un módulo: `usuariosModel::MODULO_IMAGENES = 'usuarios'` es el puente entre el modelo de datos y el de imágenes.

---

## Seguridad

- **Solo el `superadmin` puede crear, editar, cambiar estado y eliminar usuarios.** Un `lector` que haga POST directo a `/usuarios/guardar` o `/usuarios/actualizar` recibe **403**. Verificado.
- Un `superadmin` nunca se borra (ni individual ni en lote): se edita o se desactiva. Las reglas viven todas en `usuariosController::motivoBloqueoBorrado()`.
- Toda escritura: **POST + token CSRF**. Cada action repite su propio guard a propósito; es una barrera visible, no un wrapper.
- El MIME de las imágenes se valida con `finfo` por contenido, nunca por la extensión que envía el cliente. El nombre del archivo lo genera el servidor con `random_bytes`.

---

## CSS

- Tokens del proyecto en `Publico/Recursos/css/admin.css` (26 definidos).
- **El reset global de `admin.css:47` aplica `border-radius: var(--radius) !important` a todo.** Con `--radius: 0` aplasta *todos* los radios. Para dejar un componente circular hay que declarar su radio con `!important` (patrón ya usado en los iconos de SweetAlert y en `.usuario-foto-imagen`). **No borrar el reset**: afecta a todo el panel.
- `urlAsset('Publico/Recursos/css/admin.css')` versiona el archivo con `filemtime()`. Los recursos propios **deben** pasar por ahí; los de terceros no.
- `--primary-foreground` es el token para texto sobre `--primary`.

---

## Higiene (regla obligatoria)

1. **Sin archivos huérfanos.** Todo `.php` en `Vistas/`, `Modelos/` o `Controladores/` debe ser alcanzable desde una ruta o un `include`.
2. **Sin rutas muertas.** Cada entrada de `$routes` debe apuntar a un controlador y método existentes.
3. **Sin includes rotos.**
4. **Sin lógica duplicada.** Si dos sitios comparten markup, se extrae a un partial.
5. **Sin secretos en código.** Credenciales solo en `Configuracion/conexion.php` (desarrollo) o variables de entorno (producción).
6. **Sin SQL suelto en vistas.** Toda consulta va en un modelo, con prepared statements.
7. **Sin `echo` sin escapar.** `htmlspecialchars()` en todo dato de BD o de `$_GET`/`$_POST`.
8. **Sin acciones destructivas por GET.**

**Verificación:** `.\scripts\higiene.ps1` antes de cada commit. El chequeo 4 busca huérfanos **por ruta** (`Vistas/Carpeta/archivo.php`) y, en modelos y controladores, **por nombre de clase**, que es como los resuelve el autoload. Antes comparaba por `BaseName`, así que `index.php` y `detail.php` nunca se marcaban aunque nadie los referenciara. Si se cambia ese chequeo, probar con un archivo huérfano de nombre genérico.

---

## Paginación

DataTables pagina, busca y ordena **en el cliente** (`pieAdmin.php:70`), y funciona: con 29 usuarios muestra «Mostrando 1 a 10 de 29» con páginas 1·2·3 y opciones de 10/25/50/100 filas. No hace falta tocarlo salvo que la tabla supere los miles de registros.

---

## Módulos pendientes de reconstruir

Las vistas de **Pedidos** y **Riders** se borraron: quedaban huérfanas, usaban variables que ya no existen y abrirlas daba error fatal. Se reconstruyen de a poco cuando toque.

| Quiero construir | Qué falta |
|---|---|
| Conductores / Riders | tabla en la BD, modelo, controlador, ruta en `$routes`, vistas, y un `MODULO_IMAGENES` en `imagenesModel::MODULOS` |
| Pedidos | lo mismo, más lo que sea dominio de pedidos |
| Socios-conductores / Negocios | idem, ya reservados en la lista de módulos |

Si querés recuperar el código viejo como referencia está en el commit `95caa1f`:

```powershell
git show 95caa1f:Vistas/Riders/index.php
```

---

## Deuda conocida (verificada, no arreglar sin decisión)

| Qué | Dónde | Por qué sigue |
|-----|-------|---------------|
| Sin auditoría | — | Ver la caja de recordatorio arriba. Bloqueada hasta que el usuario dé el OK. |
| Credenciales de desarrollo en el repo | `Configuracion/conexion.php` | Convención del proyecto. Mover a variables de entorno antes de producción. |
| Sin tests, sin CI | — | No hay framework de pruebas instalado. |
| Paginación solo en el cliente | DataTables en `pieAdmin.php:70` | Con 10k+ filas conviene pasar a `serverSide`. Ojo: los botones de la tabla apuntan con `form="form-eliminar-{id}"` a formularios ocultos que se renderizan en PHP **fuera** de la tabla; con `serverSide` esas filas no tendrían su formulario y el borrado fallaría en silencio. Hay que reestructurar el borrado antes. |
| El topbar no muestra la foto | `Vistas/Plantillas/encabezadoAdmin.php` | Muestra las iniciales en una caja cuadrada, no `imagenesModel::url()`. Es consistente con el resto, no es un bug. |

---

## Boundaries (nunca hacer)

- **No agregar migraciones ni cambiar el schema sin preguntar.** La BD existe pero no hay migraciones versionadas. Crear la primera DDL es una decisión de arquitectura.
- **No renombrar `Configuracion/app.php` ni `index.php`** sin actualizar ambos: `BASE_URL` está en `app.php:27` y el prefijo de ruta en `index.php`.
- **No tocar el `base` del autoload.** Cambiar la convención de nombres rompe los 7 archivos de una vez.
- **No borrar el reset global de `border-radius`** sin un pass completo por componente.
- **No apuntar la `foto_usuario` de un registro al `default/default.jpg` genérico.** Siempre a la copia dentro de su carpeta: la protección de `eliminar()` distingue ambas rutas.
- **No borrar los formularios ocultos que viven fuera de la tabla.** Los botones de la fila los referencian por `form=`.

---

## Referencias

| Tema | Archivo |
|------|---------|
| Constantes, autoload, `urlAsset()` | `Configuracion/app.php` |
| Conexión PDO | `Configuracion/conexion.php` |
| CSRF, sesión, errores traducidos | `Configuracion/seguridad.php` |
| Router + 11 rutas | `index.php` |
| Plantillas | `Vistas/Plantillas/{encabezadoAdmin,barraLateralAdmin,pieAdmin}.php` |
| Tokens CSS | `Publico/Recursos/css/admin.css:23-43` (los 3 de DataTables en `539-541`, los 2 de la rueda en `1226-1227`) |
| Gestión de imágenes | `Modelos/imagenesModel.php` |
| CRUD de usuarios | `Modelos/usuariosModel.php`, `Controladores/usuariosController.php` |
| Conteos del dashboard | `Modelos/panelModel.php` |

---

## Notas operativas

- Commits: Conventional Commits, prefijo en inglés, cuerpo en español. Rama única `main`.
- Sin `.env` ni `README`. El login de desarrollo es `admin` / `Admin12345`.
- Todo código nuevo lleva banner `ENCABEZADO/DETALLES/CUERPO` + `FIN`, en español.