<?php 
/**
 * Plugin Name: Muzaara Micosoft-Bing Product Data Feed
 * Description: Muzaara Micosoft-Bing Product Data Feed
 * Author:      Muzaara
 * Author URI:  https://muzaara.com
 * Plugin URI:  https://app.muzaara.com
 * Version:     2.0
 * Text Domain: muzaara-woopf-bing
 */

defined( 'ABSPATH' ) || exit;

define( 'MUZAARA_WOOPF_BING_VERSION', 1.0 );
define( 'MUZAARA_WOOPF_BING_PATH', sprintf( '%s/', __DIR__ ) );
define( 'MUZAARA_WOOPF_BING_OBJ_PATH', sprintf( '%sclass/objects/', MUZAARA_WOOPF_BING_PATH ) );
define( 'MUZAARA_WOOPF_BING_URL', sprintf( '%s/', plugins_url( '', __FILE__ )));
define( 'MUZAARA_WOOPF_BING_ASSET_URL', sprintf( '%sasset/', MUZAARA_WOOPF_BING_URL ) );
define( 'MUZAARA_WOOPF_BING_BASE', plugin_basename( __FILE__ ));
define( 'MUZAARA_WOOPF_BING_TAX_URL', sprintf( '%staxonomy/Taxonomy.%s.txt', MUZAARA_WOOPF_BING_ASSET_URL, get_locale() ) );
define( 'MUZAARA_WOOPF_BING_TAX_URL_FALLBACK', sprintf( '%staxonomy/Taxonomy.en_US.txt', MUZAARA_WOOPF_BING_ASSET_URL ) );
define( 'MUZAARA_WOOPF_BING_POST_TYPE', 'muzaara-woopf-bing' );
define( 'MUZAARA_WOOPF_BING_CRON_ACTION', 'muzaara_woopf_bing_cron_action' );

$upload_dir = wp_upload_dir();
if ( empty( $upload_dir[ 'error' ] ) ) {
    define( 'MUZAARA_WOOPF_BING_DUMP_PATH', sprintf( '%s/muzaara-woopf-bing/', $upload_dir[ 'basedir' ] ) );
    define( 'MUZAARA_WOOPF_BING_DUMP_URL', sprintf( '%s/muzaara-woopf-bing/', $upload_dir[ 'baseurl' ] ) );
}

require_once 'lib/muzaara/muzaara.php';
require_once 'class/App.php';

$GLOBALS[ 'muzaara_woopf_bing' ] = new \Muzaara\Bing\ProductFeed\App();

register_activation_hook( __FILE__, array( '\Muzaara\Bing\ProductFeed\App', 'activation' ) );
register_deactivation_hook( __FILE__, array( '\Muzaara\Bing\ProductFeed\App', 'deactivation' ) );
