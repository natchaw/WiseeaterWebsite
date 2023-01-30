<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */
define('WP_MEMORY_LIMIT', '256M');
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'Wiseeater' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'P/UL$sO|hUCQE+om@{(:.TPWAq3uE20gsV?:};[ooI&Qu!qPT2e<N*:Jbg(PKGy=' );
define( 'SECURE_AUTH_KEY',  'SQ&E`lWM{a>@*c+xwr,bm@=J]ak$JG?6:>nOCWuqv(560->RecGPA8,=;glF6C*<' );
define( 'LOGGED_IN_KEY',    '?6i^9H`# HD; 3FyXr?wr=#8X>ND_#A)Q~^07b50QGg321o*uyI[E#g<3`to0d3C' );
define( 'NONCE_KEY',        'W&-AQy9GM6+*#VK,VgPVxHY(bd/b?jf%68/w6gP0$S5;ck6C&fTm<r[lYdB_MLoA' );
define( 'AUTH_SALT',        'icHx e)/VC%@D7qj<8J|yx2{S+Y }<`ch5AmDiS&pzQ8|2~h42Iovn?bWnDR~kPZ' );
define( 'SECURE_AUTH_SALT', 'U-C03.{@5G#{V(RICA/aN;6!_=rOYn8Ayq&m#}@501/!ebNt.Z9L^|e@5V;e~/k}' );
define( 'LOGGED_IN_SALT',   'H`cG]RH)QmdE-EB5vnX>f67D|]a9_nm$M2maM1&|{jG=y9B!N9?cmi)uLH&`W)9G' );
define( 'NONCE_SALT',       '#~6eA>lcA?qat~y@hqwq&%o1HYPgJDuw5`{C>Q;eK=lI8Z1@O|x^ +EY;2[v2%RU' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
