<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'cege_cegesp' );

/** Database username */
define( 'DB_USER', 'cege_cegesp_user' );

/** Database password */
define( 'DB_PASSWORD', 'GogH@3H3e0a*FPv-' );

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
define( 'AUTH_KEY',         'RV(oRRGfSmM4b7)zH$A=>$]d}(j{%fi$v{BwFq8lrDGjhD|p77kX@&V2$[z;Lo{b' );
define( 'SECURE_AUTH_KEY',  'Y+aqVjM170kLI(LtH.+i@kkl.>^_ODYn{ >d;Pv/WjPLa_~vc,Q7/%:9s6,EEQY{' );
define( 'LOGGED_IN_KEY',    'KC^ro9_Fd*M!Q.;-[Yx9FqyZ&9LJ!MX&|TX!>oEP:$JBz;s-#!0S2bLm.uf3MPZM' );
define( 'NONCE_KEY',        'l(ffJ?=8f$) l30=E*Zf)WL^-BRU6yUpa5[uqrBZx;vmVrwg$C:A!DLyk},%i}T!' );
define( 'AUTH_SALT',        '0)D=@~1<JEH{MQw6ri.gyq(:HBE4;~-,v`Sz]gGVBi2j V(LlpL?E*AB1W+S;7 /' );
define( 'SECURE_AUTH_SALT', '-,SX&B]E<[ER.?7SA}U]/U>0%WWUhp5nGMO:e9t!x;l4fq= yPA)c~m|uyIbA4O;' );
define( 'LOGGED_IN_SALT',   'v4,7/fBdkWWw1gpLu>1d-1%-NlbT9aWUBk.@$e!EZ&`?XvxEIL()zj49VJ3L*d5D' );
define( 'NONCE_SALT',       '5LdBLWmQmzn{k`_bf|v2Q+8@*Za(s+K[tY=&$(4~Y//g/r!7Nd1D`-e|ssAp3}y4' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );

define('FS_METHOD', 'direct');

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
