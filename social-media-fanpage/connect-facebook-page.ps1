param(
    [string]$EnvFile = "$PSScriptRoot\facebook-env.local.ps1",
    [string]$AppId = $env:FACEBOOK_APP_ID,
    [string]$AppSecret = $env:FACEBOOK_APP_SECRET,
    [string]$PageId = $env:FACEBOOK_PAGE_ID,
    [string]$GraphVersion = $env:FACEBOOK_GRAPH_API_VERSION,
    [string]$RedirectUri = $env:FACEBOOK_REDIRECT_URI,
    [string[]]$Scopes = @("pages_show_list", "pages_manage_posts", "pages_read_engagement"),
    [int]$TimeoutSeconds = 300,
    [string]$CallbackUrl,
    [switch]$ManualCallback,
    [switch]$Save
)

$ErrorActionPreference = "Stop"

if (Test-Path -LiteralPath $EnvFile) {
    . $EnvFile
}

if ([string]::IsNullOrWhiteSpace($AppId)) { $AppId = $env:FACEBOOK_APP_ID }
if ([string]::IsNullOrWhiteSpace($AppSecret)) { $AppSecret = $env:FACEBOOK_APP_SECRET }
if ([string]::IsNullOrWhiteSpace($PageId)) { $PageId = $env:FACEBOOK_PAGE_ID }
if ([string]::IsNullOrWhiteSpace($GraphVersion)) { $GraphVersion = $env:FACEBOOK_GRAPH_API_VERSION }
if ([string]::IsNullOrWhiteSpace($GraphVersion)) { $GraphVersion = "v24.0" }
if ([string]::IsNullOrWhiteSpace($RedirectUri)) { $RedirectUri = "https://www.facebook.com/connect/login_success.html" }

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

function Receive-OAuthCode {
    param(
        [Parameter(Mandatory = $true)][string]$ExpectedState,
        [Parameter(Mandatory = $true)][uri]$RedirectUri,
        [Parameter(Mandatory = $true)][int]$TimeoutSeconds
    )

    $listener = [System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Loopback, $RedirectUri.Port)
    $listener.Start()

    try {
        $acceptTask = $listener.AcceptTcpClientAsync()
        if (-not $acceptTask.Wait($TimeoutSeconds * 1000)) {
            throw "Timed out waiting for Facebook OAuth redirect after $TimeoutSeconds seconds."
        }

        $client = $acceptTask.Result
        $stream = $client.GetStream()
        $reader = [System.IO.StreamReader]::new($stream)
        $writer = [System.IO.StreamWriter]::new($stream)
        $writer.AutoFlush = $true

        try {
            $requestLine = $reader.ReadLine()
            while ($true) {
                $line = $reader.ReadLine()
                if ([string]::IsNullOrEmpty($line)) { break }
            }

            if ([string]::IsNullOrWhiteSpace($requestLine)) {
                throw "OAuth redirect did not contain an HTTP request."
            }

            $target = $requestLine.Split(" ")[1]
            $query = ""
            if ($target.Contains("?")) {
                $query = $target.Substring($target.IndexOf("?") + 1)
            }

            $values = Parse-QueryString $query

            $body = "<html><body><h2>Facebook connected</h2><p>You can close this tab and return to Codex.</p></body></html>"
            if ($values.ContainsKey("error")) {
                $body = "<html><body><h2>Facebook authorization failed</h2><p>You can close this tab and return to Codex.</p></body></html>"
            }

            $bytes = [System.Text.Encoding]::UTF8.GetBytes($body)
            $writer.Write("HTTP/1.1 200 OK`r`nContent-Type: text/html; charset=utf-8`r`nContent-Length: $($bytes.Length)`r`nConnection: close`r`n`r`n")
            $stream.Write($bytes, 0, $bytes.Length)

            if ($values.ContainsKey("error")) {
                throw "Facebook authorization failed: $($values["error"]) $($values["error_description"])"
            }

            if (-not $values.ContainsKey("state") -or $values["state"] -ne $ExpectedState) {
                throw "OAuth state mismatch. Refusing to use this authorization response."
            }

            if (-not $values.ContainsKey("code")) {
                throw "Facebook OAuth redirect did not include an authorization code."
            }

            return $values["code"]
        }
        finally {
            $writer.Dispose()
            $reader.Dispose()
            $client.Dispose()
        }
    }
    finally {
        $listener.Stop()
    }
}

function Get-OAuthCodeFromCallbackUrl {
    param(
        [Parameter(Mandatory = $true)][string]$ExpectedState,
        [string]$CallbackUrl
    )

    if ([string]::IsNullOrWhiteSpace($CallbackUrl)) {
        $CallbackUrl = Read-Host "After approving the Facebook login, paste the full final callback URL here"
    }

    if ([string]::IsNullOrWhiteSpace($CallbackUrl)) {
        throw "Callback URL is empty."
    }

    $callback = [uri]$CallbackUrl
    $values = Parse-QueryString $callback.Query

    if ($values.ContainsKey("error")) {
        throw "Facebook authorization failed: $($values["error"]) $($values["error_description"])"
    }

    if (-not $values.ContainsKey("state") -or $values["state"] -ne $ExpectedState) {
        throw "OAuth state mismatch. Refusing to use this authorization response."
    }

    if (-not $values.ContainsKey("code")) {
        throw "The callback URL did not include an authorization code."
    }

    return $values["code"]
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

Assert-Value "AppId" $AppId
Assert-Value "AppSecret" $AppSecret
Assert-Value "PageId" $PageId

$redirect = [uri]$RedirectUri
$useManualCallback = $ManualCallback.IsPresent -or $redirect.Scheme -eq "https" -or $redirect.Host -notin @("127.0.0.1", "localhost")

if ($useManualCallback) {
    if ($redirect.Scheme -ne "https") {
        throw "Manual callback mode requires an HTTPS RedirectUri, such as https://www.facebook.com/connect/login_success.html."
    }
}
elseif ($redirect.Scheme -ne "http" -or $redirect.Host -notin @("127.0.0.1", "localhost")) {
    throw "Local listener mode requires a local HTTP RedirectUri such as http://127.0.0.1:8751/."
}

$state = [guid]::NewGuid().ToString("N")
$scopeText = [string]::Join(",", $Scopes)
$loginUrl = "https://www.facebook.com/$GraphVersion/dialog/oauth?client_id=$(UrlEncode $AppId)&redirect_uri=$(UrlEncode $RedirectUri)&state=$(UrlEncode $state)&scope=$(UrlEncode $scopeText)&response_type=code"

Write-Host "Opening Facebook authorization in your browser..."
Write-Host "Redirect URI must be added in the Facebook app settings: $RedirectUri"
Write-Host "If the browser does not open, copy this URL into your browser:"
Write-Host $loginUrl

Start-Process $loginUrl
if ($useManualCallback) {
    Write-Host "After approving, copy the full URL from the browser address bar and paste it here."
    $code = Get-OAuthCodeFromCallbackUrl -ExpectedState $state -CallbackUrl $CallbackUrl
}
else {
    $code = Receive-OAuthCode -ExpectedState $state -RedirectUri $redirect -TimeoutSeconds $TimeoutSeconds
}

$shortTokenUrl = "https://graph.facebook.com/$GraphVersion/oauth/access_token?client_id=$(UrlEncode $AppId)&redirect_uri=$(UrlEncode $RedirectUri)&client_secret=$(UrlEncode $AppSecret)&code=$(UrlEncode $code)"
$shortTokenResponse = Invoke-RestMethod -Method Get -Uri $shortTokenUrl

$longTokenUrl = "https://graph.facebook.com/$GraphVersion/oauth/access_token?grant_type=fb_exchange_token&client_id=$(UrlEncode $AppId)&client_secret=$(UrlEncode $AppSecret)&fb_exchange_token=$(UrlEncode $shortTokenResponse.access_token)"
$longTokenResponse = Invoke-RestMethod -Method Get -Uri $longTokenUrl

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
else {
    Write-Host "Page access token is available in this PowerShell process. Re-run with -Save to update $EnvFile."
}
