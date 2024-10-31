<?php 
namespace Muzaara\Bing\Engine\Functions\Plugins;

if ( !function_exists( __NAMESPACE__ . '\addActive' ) ) {
    function addActive(string $plugin) {
        $active_plugins = getActive();

        if ( !$active_plugins || !in_array($plugin, $active_plugins) ) {
            $active_plugins[] = $plugin;
        }

        update_option( 'muzaara_plugins', $active_plugins);
    }
}

if ( !function_exists( __NAMESPACE__ . '\removeActive' ) ) {
    function removeActive(string $plugin) {
        $active = getActive();

        $active = array_filter($active, function( $p ) use ( $plugin ) { return $p !== $plugin; });

        update_option( 'muzaara_plugins', $active);
    }
}

if ( !function_exists( __NAMESPACE__ . '\isActive' ) ) {
    function isActive( string $plugin ) {
        $active = getActive();

        return in_array($plugin, $active);
    }
}

if ( !function_exists( __NAMESPACE__ . '\getActive' ) ) {
    function getActive() {
        return get_option( 'muzaara_plugins', array());
    }
}

if ( !function_exists( __NAMESPACE__ . '\getMenuIconSvg' ) ) {
    function getMenuIconSvg() {
        $img_data = base64_encode( file_get_contents( sprintf( '%simage/icon.svg', MUZAARA_BING_ASSET_PATH ) ) );
        return sprintf( 'data:image/svg+xml;base64,%s', $img_data );
    }
}

if ( !function_exists( __NAMESPACE__ . '\disablePlugins' ) ) {
    function disablePlugins() {
        $active = getActive();

        deactivate_plugins( $active, false );
    }
}