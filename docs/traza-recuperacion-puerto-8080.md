# Traza — Recuperación del panel administrativo en el puerto 8080

## Resumen ejecutivo

1. **El panel NO estaba roto**: `index.php`, `.htaccess` y el repo git estaban intactos.
2. **La causa era Apache**: un `<VirtualHost *:8080>` con `DocumentRoot "C:/xampp/htdocs/mfi"` (MFI) secuestraba el puerto 8080 completo.
3. Toda petición a `localhost:8080/<algo>` caía en `mfi/index.php`, que respondía `404 "Página no encontrada"` — de ahí el mensaje exacto que reportó el usuario.
4. Se desactivó ese VirtualHost (respetando que MFI sigue entrando por `localhost:8080/mfi/`, como documenta su propio `AGENTS.md`) y se reinició Apache.
5. Verificado por HTTP, por curl autenticado y en navegador real: login, dashboard y `/usuarios` funcionan.

---

## 1. Objetivo y criterios de aceptación

| Criterio | Estado |
|----------|--------|
| `http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/usuarios` responde 200 autenticado | CUMPLIDO |
| Login con `admin` / `Admin12345` funciona y redirige a `/dashboard` | CUMPLIDO |
| El resto de `htdocs` vuelve a servirse en 8080 (`/xampp/`, `/mfi/`) | CUMPLIDO |
| No se modificó ningún archivo del repo del panel | CUMPLIDO (`git status` limpio) |
| El puerto sigue siendo 8080 | CUMPLIDO (no se tocó `Listen 8080`) |

## 2. Fases del diagnóstico (cronológicas)

### Fase 1 — Reproducción
`curl` a `/usuarios`, `/`, `/login` e `/index.php`: **los cuatro dan 404**. Un bug del `.htaccess` habría dado 404 solo en las rutas limpias, no en `index.php`. Eso ya descartaba el panel.

### Fase 2 — Identificación del emisor del 404
El cuerpo de la respuesta era `Página no encontrada: /3-ThimpsonExpressPanelAdministrativo` — 60 bytes, texto plano, con cabeceras `Set-Cookie: MFI_SESSION`. Ese texto **no existe en el panel**: el 404 del panel (`Vistas/Errores/404.php`) es una página HTML completa con sidebar.

Búsqueda en `htdocs`: la frase exacta está en `mfi/index.php:229`. **El que respondía era el MFI.**

### Fase 3 — Configuración de Apache
`C:\xampp\apache\conf\httpd.conf`:
- `Listen 8080` (línea 60) — correcto, el puerto es el de siempre.
- `DocumentRoot "C:/xampp/htdocs"` (línea 252) — correcto.
- `AllowOverride All` (línea 273) — correcto, por eso el `.htaccess` del panel sigue teniendo efecto.

`C:\xampp\apache\conf\extra\httpd-vhosts.conf` (modificado **8/10/2026 17:07**):
```apache
<VirtualHost *:8080>
    ServerName mfi.local
    DocumentRoot "C:/xampp/htdocs/mfi"
    Alias "/mfi" "C:/xampp/htdocs/mfi"
    ...
</VirtualHost>
```
Un VirtualHost en `*:8080` **captura el 100% del tráfico** de ese puerto, sin importar el `ServerName` (el `*:80` de la línea 48 era un catch-all decorativo, sin efecto sobre 8080). Resultado: `htdocs` quedaba inalcanzable por completo.

`logs/mfi-access.log` lo confirmó: los 404 de `/xampp/`, `/4-ThimpsonExpressAppWeb/` y del panel **estaban todos registrados en el log de MFI**.

### Fase 4 — Reparación
Se comentó el bloque `<VirtualHost *:8080>` con la explicación de por qué secuestraba el puerto. **No se borró**: queda inerte pero documentado, por si MFI algún día necesita un puerto propio y ahí se decide con criterio.

Antes de tocar nada: copia de seguridad en `httpd-vhosts.conf.bak-20261009-pre-reparacion`.

> **Si alguien restaura ese VirtualHost activo, el panel vuelve a dar 404 y además cualquier proyecto nuevo en `htdocs` será invisible.**

## 3. Archivos afectados

| Archivo | Cambio | Si se altera |
|---------|--------|--------------|
| `C:\xampp\apache\conf\extra\httpd-vhosts.conf` | VirtualHost `*:8080` comentado | **Rompe el panel, `/xampp/`, `/mfi/` y todo `htdocs`**. Requiere reiniciar Apache. |
| `C:\xampp\apache\conf\extra\httpd-vhosts.conf.bak-20261009-pre-reparacion` | Nuevo (copia) | Ninguno; punto de retorno. |
| `index.php`, `.htaccess`, `Modelos/*`, `Controladores/*`, `Vistas/*` | **SIN CAMBIOS** | — |

`git status` en el repo del panel: **limpio**. No hubo ni un cambio de código.

## 4. Contratos verificados

| Ruta | Sin sesión | Con sesión |
|------|-----------|------------|
| `/login` | 200 | 200 |
| `/login/authenticate` (POST, `csrf_token` + `usuario` + `password`) | 302 → `/dashboard` | — |
| `/dashboard` | 302 → `/login` | **200** |
| `/usuarios` | 302 → `/login` | **200** |

Los nombres reales de campo son `csrf_token`, `usuario` y `password` (no `_token`/`clave`), tomados del HTML del login.

## 5. Seguridad

No se relajó nada. El guard de sesión de `index.php:115` sigue devolviendo 302 al login sin cookie válida, y los tokens CSRF se emitieron y validaron correctamente en el flujo real de login.

## 6. Pruebas ejecutadas

```powershell
# Sintaxis de Apache antes de reiniciar
& C:\xampp\apache\bin\httpd.exe -t -d C:/xampp/apache
# → Syntax OK (3 warnings de DocumentRoot inexistente, preexistentes)

# Reinicio
Get-Process httpd | Stop-Process -Force
Start-Process "C:\xampp\apache\bin\httpd.exe" -ArgumentList "-d","C:/xampp/apache"

# Smoke de rutas
curl -s -o NUL -w "%{http_code}" http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/usuarios   # 302
curl -s -o NUL -w "%{http_code}" http://localhost:8080/3-ThimpsonExpressPanelAdministrativo/login      # 200
curl -s -o NUL -w "%{http_code}" http://localhost:8080/xampp/                                            # 200
curl -s -o NUL -w "%{http_code}" http://localhost:8080/mfi/                                             # 200

# Login real con curl + cookie jar, CSRF extraído del HTML
# → login-post 302 → /dashboard ; dashboard 200 ; usuarios 200

# Navegador real (Playwright): login → dashboard → /usuarios
# → tabla renderiza, DataTables activo, 0 errores de consola
```

**Evidencia visual:** captura en `.playwright-mcp/panel-usuarios-ok.png`.

## 7. Decisiones y alternativas descartadas

| Alternativa | Por qué NO |
|-------------|-----------|
| Mover el MFI a un puerto nuevo (8090, 8083…) | Invasive: hay que tocar `BASE_URL` de MFI, su `.htaccess`, sus cookies y sus URLs absolutas. El usuario pidió arreglar **su** panel, no reubicar otro sistema. |
| Borrar el VirtualHost en vez de commented | Se pierde el contexto de por qué existía. Comentado es reversible y documentado. |
| "Arreglar" el `.htaccess` del panel | El `.htaccess` estaba correcto. Habría sido cambiar algo sano para tapar un problema ajeno. |
| Preocuparse por el 404 de `/4-ThimpsonExpressAppWeb/` | Ese 404 lo genera **el router interno de esa app**, no Apache (devuelve HTML propio, no el texto de MFI). Fuera de alcance y no lo causé yo. |

## 8. Riesgos y pendientes

| Riesgo | Estado |
|--------|--------|
| El MFI ahora se sirve por `localhost:8080/mfi/` (ruta por prefijo) en vez de raíz de puerto | **Consistente** con `BASE_URL='/mfi'` (`mfi/config/config.php:149`) y con `mfi/AGENTS.md`. Verificado en 200. |
| Si alguien entra a `mfi.local:8080` esperando DocumentRoot propio | Ya no resuelve a ese vhost. Se entra por `localhost:8080/mfi/`. |
| **La BD `thimpsonexpress` tiene 1 solo usuario** (`superadmin`), no los 29 que menciona `AGENTS.md` | **A revisar.** Puede ser una BD regenerada o un seed perdido. No lo toqué. |
| `DocumentRoot` inexistentes generates warnings en cada arranque: `server.ecorsa.app`, `www.ecorsa.com`, `demo_system` | Preexistente, inofensivo. Son vhosts de proyectos que no están en esta máquina. |
| Quedan pendientes en `AGENTS.md`: auditoría de usuarios (bloqueada sin tu OK) y rotar el PAT de GitHub | Sin cambios. |

## 9. Cómo reproducir

```powershell
# Si algún día vuelve a fallar con "Página no encontrada":
1. curl -s -D - http://localhost:8080/xampp/ | Select-Object -First 5
   → Si da 404, el problema es Apache, no el proyecto.
2. Select-String -Path C:\xampp\apache\conf\extra\httpd-vhosts.conf -Pattern "8080"
   → Si aparece un <VirtualHost *:8080> activo, ese es el culpable.
3. Restaurar: Copy-Item httpd-vhosts.conf.bak-20261009-pre-reparacion ... (solo para comparar)
```

---
**Fecha:** 2026-10-09 | **Lugar:** Ocotal, Nueva Segovia
**Alcance:** configuración de Apache. **Cero cambios de código en el repositorio del panel.**