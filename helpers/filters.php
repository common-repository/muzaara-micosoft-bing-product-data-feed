<?php 
namespace Muzaara\Bing\ProductFeed\Helpers;
defined( "ABSPATH" ) || exit;

require_once MUZAARA_WOOPF_BING_OBJ_PATH . "Filter.php";

use \Muzaara\Bing\ProductFeed\Object\Filter;

if ( !function_exists( "filterInit" ) ) {
    function filterInit( $condition ) {
        return new Filter($condition);
    }
}