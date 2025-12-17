# Read API Key from .env
$envContent = Get-Content .env
$apiKeyLine = $envContent | Where-Object { $_ -match "^GEMINI_API_KEY=" }
$apiKey = $apiKeyLine -replace "GEMINI_API_KEY=", ""
$apiKey = $apiKey.Trim()

Write-Host "Testing Gemini API with Key: $apiKey"

# 1. List Models
$listUrl = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey"
Write-Host "`n--- Listing Models ---"
try {
    $models = Invoke-RestMethod -Uri $listUrl -Method Get
    $models.models | ForEach-Object { Write-Host $_.name }
}
catch {
    Write-Host "Error Listing Models:"
    Write-Host $_.Exception.Message
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        Write-Host $reader.ReadToEnd()
    }
}

# 2. Test Generation with gemini-2.0-flash-exp
$model = "gemini-2.0-flash-exp"
$genUrl = "https://generativelanguage.googleapis.com/v1beta/models/$($model):generateContent?key=$apiKey"
$body = @{
    contents = @(
        @{
            parts = @(
                @{ text = "Hello, can you hear me?" }
            )
        }
    )
} | ConvertTo-Json -Depth 5

Write-Host "`n--- Testing Generation ($model) ---"
try {
    $response = Invoke-RestMethod -Uri $genUrl -Method Post -Body $body -ContentType "application/json"
    Write-Host "Success!"
    Write-Host $response.candidates[0].content.parts[0].text
}
catch {
    Write-Host "Error Generating Content:"
    Write-Host $_.Exception.Message
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        Write-Host $reader.ReadToEnd()
    }
}
