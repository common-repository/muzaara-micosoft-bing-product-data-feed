<?php 
namespace Muzaara\Bing\ProductFeed\Object;
defined( "ABSPATH" ) || exit;

class Rule extends \Muzaara\Bing\ProductFeed\Abs\Condition {
    
    public function setStmt(Field $fieldA, Field $fieldB, Field $fieldC, Field $fieldD ) {
        if ( $this->condition ) {
            $this->stmt = (object) array(
                "A" => $fieldA,
                "B" => $fieldB,
                "C" => $fieldC,
                "D" => $fieldD
            );
        }
    }

    public function getStmt() {
        return apply_filters( "muzaara_woopf_bing_get_rule_statement", $this->stmt, $this );
    }

    public function execute( $product ) {
        do_action( "muzaara_woopf_bing_before_rule_execute", $this, $product);
        $ret = parent::execute($product);

        
        // $valueC = $this->stmt->C->getValue( $product );
        // $valueD = $this->stmt->D->getValue( $product );

        // if ( $ret ) {
        //     // $this->stmt->C = $this->stmt->D;
            
        //     // $this->stmt->C->setType()
        // }

        do_action( "muzaara_woopf_bing_after_rule_execute", $this, $product);

        return $ret;
    }
}