# Read AI Config from .env
$envContent = Get-Content .env
$apiKeyLine = $envContent | Where-Object { $_ -match "^AI_API_KEY=" }
$apiKey = ($apiKeyLine -replace "AI_API_KEY=", "").Trim().Trim('"').Trim("'")

$baseUrlLine = $envContent | Where-Object { $_ -match "^AI_BASE_URL=" }
$baseUrl = ($baseUrlLine -replace "AI_BASE_URL=", "").Trim().Trim('"').Trim("'").TrimEnd('/')

$modelLine = $envContent | Where-Object { $_ -match "^AI_MODEL=" }
$model = ($modelLine -replace "AI_MODEL=", "").Trim().Trim('"').Trim("'")

$url = "$baseUrl/v1/chat/completions"

Write-Host "--- Configuration ---"
Write-Host "URL: $url"
Write-Host "Model: $model"
Write-Host "Key Length: $($apiKey.Length)"

$body = @{
    model = $model
    messages = @(
        @{
            role = "user"
            content = "Hello, are you ready?"
        }
    )
    temperature = 0.7
    max_tokens = 100
} | ConvertTo-Json -Depth 5

Write-Host "`n--- Sending Request ---"
# Write-Host $body

try {
    $response = Invoke-RestMethod -Uri $url -Method Post -Body $body -ContentType "application/json" -Headers @{ "Authorization" = "Bearer $apiKey" }
    Write-Host "`nSuccess!"
    Write-Host "Response: $($response.choices[0].message.content)"
} catch {
    Write-Host "`nError Code: $($_.Exception.Message)"
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $errorBody = $reader.ReadToEnd()
        Write-Host "Error Body: $errorBody"
    }
}
