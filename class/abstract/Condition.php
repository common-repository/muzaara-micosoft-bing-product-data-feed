<?php 
namespace Muzaara\Bing\ProductFeed\Abs;
defined( "ABSPATH" ) || exit;

abstract class Condition {
    protected $condition;
    protected $stmt;

    const CONDITION_CONTAINS        =   8;
    const CONDITION_NOT_CONTAINS    =   -8;
    const CONDITION_EQUALS          =   16;
    const CONDITION_NOT_EQUALS      =   -16;
    const CONDITION_GREATER_THAN    =   24;
    const CONDITION_GREATER_EQUALS  =   32;
    const CONDITION_LESS_THAN       =   -24;
    const CONDITION_LESS_EQUALS     =   -32;
    const CONDITION_IS_EMPTY        =   40;
    const CONDITION_IS_NOT_EMPTY    =   -40;
    const CONDITION_IS_IN           =   48;
    const CONDITION_IS_NOT_IN       =   -48;
    const CONDITION_BETWEEN         =   56;
    const CONDITION_NOT_BETWEEN     =   -56;

    const IS_RULE                   =   "RULE";
    const IS_FILTER                 =   "FILTER";

    public function __construct($condition) {

       if ( $this->isValidCondition( $condition ) )
            $this->condition = $condition;
    }

    abstract public function getStmt();
    // abstract public function setStmt(\Muzaara\Bing\ProductFeed\Object\Field $fieldA, \Muzaara\Bing\ProductFeed\Object\Field $fieldB, int $action);

    private function isValidCondition($condition) {
        $reflect = new \ReflectionClass($this);
        $conditions = $reflect->getConstants();
        $conditions = array_filter($conditions, function( $condition ) { return is_numeric($condition); });
       
        return in_array($condition, $conditions);
    }

    public function execute($product) {
        if ( empty( $product ) || empty( $this->stmt ) || empty( $this->condition ) ) {
            return false;
        }

        $valueA = $this->stmt->A->getValue( $product );
        $valueB = $this->stmt->B->getValue( $product );
       
        $ret = false;

        switch( $this->condition ) {
            case self::CONDITION_CONTAINS:
                $ret = preg_match( sprintf( "/%s/", preg_quote($valueB) ), $valueA );
            break;
            case self::CONDITION_NOT_CONTAINS:
                $ret = !preg_match( sprintf( "/%s/", preg_quote($valueB) ), $valueA );
            break;
            case self::CONDITION_EQUALS:
                $ret = $valueA == $valueB;
            break;
            case self::CONDITION_NOT_EQUALS:
                $ret = $valueA != $valueB;
            break;
            case self::CONDITION_GREATER_THAN:
                $ret = $valueA > $valueB;
            break;
            case self::CONDITION_GREATER_EQUALS:
                $ret = $valueA >= $valueB;
            break;
            case self::CONDITION_LESS_THAN:
                $ret = $valueA < $valueB;
            break;
            case self::CONDITION_LESS_EQUALS:
                $ret = $valueA <= $valueB;
            break;
            case self::CONDITION_IS_EMPTY:
                $ret = empty($valueA);
            break;
            case self::CONDITION_IS_NOT_EMPTY:
                $ret = !empty( $valueA );
            break;
            case self::CONDITION_IS_IN:
                $value = array_filter( explode( ",", $valueB ), "trim" );
                $ret = in_array( $valueA, $value );
            break;
            case self::CONDITION_IS_NOT_IN:
                $value = array_filter( explode( ",", $valueB ), "trim" );
                $ret = !in_array( $valueA, $value );
            break;
            case self::CONDITION_BETWEEN:
                $numbers = explode( ",", trim( $valueB ));
                if ( count( $numbers ) == 2 ) {
                    $ret = $valueA >= $numbers[0] && $valueA <= $numbers[1];
                }
            break;
            case self::CONDITION_NOT_BETWEEN:
                $numbers = explode( ",", trim( $valueB ));
                if ( count( $numbers ) == 2 ) {
                    $ret = !($valueA >= $numbers[0] && $valueA <= $numbers[1]);
                }
            break;
            default:
                $ret = false;
        }

        return $ret;
    }

    public function getName() : string {
        $ret = "";

        switch($this->condition) {
            case self::CONDITION_CONTAINS:
                $ret = __( "CONTAINS (LIKE)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_NOT_CONTAINS:
                $ret = __( "NOT CONTAINS (NOT LIKE)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_EQUALS:
                $ret = __( "EQUALS (=)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_NOT_EQUALS:
                $ret = __( "NOT EQUALS (!=)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_GREATER_THAN:
                $ret = __( "GREATER THAN (>)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_GREATER_EQUALS:
                $ret = __( "GREATER THAN & EQUALS (>=)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_LESS_THAN:
                $ret = __( "LESS THAN (<)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_LESS_EQUALS:
                $ret = __( "LESS THAN EQUALS (<=)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_IS_EMPTY:
                $ret = __( "IS EMPTY (!)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_IS_NOT_EMPTY:
                $ret = __( "IS NOT EMPTY (!!)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_IS_IN:
                $ret = __( "IN (..,..,)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_IS_NOT_IN:
                $ret = __( "NOT IN !(..,..,)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_BETWEEN:
                $ret = __( "BETWEEN (...)", "muzaara-woopf-bing" );
            break;
            case self::CONDITION_NOT_BETWEEN:
                $ret = __( "NOT BETWEEN !(...)", "muzaara-woopf-bing" );
            break;
            default:
                $ret = "";
        }

        return apply_filters( "muzaara_woopf_bing_get_condition_name", $ret, $this );
    }

    // public function getId() {
    //     return apply_filters( "muzaara_woopf_bing_get_condition_id", $this->id, $this );
    // }

    public function getCondition() {
        return apply_filters( "muzaara_woopf_bing_get_condition_condition", $this->condition, $this );
    }
}