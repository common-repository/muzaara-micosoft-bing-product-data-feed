<?php 
namespace Muzaara\Bing\ProductFeed\Object;
defined( 'ABSPATH' ) || die;

class Filter extends \Muzaara\Bing\ProductFeed\Abs\Condition {
	const __EXCLUDE__ = 'EXCLUDE';
	const __INCLUDE__ = 'INCLUDE';

	public function setStmt(Field $fieldA, Field $fieldB, int $action) {
		if ( $this->condition ) {
			$this->stmt = (object) array(
				'A' => $fieldA,
				'B' => $fieldB,
				'istrue' => !$action ? self::__EXCLUDE__ : self::__INCLUDE__ 
			);
		}
	}

	public function getThen() : bool {
		return $this->stmt && $this->stmt->istrue == self::__INCLUDE__;
	}

	public function getStmt() {
		return apply_filters( 'muzaara_woopf_bing_get_filter_statement', $this->stmt, $this );
	}

	public function execute( $product ) {
		do_action( 'muzaara_woopf_bing_before_filter_execute', $this, $product);
		$ret = parent::execute( $product );
		do_action( 'muzaara_woopf_bing_after_filter_execute', $this, $product);

		return apply_filters( 'muzaara_woopf_bing_filter_execute', ( $ret ? $this->stmt->istrue : '' ), $product, $this );
	}
}
