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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'employee-store' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         'w*27KI9w$i_!Ml;f]US%d]_$J0q]2WtQ,XsLd41iB$xNfgG ^F.Q{@pGw7LI/Dl@' );
define( 'SECURE_AUTH_KEY',  'g(,OC)YTaxhD8[x1#nE_wO))36j1&{mQ1B`lDu;udyOA8^xjSO>YM2tEa/i8bZ@P' );
define( 'LOGGED_IN_KEY',    '6N^o5!y:s8as2e?N<{,0IYUp}pGsAr;oJio7m]!Av#i/%Em.&;l]228(QlS>L3g@' );
define( 'NONCE_KEY',        'JMl>K;%=9cD9]sRe< 98SpVo+>Xf*KG$oj]5Ns<jMPGMV4`%tB**d+C V6*T[u%0' );
define( 'AUTH_SALT',        'sWp[`EEtoy|Un3@pC{f`,B=6a^O~0Z)]3p5/iY[6wERe7(p&lW0-72:zAmVIMGr~' );
define( 'SECURE_AUTH_SALT', 'W+Q.Y;{lKB+,7dbWF/>dAEvjpY.@9JjDOS<{|}QB&0Ke:ydhb[heR&.jAi[S<$cG' );
define( 'LOGGED_IN_SALT',   'jR#W/U$J)y|C7q$}/<!j~[5e2I?f,vYflny={c|3b`mi:{f.$;b08&^8rsM_{L]U' );
define( 'NONCE_SALT',       '/kv@}R:q&>X=.Hbz-RBDN1qoa?Y5MhYap_Ddn[nY~5eL8V~@s5ZrX%h{o)<7p+]2' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wine_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false ); 
define( 'WP_DEBUG_LOG', false ); 
define( 'WP_MAX_MEMORY_LIMIT' , '512M' );


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

define('ALLOW_UNFILTERED_UPLOADS', true);
