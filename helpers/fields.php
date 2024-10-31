<?php 
namespace Muzaara\Bing\ProductFeed\Helpers;
defined( "ABSPATH" ) || exit;

require_once MUZAARA_WOOPF_BING_OBJ_PATH . "Field.php";

use \Muzaara\Bing\ProductFeed\Object\Field;

if ( !function_exists( "fieldInit" ) ) {
    function fieldInit( $id, $type, $name = "" ) {
        return new Field($id, $name, $type);
    }
}