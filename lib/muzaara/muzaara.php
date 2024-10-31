<?php 
/**
 * Plugin Name: Muzaara Bing Engine
 * Description: Muzaara's library used across most Muzaara plugin
 * Author:      James John
 * Author URI:  https://fiverr.com/donjajo 
 * Plugin URI:  https://linkedin.com/in/donjajo
 * Version:     1.2
 * Text Domain: muzaara
 */

defined( 'MUZAARA_BING_OAUTH_URL' ) || define( 'MUZAARA_BING_OAUTH_URL', 'https://api.muzaara.com/oauth/' );
defined( 'MUZAARA_BING_API_URL' ) || define( 'MUZAARA_BING_API_URL', 'https://api.muzaara.com/wp-json/muzaara/server/' );
defined( 'MUZAARA_BING_PATH' ) || define( 'MUZAARA_BING_PATH', sprintf( '%s/', __DIR__ ) );
defined( 'MUZAARA_BING_ASSET_PATH' ) || define( 'MUZAARA_BING_ASSET_PATH', sprintf( '%sasset/', MUZAARA_BING_PATH ) );
defined( 'MUZAARA_BING_LIB_PATH' ) || define( 'MUZAARA_BING_LIB_PATH', sprintf( '%slib/', MUZAARA_BING_PATH ) );
defined( 'MUZAARA_BING_FUNC_PATH' ) || define( 'MUZAARA_BING_FUNC_PATH', sprintf( '%s/functions/', __DIR__ ) );
defined( 'MUZAARA_BING_GOOGLE_SCOPES' ) || define( 'MUZAARA_BING_GOOGLE_SCOPES', array(
    'content' => 'https://www.googleapis.com/auth/content',
    'adwords' => 'https://www.googleapis.com/auth/adwords'
));

defined( 'MUZAARA_BING_SCOPES' ) || define( 'MUZAARA_BING_SCOPES', [ 'ads' => 'https://ads.microsoft.com/msads.manage' ] );

require_once 'functions/access.php';
require_once 'functions/bing.php';
require_once 'classes/wpjson.php';

// register_deactivation_hook( __FILE__, '\Muzaara\Engine\Functions\Plugins\disablePlugins' );

if ( ! isset( $GLOBALS[ 'muzaara_bing' ] ) ) {
    $GLOBALS[ 'muzaara_bing' ] = new \StdClass;

    $GLOBALS[ 'muzaara_bing' ]->wpjson = new \Muzaara\Bing\Engine\WPJSON();
}
