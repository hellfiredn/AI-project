param(
    [string]$EnvFile = "$PSScriptRoot\facebook-env.local.ps1",
    [string]$StateFile = "$PSScriptRoot\.facebook-oauth-state.json",
    [string]$AppId = $env:FACEBOOK_APP_ID,
    [string]$PageId = $env:FACEBOOK_PAGE_ID,
    [string]$GraphVersion = $env:FACEBOOK_GRAPH_API_VERSION,
    [string]$RedirectUri = $env:FACEBOOK_REDIRECT_URI,
    [string[]]$Scopes = @("pages_show_list", "pages_manage_posts", "pages_read_engagement")
)

$ErrorActionPreference = "Stop"

if (Test-Path -LiteralPath $EnvFile) {
    . $EnvFile
}

if ([string]::IsNullOrWhiteSpace($AppId)) { $AppId = $env:FACEBOOK_APP_ID }
if ([string]::IsNullOrWhiteSpace($PageId)) { $PageId = $env:FACEBOOK_PAGE_ID }
if ([string]::IsNullOrWhiteSpace($GraphVersion)) { $GraphVersion = $env:FACEBOOK_GRAPH_API_VERSION }
if ([string]::IsNullOrWhiteSpace($GraphVersion)) { $GraphVersion = "v24.0" }
if ([string]::IsNullOrWhiteSpace($RedirectUri)) { $RedirectUri = $env:FACEBOOK_REDIRECT_URI }

if ([string]::IsNullOrWhiteSpace($AppId)) { throw "Missing FACEBOOK_APP_ID." }
if ([string]::IsNullOrWhiteSpace($PageId)) { throw "Missing FACEBOOK_PAGE_ID." }
if ([string]::IsNullOrWhiteSpace($RedirectUri)) { throw "Missing FACEBOOK_REDIRECT_URI." }

function UrlEncode {
    param([Parameter(Mandatory = $true)][string]$Value)
    return [System.Uri]::EscapeDataString($Value)
}

$state = [guid]::NewGuid().ToString("N")
$scopeText = [string]::Join(",", $Scopes)
$loginUrl = "https://www.facebook.com/$GraphVersion/dialog/oauth?client_id=$(UrlEncode $AppId)&redirect_uri=$(UrlEncode $RedirectUri)&state=$(UrlEncode $state)&scope=$(UrlEncode $scopeText)&response_type=code"

$stateData = [ordered]@{
    state = $state
    appId = $AppId
    pageId = $PageId
    graphVersion = $GraphVersion
    redirectUri = $RedirectUri
    scopes = $Scopes
    createdAt = (Get-Date).ToString("o")
}

$stateData | ConvertTo-Json -Depth 5 | Set-Content -LiteralPath $StateFile -Encoding UTF8

Write-Host "Facebook login URL:"
Write-Host $loginUrl
Write-Host ""
Write-Host "Open the URL, approve permissions, then copy the full final URL from the browser address bar."
Write-Host "OAuth state saved to $StateFile"

Start-Process $loginUrl
