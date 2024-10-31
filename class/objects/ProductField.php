<?php 
namespace Muzaara\Bing\ProductFeed\Object;

class ProductField {
	protected $slug, $description, $schema, $group;

	public function __construct(\StdClass $field) {
		if ( !empty( $field->slug ) ) {
			$this->slug = $field->slug;
			$this->description = (!empty($field->description) ? $field->description : '' );
			$this->schema = (!empty($field->schema) ? $field->schema : '' );
			$this->group = (!empty($field->group) ? $field->group : '' );
		}
	}

	public function getSlug() : string {
		return apply_filters( 'muzaara_woopf_bing_get_product_field_slug', $this->slug, $this );
	}

	public function getName() : string {
		$name = str_replace( '_', ' ', $this->slug );
		$name = str_replace( 'min', __( 'Minimum', 'muzaara-woopf-bing' ), $name );
		$name = str_replace( 'max', __( 'Maxium', 'muzaara-woopf-bing' ), $name );
		$name = trim( $name );

		return apply_filters( 'muzaara_woopf_bing_get_product_field_name', ucwords($name), $this );
	}

	public function getGroup() : string {
		return apply_filters( 'muzaara_woopf_bing_get_product_field_group', $this->group, $this );
	}

	public function getGroupName() : string {
		$ret = '';

		switch( $this->group ) {
			case 'basic':
				$ret = __( 'Basic product data', 'muzaara-woopf-bing' );
			break;
			case 'price':
				$ret = __( 'Price & availability', 'muzaara-woopf-bing' );
			break;
			case 'category':
				$ret = __( 'Product category', 'muzaara-woopf-bing' );
			break;
			case 'identifiers':
				$ret = __( 'Product identifiers', 'muzaara-woopf-bing' );
			break;
			case 'product_description':
				$ret = __( 'Detailed product description', 'muzaara-woopf-bing' );
			break;
			case 'shopping_campaigns':
				$ret = __( 'Shopping campaigns and other configurations', 'muzaara-woopf-bing' );
			break;
			case 'destinations':
				$ret = __( 'Destinations', 'muzaara-woopf-bing' );
			break;
			case 'shipping':
				$ret = __( 'Shipping', 'muzaara-woopf-bing' );
			break;
			case 'tax':
				$ret = __( 'Tax', 'muzaara-woopf-bing' );
			break;
			default:
				$ret = '';
		}
		
		return $ret;
	}
}