# ====================ENCABEZADO====================
# HERRAMIENTA: preparación del entorno local
# ARCHIVO: scripts/preparar-entorno.ps1
# ==================================================
# =====================DETALLES=====================
# QUÉ HACE: crea .env desde .env.example si falta y
#     luego prueba la conexión a la base configurada.
# VINCULADO A: Configuracion/conexion.php y .env.example.
# SI SE ALTERA: un clon nuevo queda sin guía de arranque.
# LÍMITES: no guarda secretos ni descarga credenciales.
# FECHA: 2026-10-30 | LUGAR: Ocotal, Nueva Segovia
# ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
# ==================================================

$raiz = Split-Path $PSScriptRoot -Parent
$archivoEnv = Join-Path $raiz '.env'
$archivoEjemplo = Join-Path $raiz '.env.example'

if (-not (Test-Path $archivoEnv)) {
    Copy-Item $archivoEjemplo $archivoEnv
    Write-Host "Se creó .env a partir de .env.example."
    Write-Host "Abrilo y pegá la cadena real de DATABASE_URL antes de continuar."
    Write-Host "El archivo no se versiona: está en .gitignore."
    exit 1
}

$php = Join-Path 'C:\xampp\php' 'php.exe'
if (-not (Test-Path $php)) {
    $php = 'php'
}

& $php (Join-Path $PSScriptRoot 'probar-conexion.php')
