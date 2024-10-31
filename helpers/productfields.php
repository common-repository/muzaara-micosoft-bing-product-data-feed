<?php 
namespace Muzaara\Bing\ProductFeed\Helpers;
defined( "ABSPATH" ) || exit;

require_once MUZAARA_WOOPF_BING_OBJ_PATH . "ProductField.php";

use \Muzaara\Bing\ProductFeed\Object\ProductField;

if ( !function_exists( "ProductFieldInit" ) ) {
    function ProductFieldInit( \StdClass $field ) {
        return new ProductField($field);
    }
}