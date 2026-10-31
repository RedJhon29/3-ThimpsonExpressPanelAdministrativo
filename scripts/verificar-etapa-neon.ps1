# ====================ENCABEZADO====================
# HERRAMIENTA: verificador de la etapa "base remota en Neon"
# ARCHIVO: scripts/verificar-etapa-neon.ps1
# ==================================================
# =====================DETALLES=====================
# QUÉ HACE: comprueba archivos, secretos, conexión PHP,
#     Apache y que local y Neon tengan los mismos datos.
# VINCULADO A: .env, Configuracion/entorno.php,
#     Configuracion/conexion.php y scripts/probar-conexion.php.
# SI SE ALTERA: deja de servir como compuerta previa al commit.
# LÍMITES: solo lee; nunca imprime la cadena ni la clave.
# FECHA: 2026-10-30 | LUGAR: Ocotal, Nueva Segovia
# ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
# ==================================================

$raiz = Split-Path $PSScriptRoot -Parent
# La salida de PHP viene en UTF-8; sin esto las tildes se ven rotas
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$php = Join-Path 'C:\xampp\php' 'php.exe'
if (-not (Test-Path $php)) { $php = 'php' }
$psql = 'C:\Program Files\PostgreSQL\16\bin\psql.exe'
$urlNeon = 'C:\Users\DELL\.neon-admin-url'
$panel = 'http://localhost:8080/3-ThimpsonExpressPanelAdministrativo'
$fallos = 0
$omitidas = 0

function Informar {
    param([string]$Estado, [string]$Nombre, [string]$Detalle = '')
    switch ($Estado) {
        'OK'    { Write-Host "[ OK  ] $Nombre" -ForegroundColor Green }
        'FALLA' { Write-Host "[FALLA] $Nombre" -ForegroundColor Red; $script:fallos++ }
        'OMITE' { Write-Host "[OMITE] $Nombre" -ForegroundColor Yellow; $script:omitidas++ }
    }
    if ($Detalle) { Write-Host "        $Detalle" -ForegroundColor DarkGray }
}

Write-Host "=== VERIFICACIÓN: base remota en Neon ===" -ForegroundColor Cyan
Write-Host "Proyecto: $raiz`n"

# --- 1. Archivos de la etapa
$archivos = @(
    '.env', '.env.example',
    'Configuracion\entorno.php', 'Configuracion\conexion.php',
    'scripts\probar-conexion.php', 'scripts\preparar-entorno.ps1'
)
$faltantes = @($archivos | Where-Object { -not (Test-Path (Join-Path $raiz $_)) })
if ($faltantes.Count -eq 0) {
    Informar 'OK' 'Archivos de la etapa presentes' "$($archivos.Count) archivos"
} else {
    Informar 'FALLA' 'Archivos de la etapa' "faltan: $($faltantes -join ', ')"
}

# --- 2. .env tiene DATABASE_URL (sin mostrar el valor)
$rutaEnv = Join-Path $raiz '.env'
if (Test-Path $rutaEnv) {
    $linea = Get-Content $rutaEnv | Where-Object { $_ -match '^\s*DATABASE_URL\s*=' } | Select-Object -First 1
    if ($linea) {
        $hostVisible = if ($linea -match '@([^/]+)/') { $matches[1] } else { 'host no reconocido' }
        Informar 'OK' 'DATABASE_URL definida en .env' "host: $hostVisible"
    } else {
        Informar 'FALLA' 'DATABASE_URL en .env' 'la variable no aparece'
    }
} else {
    Informar 'FALLA' '.env' 'no existe; correr scripts/preparar-entorno.ps1'
}

# --- 3. .env ignorado por git y no versionado
$ignorado = git -C $raiz check-ignore -q .env 2>$null
if ($LASTEXITCODE -eq 0) {
    $origen = git -C $raiz check-ignore -v .env 2>$null
    Informar 'OK' '.env ignorado por git' "$origen"
} else {
    Informar 'FALLA' '.env ignorado por git' 'NO está en .gitignore: riesgo de subir la clave'
}

git -C $raiz ls-files --error-unmatch .env > $null 2>&1
if ($LASTEXITCODE -eq 0) {
    Informar 'FALLA' '.env no versionado' 'ESTÁ en el índice de git: sacarlo antes de commitear'
} else {
    Informar 'OK' '.env no versionado' 'no figura en el índice de git'
}

git -C $raiz check-ignore -q .env.example 2>$null
if ($LASTEXITCODE -ne 0) {
    Informar 'OK' '.env.example sí se versiona' 'la plantilla viaja con el repo'
} else {
    Informar 'FALLA' '.env.example versionable' 'quedó ignorado; los clones no tendrían plantilla'
}

# --- 4. Conexión desde PHP a la base configurada
$salida = & $php (Join-Path $raiz 'scripts\probar-conexion.php') 2>&1 | Out-String
if ($salida -match 'Estado:\s+CONECTADO') {
    $servidor = if ($salida -match 'Servidor:\s+(\S+)') { $matches[1] } else { '?' }
    $filas    = if ($salida -match 'Filas:\s+(\d+)') { $matches[1] } else { '?' }
    $version  = if ($salida -match 'PostgreSQL\s+([\d.]+)') { "PostgreSQL $($matches[1])" } else { 'versión no leída' }
    Informar 'OK' 'PHP conecta a la base configurada' "$servidor | $version | $filas fila(s)"
    if ($servidor -notmatch 'neon\.tech') {
        Informar 'FALLA' 'La conexión apunta a Neon' "apunta a $servidor (¿falta .env?)"
    } else {
        Informar 'OK' 'La conexión apunta a Neon'
    }
} else {
    $detalle = ($salida -split "`n" | Where-Object { $_ -match 'Detalle:' }) -join ' '
    Informar 'FALLA' 'PHP conecta a la base configurada' $detalle
}

# --- 5. Apache sirve el panel
$codigo = & curl.exe -s -o NUL -w '%{http_code}' "$panel/login" 2>$null
if ($codigo.Trim() -eq '200') {
    Informar 'OK' 'Apache sirve /login' "HTTP $($codigo.Trim())"
} else {
    Informar 'FALLA' 'Apache sirve /login' "HTTP $($codigo.Trim()) (¿Apache arrancado en 8080?)"
}

# --- 6. Comparación de datos local vs Neon
if (-not (Test-Path $psql)) {
    Informar 'OMITE' 'Comparación local vs Neon' "no se encontró psql en $psql"
} elseif (-not (Test-Path $urlNeon)) {
    Informar 'OMITE' 'Comparación local vs Neon' "no se encontró $urlNeon"
} else {
    $cadena = (Get-Content $urlNeon -Raw).Trim()
    $consulta = "SELECT count(*)::text || '|' || coalesce(md5(string_agg(id_usuario::text || tipo_usuario || nick_name || clave_usuario || estado_usuario, ',' ORDER BY id_usuario)), '-') FROM usuarios;"

    $local = (& $psql -h 127.0.0.1 -U postgres -d thimpsonexpress -Atc $consulta 2>&1 | Out-String).Trim()
    $neon  = (& $psql -d $cadena -Atc $consulta 2>&1 | Out-String).Trim()

    if ($local -match '^error|no se pudo|FATAL' -or $neon -match '^error|no se pudo|FATAL') {
        Informar 'OMITE' 'Comparación local vs Neon' 'alguna de las dos bases no respondió'
    } elseif ($local -eq $neon) {
        Informar 'OK' 'Datos idénticos en local y Neon' "huella: $local"
    } else {
        Informar 'FALLA' 'Datos idénticos en local y Neon' "local=$local | neon=$neon"
    }

    $secLocal = (& $psql -h 127.0.0.1 -U postgres -d thimpsonexpress -Atc 'SELECT last_value FROM usuarios_id_usuario_seq;' 2>&1 | Out-String).Trim()
    $secNeon  = (& $psql -d $cadena -Atc 'SELECT last_value FROM usuarios_id_usuario_seq;' 2>&1 | Out-String).Trim()
    if ($secLocal -eq $secNeon) {
        Informar 'OK' 'Secuencia igual en ambas bases' "last_value: $secLocal"
    } else {
        Informar 'FALLA' 'Secuencia igual en ambas bases' "local=$secLocal | neon=$secNeon"
    }
}

# --- 7. Resultado
Write-Host "`n=== RESULTADO ===" -ForegroundColor Cyan
if ($fallos -eq 0) {
    Write-Host "TODO EN ORDEN: $($omitidas) omitida(s), 0 fallas." -ForegroundColor Green
    exit 0
} else {
    Write-Host "HAY $fallos FALLA(S) y $($omitidas) omitida(s)." -ForegroundColor Red
    exit 1
}
