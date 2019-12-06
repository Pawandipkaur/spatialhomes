<?php
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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'projects_wordpress_spatial_home_development' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '&S{-e{E&S|p#t%<6IZ*3]J/5bQG~^dK,thFuG4p;`Qt#zV85JD)N?O$[_;~68bor' );
define( 'SECURE_AUTH_KEY',  ':$NNeHKRDH/pj*pXsv$nxu7jJinN:iXCnaFUXNQpeAB{3zMD0M~dnltpbjuerc06' );
define( 'LOGGED_IN_KEY',    'pRJ48fekT(u/MTt`T9mML.DdY6f+M1`p+%V808>]>d-/LLM<sPsKKH-y]K/Vjn?3' );
define( 'NONCE_KEY',        'xOt.+b[s X=SDtsxz1v87]Q4bIB*(4o^Dw_~5/x3o;w;E1%}l!QS&ueLMPu1cs2~' );
define( 'AUTH_SALT',        'pl2i*Per.VmH@AXGeu#UQUd`/LNjdNgC3JnYzV-,pK4-}]q/dDH`r<:G5rY<8Ku+' );
define( 'SECURE_AUTH_SALT', 'BaFB8F&)A(m*7W}53K Km?v2;Sx}vNb!^/mo_*c<^`>?{k)f4i!U2oC[y khRWF>' );
define( 'LOGGED_IN_SALT',   'Nt6Ic:~X9W#3xJ(ONBrL~| HR5rK)C-[#bF_q+Ekh%%m!YZ*0Fdu/=L~`DkK~3W1' );
define( 'NONCE_SALT',       'I()F!OE7[%0(crA@!cyKd;cfyG)}z{PC=z9JI.RZZ|w%/22<oCofI#$<zoFd.b}V' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
