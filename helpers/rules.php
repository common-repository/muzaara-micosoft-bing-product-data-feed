<?php 
namespace Muzaara\Bing\ProductFeed\Helpers;
defined( "ABSPATH" ) || exit;

require_once MUZAARA_WOOPF_BING_OBJ_PATH . "Rule.php";

use \Muzaara\Bing\ProductFeed\Object\Rule;

if ( !function_exists( "ruleInit" ) ) {
    function ruleInit( int $condition ) {
        return new Rule($condition);
    }
}