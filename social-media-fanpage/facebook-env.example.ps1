# Copy this file to facebook-env.local.ps1 and fill in real values.
# Do not commit facebook-env.local.ps1.

$env:FACEBOOK_PAGE_ID = "123456789012345"
$env:FACEBOOK_PAGE_ACCESS_TOKEN = "EAAB_REPLACE_WITH_LONG_LIVED_PAGE_TOKEN"
$env:FACEBOOK_GRAPH_API_VERSION = "v24.0"

# Required only when using connect-facebook-page.ps1 to fetch a new Page token.
$env:FACEBOOK_APP_ID = "123456789012345"
$env:FACEBOOK_APP_SECRET = "REPLACE_WITH_APP_SECRET"
$env:FACEBOOK_REDIRECT_URI = "taphoagiamgia.com"

# Keep this at 1 until you have tested the full flow.
$env:FACEBOOK_DRY_RUN = "1"
