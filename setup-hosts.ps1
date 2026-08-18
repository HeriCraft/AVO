# Définition des variables
$HostsFile = "$env:windir\System32\drivers\etc\hosts"
$IpAddress = "127.0.0.1"
$Domains = @("app.avo.local", "api.avo.local", "inspector.avo.local")

Write-Host "Verification des privileges Administrateur..." -ForegroundColor Yellow

# Vérification de l'élévation des privilèges
$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
$principal = [Security.Principal.WindowsPrincipal]$identity
$isAdmin = $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "Erreur : Ce script necessite des droits Administrateur pour modifier le fichier hosts." -ForegroundColor Red
    Write-Host "Tentative de relance automatique du script en mode Administrateur..." -ForegroundColor Yellow
    
    # Récupère le chemin absolu du script
    $scriptPath = $MyInvocation.MyCommand.Path
    
    # Relance powershell en mode Admin avec le script actuel
    Start-Process powershell.exe -ArgumentList "-NoProfile -ExecutionPolicy Bypass -File `"$scriptPath`"" -Verb RunAs
    exit
}

Write-Host "Privileges accordes. Mise a jour de $HostsFile..." -ForegroundColor Yellow

$HostsContent = [System.IO.File]::ReadAllText($HostsFile)
$AddedCount = 0

foreach ($Domain in $Domains) {
    $Regex = "(?m)^\s*$IpAddress\s+$Domain"
    $Match = $HostsContent -match $Regex
    
    if ($Match) {
        Write-Host "[OK] Le domaine $Domain est deja configure." -ForegroundColor Green
    } else {
        [System.IO.File]::AppendAllText($HostsFile, "`r`n$IpAddress`t$Domain")
        Write-Host "[+] Le domaine $Domain a ete ajoute." -ForegroundColor Cyan
        $AddedCount++
    }
}

Write-Host "`nVidage du cache DNS systeme (ipconfig /flushdns)..." -ForegroundColor Yellow
ipconfig /flushdns | Out-Null
Write-Host "[OK] Cache DNS vide avec succes." -ForegroundColor Green

Write-Host ""
if ($AddedCount -gt 0) {
    Write-Host "[DONE] Operation terminee ! $AddedCount domaine(s) ajoute(s)." -ForegroundColor Green
} else {
    Write-Host "[DONE] Operation terminee ! Aucune modification necessaire, vos DNS locaux sont deja a jour." -ForegroundColor Green
}

# Garder la fenetre ouverte 5 secondes pour lire le resultat si relance via Start-Process
Start-Sleep -Seconds 5
