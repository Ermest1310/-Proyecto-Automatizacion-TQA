$ErrorActionPreference = 'Stop'
$qaRoot = Split-Path -Parent $PSScriptRoot
$qaFile = Join-Path $qaRoot '.env.local'
$qaSecret = Read-Host 'Pega tu API key de Google AI Studio (no se mostrara)' -AsSecureString
$qaPointer = [IntPtr]::Zero
try {
    $qaPointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($qaSecret)
    $qaKey = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($qaPointer).Trim()
    if ($qaKey -notmatch '^[A-Za-z0-9_.-]{20,300}$') { throw 'Formato no valido; no se guardaron cambios.' }
    $qaLines = if (Test-Path -LiteralPath $qaFile) { [IO.File]::ReadAllLines($qaFile) } else { @() }
    $qaLines = @($qaLines | Where-Object { $_ -notmatch '^\s*(?:export\s+)?(?:GEMINI_API_KEY|QA_AI_PROVIDER|QA_AI_ENABLED|QA_GEMINI_MODEL)\s*=' })
    $qaLines += @('QA_AI_PROVIDER=gemini', 'QA_AI_ENABLED=true', 'QA_GEMINI_MODEL=gemini-3.5-flash-lite', "GEMINI_API_KEY=$qaKey")
    [IO.File]::WriteAllLines($qaFile, $qaLines, [Text.UTF8Encoding]::new($false))
    Write-Host 'Clave guardada localmente. Ejecuta npm run qa:ai:check y reinicia el backend QA.'
} finally {
    if ($qaPointer -ne [IntPtr]::Zero) { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($qaPointer) }
    $qaKey = $null
    $qaLines = $null
    $qaSecret.Dispose()
}
