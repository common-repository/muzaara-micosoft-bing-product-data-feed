<?php 
namespace Muzaara\Bing\ProductFeed\Abs;
defined( "ABSPATH" ) || exit;

abstract class Feed {

    const MIN_REFRESH_RATE = 1;

    protected $id = 0;
    protected $name = null;
    protected $pushType;
    protected $refreshRate;
    protected $country;
    protected $type;
    protected $productTypes = array();
    protected $mappings = array();
    protected $categoryMapping = array();
    protected $filters = array();
    protected $rules = array();
    protected $lastRefreshed = 0;
    protected $dumpURL;
    protected $status = "publish";
    protected $runningStatus;
    protected $productIds = array();

    public function __construct(int $id = 0) {
        $this->id = $id;

        $this->refreshRate = self::MIN_REFRESH_RATE;
    }

    public function getId() : int {
        return apply_filters( "muzaara_woopf_bing_get_feed_id", $this->id, $this );
    }

    public function getLastRefreshed() : ?int {
        if ( $this->pushType == 2 && $this->lastRefreshed )
            return apply_filters( "muzaara_woopf_bing_get_feed_last_refreshed", $this->lastRefreshed, $this );
        
        return null;
    }

    public function getDumpURL() : ?string {
        return apply_filters( "muzaara_woopf_bing_get_feed_dump_url", $this->dumpURL, $this );
    }

    public function setId( int $id ) {
        $this->id = $id;

        return $this;
    }

    public function setDumpURL(string $url) {
        $this->dumpURL = $url;

        return $this;
    }

    public function setPushType(int $pushType) {
        $this->pushType = $pushType;

        return $this;
    }

    public function setRefreshRate(int $refreshRate) {
        $this->refreshRate = $refreshRate;
        if ( $this->refreshRate < self::MIN_REFRESH_RATE ) {
            $this->refreshRate = self::MIN_REFRESH_RATE;
        }

        return $this;
    }

    public function setCountry(string $country) {
        $country = trim(esc_attr(sanitize_text_field( $country ) ) );
        if ( strlen( $country ) != 2 ) {
            $country = "US";
        }

        $this->country = $country;
        return $this;
    }

    public function setLastRefreshed( int $lastRefreshed ) {
        $this->lastRefreshed = $lastRefreshed;

        return $this;
    }

    public function setRunningStatus( bool $status ) {
        $this->runningStatus = $status;

        return $this;
    }

    public function getRunningStatus() : bool {
        return apply_filters( "muzaara_woopf_bing_get_feed_running_status", $this->runningStatus, $this );
    }

    public function getNextRefresh() {
        $post = $this->getPost();

        if ( $this->pushType == 2 && $post->post_status == "publish") {
            $secs = $this->refreshRate * 60 * 60;
            $nextRefresh = ($this->lastRefreshed ? $secs + $this->lastRefreshed : strtotime( $post->post_date));
            
            return apply_filters( "muzaara_woopf_bing_get_feed_next_refresh", $nextRefresh, $this);
        }
    }

    public function setName(string $name) {
        $this->name = esc_attr( sanitize_text_field( $name ) );

        return $this;
    }

    public function setStatus( string $status ) {
        $this->status = $status;

        return $this;
    }
    
    public function setCategoryMapping(array $catMap) {
        foreach( $catMap as $term_id => $googleCat ) {
            // if ( trim( $googleCat ) ) {
            //     $this->categoryMapping[] = array(
            //         "term_id" => intval($term_id),
            //         "category" => sanitize_text_field( $googleCat )
            //     );
            // }

            $this->setCategoryMap(array(
                "term_id" => intval($term_id),
                "category" => sanitize_text_field( $googleCat )
            ));
        }

        return $this;
    }

    public function setType($type) {
        $this->type = $type;

        return $this;
    }

    public function setProductId( int $id ) {
        if ( !in_array( $id, $this->productIds ) ) {
            $this->productIds[] = $id;
        }
        
        return $this;
    }

    public function setProductIds(array $ids) {
        $ids = array_map( "intval", $ids );
        $ids = array_values( array_unique( $ids ) );

        $this->productIds = $ids;

        return $this;
    }

    public function setCategoryMap(array $map) {
        $map[ "term_id" ] = intval( $map[ "term_id" ] );
        $map[ "category" ] = sanitize_text_field( $map[ "category" ] );

        if ( !empty( $map[ "category" ] ) ) 
            $this->categoryMapping[] = $map;

        return $this;
    }

    public function setProductTypes(array $types) {
        $types = array_map("sanitize_text_field", $types);
        $this->productTypes = $types;

        return $this;
    }


    public function getProductIds() : array {
        return apply_filters( "muzaara_woopf_bing_get_feed_product_ids", $this->productIds, $this );
    }

    public function getCountry() {
        return apply_filters( "muzaara_woopf_bing_get_feed_country", $this->country, $this );
    }

    public function getPushType() {
        return apply_filters( "muzaara_woopf_bing_get_feed_push_type", $this->pushType, $this );
    }

    public function getProductTypes() : array {
        return apply_filters( "muzaara_woopf_bing_get_feed_product_type", $this->productTypes, $this );
    }

    public function getRefreshRate() {
        if ( $this->pushType == 2 )
            return apply_filters( "muzaara_woopf_bing_get_feed_refresh_rate", $this->refreshRate, $this );
    }

    public function getCategoryMapping() : array {
        return apply_filters( "muzaara_woopf_bing_get_feed_category_mapping", $this->categoryMapping );
    }

    public function getMappings() : array {
        return apply_filters( "muzaara_woopf_bing_get_feed_mappings", $this->mappings, $this );
    }

    public function getName() : string {
        return apply_filters( "muzaara_woopf_bing_get_feed_name", $this->name,  $this );
    }

    public function setMappings(array $mappings) {
        foreach( $mappings as $mapping ) {
            $type = intval(@$mapping["type"]);

            if ( ($type == 0 && empty( $mapping[ "productField" ] )) || empty( $mapping["gField"] ) )  {
                continue;
            }

            $this->setMapping(array(
                "slug" => esc_attr( sanitize_text_field( $mapping[ "productField" ] ) ),
                "gField" => esc_attr( sanitize_text_field( $mapping[ "gField" ] ) ),
                "prefix" => esc_attr( sanitize_text_field( $mapping[ "prefix" ] ) ),
                "suffix" => esc_attr( sanitize_text_field( $mapping[ "suffix" ] ) ),
                // I reimagined to create a constant for custom field. This is not recognized in the frontend to be able to change select to input field. So, translate back to what backend understands
                "type" => ( !empty( $mapping[ "type" ] ) ? WooField::CUSTOM_FIELD : intval( $mapping[ "productFieldType" ]) )
            ));
        }
    }

    public function delete() {
        if ( $this->id ) {
            do_action( "muzaara_woopf_bing_before_delete_feed", $this->id, $this);
            wp_delete_post( $this->id, true);
            do_action( "muzaara_woopf_bing_after_delete_feed", $this->id, $this);
        }
    }

    abstract protected function setMapping(array $mapping);
    abstract public function setFilter(array $filter);
    abstract public function getFilters() : array;
    abstract public function getRules() : array;
    abstract public function setRule(array $rule);
    abstract public function executeFilters($product);
    abstract public function generateDump();
    abstract public function findProducts() : array;

    public function getPost() {
        if ( !$this->id ) 
            return array();

        return \get_post($this->id);
    }

    abstract public function save();
    // public function save() : int {
    //     do_action( "muzaara_woopf_bing_before_channel_save", $this );

    //     if ( $this->id ) {
    //         return wp_insert_post( array(

    //         ), false );
    //     } else {
    //         return wp_update_post( array(), false );
    //     }

    //     do_action( "muzaara_woopf_bing_after_channel_save", $this );
    // }
}