<#
.SYNOPSIS
    Verifica la higiene del proyecto: lint, rutas, includes y archivos huérfanos.
.DESCRIPTION
    Recorre el proyecto y reporta problemas que violan la regla de higiene
    definida en AGENTS.md. Devuelve código de salida 1 si encuentra algo.
#>

$ErrorActionPreference = 'Stop'
$raiz = Split-Path -Parent $PSScriptRoot
$problemas = @()

Write-Host "=== HIGIENE DEL PROYECTO ===" -ForegroundColor Cyan

# 1. Lint de todos los .php
Write-Host "`n[1/5] Lint PHP..." -ForegroundColor Yellow
$archivosPhp = Get-ChildItem -Path $raiz -Recurse -Filter *.php | Where-Object { $_.FullName -notmatch '\\vendor\\' }
foreach ($archivo in $archivosPhp) {
    $salida = php -l $archivo.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        $problemas += "LINT: $($archivo.FullName) -> $salida"
    }
}
Write-Host "  $($archivosPhp.Count) archivos revisados."

# 2. Rutas muertas: cada entrada de $routes debe apuntar a un controlador y método existentes
Write-Host "`n[2/5] Rutas muertas..." -ForegroundColor Yellow
$indexPhp = Get-Content "$raiz\index.php" -Raw
$rutas = [regex]::Matches($indexPhp, "'([^']+)'\s*=>\s*\['([^']+)',\s*'([^']+)'\]")
foreach ($ruta in $rutas) {
    $controlador = $ruta.Groups[2].Value
    $metodo = $ruta.Groups[3].Value
    $archivoControlador = "$raiz\Controladores\$controlador.php"
    if (-not (Test-Path $archivoControlador)) {
        $problemas += "RUTA MUERTA: '$($ruta.Groups[1].Value)' -> controlador '$controlador' no existe"
        continue
    }
    $contenido = Get-Content $archivoControlador -Raw
    if ($contenido -notmatch "function\s+$metodo\s*\(") {
        $problemas += "RUTA MUERTA: '$($ruta.Groups[1].Value)' -> método '$metodo' no existe en $controlador"
    }
}
Write-Host "  $($rutas.Count) rutas revisadas."

# 3. Includes rotos: todo include VIEW_PATH . '/...' debe resolver a un archivo existente
Write-Host "`n[3/5] Includes rotos..." -ForegroundColor Yellow
$includes = [regex]::Matches((Get-ChildItem -Path $raiz -Recurse -Filter *.php | Get-Content -Raw), "include\s+VIEW_PATH\s*\.\s*'([^']+)'")
foreach ($inc in $includes) {
    $ruta = $inc.Groups[1].Value -replace '/', '\'
    $archivo = "$raiz\Vistas$ruta"
    if (-not (Test-Path $archivo)) {
        $problemas += "INCLUDE ROTO: Vistas$ruta no existe"
    }
}
Write-Host "  $($includes.Count) includes revisados."

# 4. Archivos huérfanos en Vistas/, Modelos/ y Controladores/
Write-Host "`n[4/5] Archivos huérfanos..." -ForegroundColor Yellow
$todosLosPhp = Get-ChildItem -Path $raiz -Recurse -Filter *.php | Where-Object { $_.FullName -notmatch '\\vendor\\' }
$contenidoTotal = ($todosLosPhp | Get-Content -Raw) -join "`n"
$carpetas = @('Vistas', 'Modelos', 'Controladores')
foreach ($carpeta in $carpetas) {
    $archivos = Get-ChildItem -Path "$raiz\$carpeta" -Recurse -Filter *.php
    foreach ($archivo in $archivos) {
        $nombre = $archivo.BaseName
        # Un archivo es huérfano si su nombre no aparece en ningún otro archivo
        $referencias = ($contenidoTotal | Select-String -Pattern ([regex]::Escape($nombre)) -AllMatches).Matches.Count
        if ($referencias -le 1) {
            $problemas += "HUÉRFANO: $($archivo.FullName) no se referencia en ningún otro archivo"
        }
    }
}
Write-Host "  $($carpetas.Count) carpetas revisadas."

# 5. Llamadas internas a metodos inexistentes ($this->metodo())
Write-Host "`n[5/5] Llamadas internas sin método..." -ForegroundColor Yellow
$controladores = Get-ChildItem -Path "$raiz\Controladores" -Filter *.php
foreach ($controlador in $controladores) {
    $codigo = Get-Content $controlador.FullName -Raw

    # Metodos que el archivo define (publicos, privados y protected)
    $definidos = @([regex]::Matches($codigo, 'function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(') |
        ForEach-Object { $_.Groups[1].Value })

    # Metodos heredados de una clase padre: no se pueden resolver aqui
    if ($codigo -match 'class\s+\w+\s+extends\s+') {
        Write-Host "  $($controlador.Name): extiende otra clase, se omite."
        continue
    }

# Llamadas del tipo $this->metodo( y self::metodo(
    $llamadas = @([regex]::Matches($codigo, '(?:\$this\s*->|self\s*::)\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\(') |
        ForEach-Object { $_.Groups[1].Value })

    # Metodos que ofrece PHP y los propios, se excluyen del chequeo
    $delPHP = @('die','exit','isset','empty','unset','print','echo','require','include','array','list','eval')

    foreach ($llamada in ($llamadas | Sort-Object -Unique)) {
        if ($delPHP -contains $llamada) { continue }
        if ($definidos -notcontains $llamada) {
            $linea = (Select-String -Path $controlador.FullName -Pattern ([regex]::Escape($llamada)) -SimpleMatch |
                Select-Object -First 1).LineNumber
            $problemas += "MÉTODO INEXISTENTE: $($controlador.Name) llama $llamada() pero no lo define (cerca de la línea $linea)"
        }
    }
}
Write-Host "  $($controladores.Count) controlador(es) revisados."

# Resultado
Write-Host "`n=== RESULTADO ===" -ForegroundColor Cyan
if ($problemas.Count -eq 0) {
    Write-Host "Sin problemas de higiene." -ForegroundColor Green
    exit 0
} else {
    Write-Host "Se encontraron $($problemas.Count) problema(s):" -ForegroundColor Red
    foreach ($p in $problemas) {
        Write-Host "  - $p" -ForegroundColor Red
    }
    exit 1
}
