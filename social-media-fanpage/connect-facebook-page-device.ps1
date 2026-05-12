param(
    [string]$EnvFile = "$PSScriptRoot\facebook-env.local.ps1",
    [string]$AppId = $env:FACEBOOK_APP_ID,
    [string]$AppSecret = $env:FACEBOOK_APP_SECRET,
    [string]$ClientToken = $env:FACEBOOK_CLIENT_TOKEN,
    [string]$PageId = $env:FACEBOOK_PAGE_ID,
    [string]$GraphVersion = $env:FACEBOOK_GRAPH_API_VERSION,
    [string[]]$Scopes = @("pages_show_list", "pages_manage_posts", "pages_read_engagement"),
    [switch]$Save
)

$ErrorActionPreference = "Stop"

if (Test-Path -LiteralPath $EnvFile) {
    . $EnvFile
}

if ([string]::IsNullOrWhiteSpace($AppId)) { $AppId = $env:FACEBOOK_APP_ID }
if ([string]::IsNullOrWhiteSpace($AppSecret)) { $AppSecret = $env:FACEBOOK_APP_SECRET }
if ([string]::IsNullOrWhiteSpace($ClientToken)) { $ClientToken = $env:FACEBOOK_CLIENT_TOKEN }
if ([string]::IsNullOrWhiteSpace($PageId)) { $PageId = $env:FACEBOOK_PAGE_ID }
if ([string]::IsNullOrWhiteSpace($GraphVersion)) { $GraphVersion = $env:FACEBOOK_GRAPH_API_VERSION }
if ([string]::IsNullOrWhiteSpace($GraphVersion)) { $GraphVersion = "v24.0" }

function Assert-Value {
    param(
        [Parameter(Mandatory = $true)][string]$Name,
        [string]$Value
    )

    if ([string]::IsNullOrWhiteSpace($Value)) {
        throw "Missing $Name. Add it to $EnvFile or pass -$Name."
    }
}

function UrlEncode {
    param([Parameter(Mandatory = $true)][string]$Value)
    return [System.Uri]::EscapeDataString($Value)
}

function Invoke-FacebookPost {
    param(
        [Parameter(Mandatory = $true)][string]$Uri,
        [Parameter(Mandatory = $true)][hashtable]$Body
    )

    try {
        return Invoke-RestMethod -Method Post -Uri $Uri -Body $Body
    }
    catch {
        $response = $_.Exception.Response
        if ($null -eq $response) { throw }

        $stream = $response.GetResponseStream()
        if ($null -eq $stream) { throw }

        $reader = [System.IO.StreamReader]::new($stream)
        try {
            $raw = $reader.ReadToEnd()
            if ([string]::IsNullOrWhiteSpace($raw)) { throw }
            return $raw | ConvertFrom-Json
        }
        finally {
            $reader.Dispose()
        }
    }
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

    throw "Page ID $PageId was not returned by /me/accounts. Check page access and permissions."
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

Assert-Value "AppId" $AppId
Assert-Value "AppSecret" $AppSecret
Assert-Value "ClientToken" $ClientToken
Assert-Value "PageId" $PageId

$clientAccessToken = "$AppId|$ClientToken"
$scopeText = [string]::Join(",", $Scopes)

$deviceResponse = Invoke-FacebookPost `
    -Uri "https://graph.facebook.com/$GraphVersion/device/login" `
    -Body @{
        access_token = $clientAccessToken
        scope = $scopeText
        type = "device_code"
    }

if ($null -ne $deviceResponse.error) {
    throw "Facebook device login failed: $($deviceResponse.error.message)"
}

$verificationUri = $deviceResponse.verification_uri
if ([string]::IsNullOrWhiteSpace($verificationUri)) {
    $verificationUri = "https://www.facebook.com/device"
}

Write-Host "Open this URL and enter the code:"
Write-Host $verificationUri
Write-Host ""
Write-Host "Code: $($deviceResponse.user_code)"
Write-Host ""
Write-Host "Waiting for Facebook approval..."

Start-Process $verificationUri

$interval = if ($null -ne $deviceResponse.interval) { [int]$deviceResponse.interval } else { 5 }
$expiresAt = (Get-Date).AddSeconds([int]$deviceResponse.expires_in)
$userAccessToken = $null

while ((Get-Date) -lt $expiresAt) {
    Start-Sleep -Seconds $interval

    $statusResponse = Invoke-FacebookPost `
        -Uri "https://graph.facebook.com/$GraphVersion/device/login_status" `
        -Body @{
            access_token = $clientAccessToken
            code = $deviceResponse.code
        }

    if ($null -ne $statusResponse.access_token) {
        $userAccessToken = $statusResponse.access_token
        break
    }

    if ($null -ne $statusResponse.error) {
        $errorCode = $statusResponse.error.error_subcode
        $errorMessage = $statusResponse.error.message

        switch ($errorCode) {
            1349172 { continue } # authorization_pending
            1349173 { $interval += 5; continue } # slow_down
            1349174 { throw "Facebook device login was declined." }
            1349175 { throw "Facebook device login code expired." }
            default { throw "Facebook device login failed: $errorMessage" }
        }
    }
}

if ([string]::IsNullOrWhiteSpace($userAccessToken)) {
    throw "Timed out waiting for Facebook device approval."
}

$longTokenResponse = Invoke-RestMethod `
    -Method Get `
    -Uri "https://graph.facebook.com/$GraphVersion/oauth/access_token?grant_type=fb_exchange_token&client_id=$(UrlEncode $AppId)&client_secret=$(UrlEncode $AppSecret)&fb_exchange_token=$(UrlEncode $userAccessToken)"

$page = Find-PageToken -AccessToken $longTokenResponse.access_token -PageId $PageId -GraphVersion $GraphVersion

$env:FACEBOOK_PAGE_ACCESS_TOKEN = $page.access_token
$env:FACEBOOK_GRAPH_API_VERSION = $GraphVersion

if ($Save.IsPresent) {
    Set-EnvFileValue -Path $EnvFile -Name "FACEBOOK_PAGE_ACCESS_TOKEN" -Value $page.access_token
    Set-EnvFileValue -Path $EnvFile -Name "FACEBOOK_GRAPH_API_VERSION" -Value $GraphVersion
    Set-EnvFileValue -Path $EnvFile -Name "FACEBOOK_TOKEN_UPDATED_AT" -Value (Get-Date).ToString("o")
}

Write-Host "Connected Facebook page: $($page.name) ($($page.id))"
if ($Save.IsPresent) {
    Write-Host "Saved page access token to $EnvFile"
}
