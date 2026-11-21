$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $projectRoot '.env.local'
if (-not (Test-Path -LiteralPath $envFile)) { throw '.env.local no existe; copia .env.example y completa la configuración QA primero.' }

$secure = Read-Host 'Contraseña de aplicación de Google (16 caracteres; no uses la contraseña normal)' -AsSecureString
$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
try {
    $password = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer) -replace '\s', ''
} finally {
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
    $secure.Dispose()
}
if ($password.Length -ne 16) { throw 'Google debe generar una contraseña de aplicación de 16 caracteres. No se cambió .env.local.' }

$settings = [ordered]@{
    QA_MAIL_MODE = 'smtp'
    QA_SMTP_HOST = 'smtp.gmail.com'
    QA_SMTP_PORT = '587'
    QA_SMTP_TLS = 'starttls'
    QA_SMTP_USER = 'asborisernesto@gmail.com'
    QA_SMTP_PASSWORD = $password
    QA_MAIL_FROM = 'asborisernesto@gmail.com'
}
$lines = [System.Collections.Generic.List[string]]::new()
[System.IO.File]::ReadAllLines($envFile) | ForEach-Object { $lines.Add($_) }
foreach ($key in $settings.Keys) {
    $found = 0
    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match ('^\s*' + [regex]::Escape($key) + '\s*=')) {
            $lines[$i] = "$key=$($settings[$key])"
            $found++
        }
    }
    if ($found -gt 1) { throw "Clave duplicada en .env.local: $key. No se guardó la configuración." }
    if ($found -eq 0) { $lines.Add("$key=$($settings[$key])") }
}
[System.IO.File]::WriteAllText($envFile, ($lines -join "`r`n") + "`r`n", [System.Text.UTF8Encoding]::new($false))
$password = $null
Write-Host 'Gmail SMTP quedó configurado en .env.local. La contraseña no se mostró.'
Write-Host 'Reinicia el stack con npm run qa:stop y npm run qa:start.'
Write-Host 'No se envió ningún correo.'
