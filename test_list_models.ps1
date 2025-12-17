$envContent = Get-Content .env
$apiKeyLine = $envContent | Where-Object { $_ -match "^GEMINI_API_KEY=" }
$apiKey = $apiKeyLine -replace "GEMINI_API_KEY=", ""
$apiKey = $apiKey.Trim()

Write-Host "Listing Models for Key: $apiKey"
$listUrl = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey"

try {
    $models = Invoke-RestMethod -Uri $listUrl -Method Get
    $models.models | Where-Object { $_.name -match "flash|pro" } | ForEach-Object { Write-Output $_.name }
} catch {
    Write-Output "Error:"
    Write-Output $_.Exception.Message
}
