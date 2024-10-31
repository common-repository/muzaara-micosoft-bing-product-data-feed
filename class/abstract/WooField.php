<?php 
namespace Muzaara\Bing\ProductFeed\Abs;
defined( "ABSPATH" ) || exit;


abstract class WooField {
    protected $slug;
    protected $name;
    protected $type;
    protected $value = "";
    protected $suffix = "";
    protected $prefix = "";
    protected $productField;

    const PRODUCT_PROP = 1;
    const CUSTOM_FIELD = 2;
    const PRODUCT_META = 3;

    public function __construct(string $slug, string $name, int $type ) {
        $this->slug = $slug;
        $this->name = $name;
        $this->type = $type;

        if ( $this->type == self::CUSTOM_FIELD )
            $this->setValue($slug);
    }

    public function getName() : string {
        return apply_filters( "muzaara_woopf_bing_get_field_name", $this->name, $this );
    }

    public function getSlug() : string {
        return apply_filters( "muzaara_woopf_bing_get_field_slug", $this->slug, $this );
    }

    public function getType() : int {
        return apply_filters( "muzaara_woopf_bing_get_field_type", $this->type, $this );
    }

    public function getSuffix() : string {
        return apply_filters( "muzaara_woopf_bing_get_field_suffix", $this->suffix, $this );
    }

    public function getPrefix() : string {
        return apply_filters( "muzaara_woopf_bing_get_field_prefix", $this->prefix, $this );
    }

    public function getProductField() : \Muzaara\Bing\ProductFeed\Object\ProductField {
        return apply_filters("muzaara_woopf_bing_get_field_productfield", $this->productField, $this );
    }

    public function setType( int $type ) {
        $this->type = $type;

        return $this;
    }

    public function setProductField(\Muzaara\Bing\ProductFeed\Object\ProductField $productField) {
        $this->productField = $productField;

        return $this;
    }

    public function setPrefix(string $prefix) {
        $this->prefix = esc_attr( $prefix );

        return $this;
    }

    public function setSuffix(string $suffix) {
        $this->suffix = esc_attr( $suffix );

        return $this;
    }

    public function setValue( string $value ) {
        $this->value = esc_attr($value);

        return $this;
    }

    public function getTypeFriendly() : string {
        $ret = "";

        switch( $this->type ) {
            case self::PRODUCT_PROP:
                $ret = __( "Product", "muzaara-woopf-bing" );
            break;
            case self::PRODUCT_META:
                $ret = __( "Meta Field", "muzaara-woopf-bing" );
            break;
            case self::CUSTOM_FIELD:
                $ret = __( "Custom Field", "muzaara-woopf-bing" );
            break;
            default:
                $ret = "";
        }

        return $ret;
    }

    public function getValue($product) {
        if ( !$product || !$this->slug )
            return "";

        $result = "";
        $dateformat = sprintf( "%s %s", get_option( "date_format" ), get_option( "time_format" ));

        switch($this->type) {
            case self::PRODUCT_PROP:
                switch( $this->slug ) {
                    case "name":
                        $result = $product->get_name();
                    break;
                    case "description":
                        $result = $product->get_description();
                    break;
                    case "id":
                        $result = $product->get_id();
                    break;
                    case "height":
                        $result= $product->get_height();
                    break;
                    case "length":
                        $result = $product->get_length();
                    break;
                    case "regular_price":
                        $result = $product->get_regular_price();
                    break;
                    case "sku":
                        $result = $product->get_sku();
                    break;
                    case "weight":
                        $result = $product->get_weight();
                    break;
                    case "link":
                        $result = $product->get_permalink();
                    break;
                    case "width":
                        $result = $product->get_width();
                    break;
                    case "date_created":
                        if ( ( $date = $product->get_date_created() ) ) {
                            $result = $date->date( $dateformat );
                        }
                    break;
                    case "date_modified":
                        if ( ($date = $product->get_date_modified() ) ) {
                            $result = $date->date( $dateformat );
                        }
                    break;
                    case "display_price":
                        $result = $product->get_display_price();
                    break;
                    case "price":
                        $result = $product->get_price();
                    break;
                    case "price_suffix":
                        $result = $product->get_price_suffix();
                    break;
                    case "purchase_note":
                        $result = $product->get_purchase_note();
                    break;
                    case "rating_count":
                        $result = $product->get_rating_count();
                    break;
                    case "review_count":
                        $result = $product->get_review_count();
                    break;
                    case "image":
                        $result = wp_get_attachment_url($product->get_image_id());
                    break;
                    case "sale_price":
                        $result = $product->get_sale_price();
                    break;
                    case "short_description":
                        $result = $product->get_short_description();
                    break;
                    case "slug":
                        $result = $product->get_slug();
                    break;
                    case "stock_quantity":
                        $result = $product->get_stock_quantity();
                    break;
                    case "stock_status":
                        $result = $product->get_stock_status();
                        if ( 'instock' == $result ) {
                            $result = 'in stock';
                        } else if ( 'outofstock' == $result ) {
                            $result = 'out of stock';
                        } else if ( 'onbackorder' == $result ) {
                            $result = 'preorder';
                        }
                    break;
                    case "categories":
                        $terms = get_the_terms( $product->get_id(), "product_cat" );
                        if ( $terms ) {
                            $term_names = array_map(function( $term ) { return $term->name; }, $terms );
                            $result = implode( ",", $term_names);
                        }
                    break;
                    case "total_sales":
                        $result = $product->get_total_sales();
                    break;
                    case "total_stock":
                        $result = $product->get_total_stock();
                    break;
                    case "type":
                        $result = $product->get_type();
                    break;
                    default:
                        $result = "";
                }
            break;
            case self::PRODUCT_META:
                $result = $product->get_meta($this->slug );
            break;
            case self::CUSTOM_FIELD:
                $result = $this->value;
            break;
            default:
                $result = "";
        }

        return apply_filters( "muzaara_woopf_bing_get_field_value", $result, $this );
    }
}