param(
    [Parameter(Mandatory = $true)]
    [string]$CallbackUrl,

    [string]$EnvFile = "$PSScriptRoot\facebook-env.local.ps1",
    [string]$StateFile = "$PSScriptRoot\.facebook-oauth-state.json",
    [string]$AppSecret = $env:FACEBOOK_APP_SECRET,
    [switch]$Save
)

$ErrorActionPreference = "Stop"

if (Test-Path -LiteralPath $EnvFile) {
    . $EnvFile
}

if (-not (Test-Path -LiteralPath $StateFile)) {
    throw "OAuth state file not found. Run start-facebook-page-oauth.ps1 first."
}

if ([string]::IsNullOrWhiteSpace($AppSecret)) { $AppSecret = $env:FACEBOOK_APP_SECRET }
if ([string]::IsNullOrWhiteSpace($AppSecret)) { throw "Missing FACEBOOK_APP_SECRET." }

function UrlEncode {
    param([Parameter(Mandatory = $true)][string]$Value)
    return [System.Uri]::EscapeDataString($Value)
}

function Parse-QueryString {
    param([string]$Query)

    $result = @{}
    if ([string]::IsNullOrWhiteSpace($Query)) {
        return $result
    }

    foreach ($pair in $Query.TrimStart("?").Split("&")) {
        if ([string]::IsNullOrWhiteSpace($pair)) { continue }
        $parts = $pair.Split("=", 2)
        $key = [System.Uri]::UnescapeDataString($parts[0].Replace("+", " "))
        $value = if ($parts.Count -gt 1) { [System.Uri]::UnescapeDataString($parts[1].Replace("+", " ")) } else { "" }
        $result[$key] = $value
    }

    return $result
}

function Find-PageToken {
    param(
        [Parameter(Mandatory = $true)][string]$AccessToken,
        [Parameter(Mandatory = $true)][string]$PageId,
        [Parameter(Mandatory = $true)][string]$GraphVersion
    )

    $url = "https://graph.facebook.com/$GraphVersion/me/accounts?fields=id,name,access_token&access_token=$(UrlEncode $AccessToken)"

    while (-not [string]::IsNullOrWhiteSpace($url)) {
        $response = Invoke-RestMethod -Method Get -Uri $url
        $page = $response.data | Where-Object { $_.id -eq $PageId } | Select-Object -First 1
        if ($null -ne $page) {
            return $page
        }

        $url = if ($null -ne $response.paging -and -not [string]::IsNullOrWhiteSpace($response.paging.next)) {
            $response.paging.next
        }
        else {
            $null
        }
    }

    throw "Page ID $PageId was not returned by /me/accounts. Check page access and OAuth permissions."
}

function Set-EnvFileValue {
    param(
        [Parameter(Mandatory = $true)][string]$Path,
        [Parameter(Mandatory = $true)][string]$Name,
        [Parameter(Mandatory = $true)][string]$Value
    )

    $escapedValue = $Value.Replace("`"", "\`"")
    $assignment = "`$env:$Name = `"$escapedValue`""

    $content = if (Test-Path -LiteralPath $Path) {
        Get-Content -LiteralPath $Path -Raw -Encoding UTF8
    }
    else {
        ""
    }

    $pattern = "(?m)^\s*\`$env:$([regex]::Escape($Name))\s*=\s*`".*?`"\s*$"
    if ([regex]::IsMatch($content, $pattern)) {
        $content = [regex]::Replace($content, $pattern, $assignment)
    }
    else {
        if (-not $content.EndsWith("`n") -and $content.Length -gt 0) { $content += "`n" }
        $content += "$assignment`n"
    }

    Set-Content -LiteralPath $Path -Value $content -Encoding UTF8
}

$stateData = Get-Content -LiteralPath $StateFile -Raw -Encoding UTF8 | ConvertFrom-Json
$callback = [uri]$CallbackUrl
$values = Parse-QueryString $callback.Query

if ($values.ContainsKey("error")) {
    throw "Facebook authorization failed: $($values["error"]) $($values["error_description"])"
}

if (-not $values.ContainsKey("state") -or $values["state"] -ne $stateData.state) {
    throw "OAuth state mismatch. Run start-facebook-page-oauth.ps1 again and use the matching callback URL."
}

if (-not $values.ContainsKey("code")) {
    throw "Callback URL does not contain an OAuth code."
}

$shortTokenUrl = "https://graph.facebook.com/$($stateData.graphVersion)/oauth/access_token?client_id=$(UrlEncode $stateData.appId)&redirect_uri=$(UrlEncode $stateData.redirectUri)&client_secret=$(UrlEncode $AppSecret)&code=$(UrlEncode $values["code"])"
$shortTokenResponse = Invoke-RestMethod -Method Get -Uri $shortTokenUrl

$longTokenUrl = "https://graph.facebook.com/$($stateData.graphVersion)/oauth/access_token?grant_type=fb_exchange_token&client_id=$(UrlEncode $stateData.appId)&client_secret=$(UrlEncode $AppSecret)&fb_exchange_token=$(UrlEncode $shortTokenResponse.access_token)"
$longTokenResponse = Invoke-RestMethod -Method Get -Uri $longTokenUrl

$page = Find-PageToken -AccessToken $longTokenResponse.access_token -PageId $stateData.pageId -GraphVersion $stateData.graphVersion

$env:FACEBOOK_PAGE_ACCESS_TOKEN = $page.access_token
$env:FACEBOOK_GRAPH_API_VERSION = $stateData.graphVersion

if ($Save.IsPresent) {
    Set-EnvFileValue -Path $EnvFile -Name "FACEBOOK_PAGE_ACCESS_TOKEN" -Value $page.access_token
    Set-EnvFileValue -Path $EnvFile -Name "FACEBOOK_GRAPH_API_VERSION" -Value $stateData.graphVersion
    Set-EnvFileValue -Path $EnvFile -Name "FACEBOOK_TOKEN_UPDATED_AT" -Value (Get-Date).ToString("o")
}

Write-Host "Connected Facebook page: $($page.name) ($($page.id))"
if ($Save.IsPresent) {
    Write-Host "Saved page access token to $EnvFile"
}
