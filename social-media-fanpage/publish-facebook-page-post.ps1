param(
    [Parameter(Mandatory = $true)]
    [string]$MessageFile,

    [string]$Link,

    [string]$FirstCommentFile,

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

$feedEndpoint = "https://graph.facebook.com/$graphVersion/$pageId/feed"
$body = @{
    message = $message.Trim()
    access_token = $pageAccessToken
}

if (-not [string]::IsNullOrWhiteSpace($Link)) {
    $body.link = $Link
}

$timestamp = (Get-Date).ToString("o")

if ($shouldDryRun) {
    Write-JsonLog @{
        timestamp = $timestamp
        status = "dry_run"
        endpoint = $feedEndpoint
        messageFile = (Resolve-Path -LiteralPath $MessageFile).Path
        hasLink = -not [string]::IsNullOrWhiteSpace($Link)
        hasFirstComment = -not [string]::IsNullOrWhiteSpace($FirstCommentFile)
        message = $message.Trim()
    }

    Write-Host "DRY RUN: would publish to $feedEndpoint"
    exit 0
}

try {
    $postResponse = Invoke-RestMethod -Method Post -Uri $feedEndpoint -Body $body
    $postId = $postResponse.id

    $commentResponse = $null
    if (-not [string]::IsNullOrWhiteSpace($FirstCommentFile)) {
        if (-not (Test-Path -LiteralPath $FirstCommentFile)) {
            throw "First comment file not found: $FirstCommentFile"
        }

        $firstComment = Get-Content -LiteralPath $FirstCommentFile -Raw -Encoding UTF8
        if (-not [string]::IsNullOrWhiteSpace($firstComment)) {
            $commentEndpoint = "https://graph.facebook.com/$graphVersion/$postId/comments"
            $commentBody = @{
                message = $firstComment.Trim()
                access_token = $pageAccessToken
            }
            $commentResponse = Invoke-RestMethod -Method Post -Uri $commentEndpoint -Body $commentBody
        }
    }

    Write-JsonLog @{
        timestamp = $timestamp
        status = "published"
        endpoint = $feedEndpoint
        postId = $postId
        commentId = if ($null -ne $commentResponse) { $commentResponse.id } else { $null }
        messageFile = (Resolve-Path -LiteralPath $MessageFile).Path
        message = $message.Trim()
    }

    Write-Host "Published Facebook post: $postId"
}
catch {
    Write-JsonLog @{
        timestamp = $timestamp
        status = "failed"
        endpoint = $feedEndpoint
        messageFile = (Resolve-Path -LiteralPath $MessageFile).Path
        error = $_.Exception.Message
    }

    throw
}
