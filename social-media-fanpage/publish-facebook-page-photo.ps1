param(
    [Parameter(Mandatory = $true)]
    [string]$ImageFile,

    [Parameter(Mandatory = $true)]
    [string]$MessageFile,

    [string]$LogPath = "$PSScriptRoot\logs\facebook-posts.ndjson",

    [switch]$DryRun
)

$ErrorActionPreference = "Stop"

function Get-RequiredEnv {
    param([Parameter(Mandatory = $true)][string]$Name)

    $value = [Environment]::GetEnvironmentVariable($Name, "Process")
    if ([string]::IsNullOrWhiteSpace($value)) {
        throw "Missing required environment variable: $Name"
    }

    return $value
}

function Write-JsonLog {
    param(
        [Parameter(Mandatory = $true)]
        [hashtable]$Entry
    )

    $logDir = Split-Path -Parent $LogPath
    if (-not [string]::IsNullOrWhiteSpace($logDir)) {
        New-Item -ItemType Directory -Force -Path $logDir | Out-Null
    }

    ($Entry | ConvertTo-Json -Compress -Depth 8) + "`n" | Add-Content -Path $LogPath -Encoding UTF8
}

if (-not (Test-Path -LiteralPath $ImageFile)) {
    throw "Image file not found: $ImageFile"
}

if (-not (Test-Path -LiteralPath $MessageFile)) {
    throw "Message file not found: $MessageFile"
}

$message = Get-Content -LiteralPath $MessageFile -Raw -Encoding UTF8
if ([string]::IsNullOrWhiteSpace($message)) {
    throw "Message file is empty: $MessageFile"
}

$pageId = Get-RequiredEnv "FACEBOOK_PAGE_ID"
$pageAccessToken = Get-RequiredEnv "FACEBOOK_PAGE_ACCESS_TOKEN"
$graphVersion = [Environment]::GetEnvironmentVariable("FACEBOOK_GRAPH_API_VERSION", "Process")
if ([string]::IsNullOrWhiteSpace($graphVersion)) {
    $graphVersion = "v24.0"
}

$envDryRun = [Environment]::GetEnvironmentVariable("FACEBOOK_DRY_RUN", "Process")
$shouldDryRun = $DryRun.IsPresent -or $envDryRun -eq "1" -or $envDryRun -eq "true"

$photoEndpoint = "https://graph.facebook.com/$graphVersion/$pageId/photos"
$timestamp = (Get-Date).ToString("o")
$resolvedImage = (Resolve-Path -LiteralPath $ImageFile).Path
$resolvedMessage = (Resolve-Path -LiteralPath $MessageFile).Path

if ($shouldDryRun) {
    Write-JsonLog @{
        timestamp = $timestamp
        status = "dry_run_photo"
        endpoint = $photoEndpoint
        imageFile = $resolvedImage
        messageFile = $resolvedMessage
        message = $message.Trim()
    }

    Write-Host "DRY RUN: would publish photo to $photoEndpoint"
    exit 0
}

Add-Type -AssemblyName System.Net.Http

$client = [System.Net.Http.HttpClient]::new()
$form = [System.Net.Http.MultipartFormDataContent]::new()
$stream = $null

try {
    $form.Add([System.Net.Http.StringContent]::new($message.Trim(), [System.Text.Encoding]::UTF8), "message")
    $form.Add([System.Net.Http.StringContent]::new($pageAccessToken), "access_token")
    $form.Add([System.Net.Http.StringContent]::new("true"), "published")

    $stream = [System.IO.File]::OpenRead($resolvedImage)
    $fileContent = [System.Net.Http.StreamContent]::new($stream)
    $fileContent.Headers.ContentType = [System.Net.Http.Headers.MediaTypeHeaderValue]::Parse("image/png")
    $form.Add($fileContent, "source", [System.IO.Path]::GetFileName($resolvedImage))

    $response = $client.PostAsync($photoEndpoint, $form).GetAwaiter().GetResult()
    $body = $response.Content.ReadAsStringAsync().GetAwaiter().GetResult()

    if (-not $response.IsSuccessStatusCode) {
        throw "Facebook photo publish failed ($([int]$response.StatusCode)): $body"
    }

    $photoResponse = $body | ConvertFrom-Json
    $postId = if ($photoResponse.PSObject.Properties.Name -contains "post_id") { $photoResponse.post_id } else { $null }

    Write-JsonLog @{
        timestamp = $timestamp
        status = "published_photo"
        endpoint = $photoEndpoint
        photoId = $photoResponse.id
        postId = $postId
        imageFile = $resolvedImage
        messageFile = $resolvedMessage
        message = $message.Trim()
    }

    Write-Host "Published Facebook photo: $($photoResponse.id)"
    if ($null -ne $postId) {
        Write-Host "Published Facebook post: $postId"
    }
}
catch {
    Write-JsonLog @{
        timestamp = $timestamp
        status = "failed_photo"
        endpoint = $photoEndpoint
        imageFile = $resolvedImage
        messageFile = $resolvedMessage
        error = $_.Exception.Message
    }

    throw
}
finally {
    if ($null -ne $stream) { $stream.Dispose() }
    if ($null -ne $form) { $form.Dispose() }
    if ($null -ne $client) { $client.Dispose() }
}
