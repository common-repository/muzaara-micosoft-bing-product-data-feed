<?php 
namespace Muzaara\Bing\ProductFeed;
use \Muzaara\Bing\ProductFeed\Object\Field;
use \Muzaara\Bing\ProductFeed\Object\GField;
use \Muzaara\Bing\ProductFeed\Object\Filter;

use function Muzaara\Bing\ProductFeed\Helpers\ProductFieldInit;

class Channels {
	private $app;
	protected $fields, $customFields, $bingFields;
	protected $filterConditions = array();

	public function __construct() {
		$this->fields = array(
			new Field('id',__('Product ID','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('name',__('Product Name','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('description',__('Product Description','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('link',__('Product Link','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('image',__('Product Image','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('height',__('Product Height','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('length',__('Product Length','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('regular_price',__('Product Regular Price','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('sku',__('Product SKU','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('weight',__('Product Weight','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('categories',__('Product Categories','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			
			new Field('width',__('Product Width','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('date_created',__('Product Created Date','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('date_modified',__('Product Modified Date','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('display_price',__('Product Display Price','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('price',__('Product Price','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('price_suffix',__('Product Price Suffix','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('purchase_note',__('Product Purchase Note','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('rating_count',__('Product Rating Count','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('review_count',__('Product Review Count','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('sale_price',__('Product Sale Price','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('short_description',__('Product Short Description','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('slug',__('Product Slug','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('stock_quantity',__('Product Stock Quantity','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('stock_status',__('Product Stock Status','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('total_sales',__('Product Total Sales','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('total_stock',__('Product Total Stock','muzaara-woopf-bing'),Field::PRODUCT_PROP),
			new Field('type',__('Product Type','muzaara-woopf-bing'),Field::PRODUCT_PROP)
		);

		$this->filterConditions = array(
			new Filter( Filter::CONDITION_EQUALS ),
			new Filter( Filter::CONDITION_NOT_EQUALS ),
			new Filter( Filter::CONDITION_CONTAINS ),
			new Filter( Filter::CONDITION_NOT_CONTAINS ),
			new Filter( Filter::CONDITION_IS_IN ),
			new Filter( Filter::CONDITION_IS_NOT_IN ),
			new Filter( Filter::CONDITION_BETWEEN ),
			new Filter( Filter::CONDITION_NOT_BETWEEN ),
			new Filter( Filter::CONDITION_GREATER_THAN ),
			new Filter( Filter::CONDITION_GREATER_EQUALS ),
			new Filter( Filter::CONDITION_LESS_THAN ),
			new Filter( Filter::CONDITION_LESS_EQUALS ),
			new Filter( Filter::CONDITION_IS_EMPTY ),
			new Filter( Filter::CONDITION_IS_NOT_EMPTY )
		);
		
		$this->loadMetaFields();
		$this->loadBingFields();
	}

	private function actions() {

	}

	public function getProductFields() {
		return apply_filters( 'muzaara_woopf_bing_get_product_fields', array_merge($this->fields, $this->customFields) );
	}

	public function getBingFields() {
		return apply_filters( 'muzaara_woopf_bing_get_google_fields', $this->bingFields );
	}

	public function loadBingFields() {
		$file = sprintf( '%sproduct_fields.json', MUZAARA_WOOPF_BING_PATH );

		$req = wp_remote_get( sprintf( '%sproduct_fields.json', MUZAARA_WOOPF_BING_ASSET_URL ), [ 'sslverify' => false ] );
		$content = wp_remote_retrieve_body( $req );
		$this->bingFields = array();

		if ( $content && ($fields = json_decode($content)) ) {
			foreach( $fields as $field ) {
				$this->bingFields[] = ProductFieldInit( $field );
			}
		}
	}

	public function getFilterConditions() {
		return apply_filters( 'muzaara_woopf_bing_get_filter_conditions', $this->filterConditions);
	}

	public function loadMetaFields() {
		global $wpdb;

		$this->customFields = array();

		$fields = $wpdb->get_results( "SELECT DISTINCT `meta_key` FROM `{$wpdb->postmeta}` INNER JOIN `{$wpdb->posts}` ON ID = post_id WHERE post_type = 'product'" );
		if ( $fields ) {
			foreach( $fields as $field ) {
				$this->customFields[] = new Field( $field->meta_key, $field->meta_key, Field::PRODUCT_META );
			}
		}
	}
}