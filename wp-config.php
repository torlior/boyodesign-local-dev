<?php
define('WP_CACHE_KEY_SALT', 'abfb8be9d3f610f2998ec0e6a7e73e2c');
define('WP_DEBUG_DISPLAY', false);
define('WP_AUTO_UPDATE_CORE', 'minor');// הגדרה זו נדרשת כדי לוודא שניתן יהיה לנהל כהלכה עדכוני WordPress בערכת הכלים של WordPress. הסר שורה זו אם התקנת WordPress זו אינה מנוהלת עוד על ידי ערכת הכלים של WordPress.
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings ** //
/** The name of the database for WordPress */
define('DB_NAME', "boyodb");

/** MySQL database username */
define('DB_USER', "root");

/** MySQL database password */
define('DB_PASSWORD', "root");

/** MySQL hostname */
define('DB_HOST', "localhost");

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'Y&S7Y4sly4gsuyaN[LIzUTn;4r*m4qo*%+tfWTVB34M!#uc%&kwl--:89-lrD91L');
define('SECURE_AUTH_KEY', '|#g@UX%99BV&201E:J2a)13#)PQ#03_~Ay3h!363e79H7pP6Jc9&L_Qb3b4M50#z');
define('LOGGED_IN_KEY', '%9]Y1qp+4Cw%/46rVc:8(9Ph#9Qrz45pz_5#ZP5mgX281+dLGF09):IHw6!B8iD[');
define('NONCE_KEY', '59M(206G#41iI:b+bLc74C4T-Q)gTH261wWabQb#4-FFU25#*|F/6S9;0D56Ghz~');
define('AUTH_SALT', 'nUCSg&cW8i~9GN0Z8y|uifD5~V!N5Zu9b8q859F%84)ga_u@I0c/(~z02gLLWc!C');
define('SECURE_AUTH_SALT', '18V1qZ@:u#Kh06|9Px@4A1_KIk-[Vft@y~(!sOVmG7RYv%fX%a:1n9w~&hLd|dAX');
define('LOGGED_IN_SALT', 'IB:R+PJ4(i8i2~J4jQ8S3D5_|e65-S10@Jy*lHu0_gi;/|IZ80wX!S70y_/m3xV8');
define('NONCE_SALT', '*-aQoK~PcV#MvX:5l97]tk&1mD4X2uUL]Z2*Qgl#UYv]!weK&3X;NsmW2;75w0@e');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

define('WP_DEBUG', false);
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
