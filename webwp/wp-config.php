<?php
/**
 * WordPress base configuration — webwp
 */

// ** Database settings ** //
define( 'DB_NAME',     'webwp' );
define( 'DB_USER',     'root' );
define( 'DB_PASSWORD', '123456' );
define( 'DB_HOST',     '127.0.0.1' );
define( 'DB_CHARSET',  'utf8mb4' );
define( 'DB_COLLATE',  '' );

// ** Authentication unique keys and salts ** //
define('AUTH_KEY',         '7UD^If(1+G[SucBAPS5r<Ux$D2ZaEF+Y+ <xhl5&F!s+ Oy57P*%|T<sR6<0p-wx');
define('SECURE_AUTH_KEY',  '+sz$:aa?b#s]q$][up]~d!41goIvrjr.5%ATN4~_1ASL0>VTnN[@EyeP;> t/uM+');
define('LOGGED_IN_KEY',    'hXpLv(7M-uazniS$S%o_`<1 6=y|;1E.7JxN%<VV`ocmf*|Yal2AH1-O.}W)s:Z;');
define('NONCE_KEY',        'W=]+*!SR=$f;alj7]1LF&LMqu@SH|qAaR81xf$-[prcwMy]a@R}eS`aIUdvcu*s;');
define('AUTH_SALT',        '#ke3<{:c<:-ulU8-3U4mN#98abYGa+aq)(6M72of9lZ+ghp`C%022HoJ,V+AI>^B');
define('SECURE_AUTH_SALT', ']5j@)Z2Ua?e4:y^fl2(-eKI,OU+l,vs+uHC(+bn^2o^%Z2_cN=(@}P$H}lbWbjcu');
define('LOGGED_IN_SALT',   '34F%9rZu*w0k2=u46`LeYXrmi>w!T#pMiiJ^#T>|F1QbB~$`OvU2*ZpIcvJriH*j');
define('NONCE_SALT',       'qGsVG}J8>+-Oaon%*fgUUP L|tQDHe_aM;0&i;q{15G25wC1RRuz,SgJ,aVAvN f');

$table_prefix = 'wp_';

// Dev settings
define( 'WP_DEBUG',         true );
define( 'WP_DEBUG_LOG',     true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

define( 'WP_HOME',    'http://learning.local' );
define( 'WP_SITEURL', 'http://learning.local' );

define( 'FS_METHOD', 'direct' );

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';