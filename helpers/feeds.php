<?php 
namespace Muzaara\Bing\ProductFeed\Helpers;
defined( "ABSPATH" ) || exit;

require_once MUZAARA_WOOPF_BING_OBJ_PATH . "BingFeed.php";

use Muzaara\Bing\ProductFeed\Object\BingFeed;
use Muzaara\Bing\ProductFeed\Object\Field;

if ( !function_exists( "createFeed" ) ) {
	function createFeed(array $postargs) {
		if ( !empty( $postargs[ "name" ] ) && !empty( $postargs[ "productTypes" ] ) ) {
			$postargs[ "name" ] = esc_attr( sanitize_text_field( $postargs[ "name" ] ) );
			$postargs[ "country" ] = esc_attr( sanitize_text_field( $postargs[ "country" ] ) );
			$postargs[ "refreshRate" ] = intval($postargs[ "refreshRate" ] );
			$postargs[ "pushType" ] = intval($postargs["pushType"]);
			$postargs[ "merchantId" ] = isset( $postargs[ "merchantId" ] ) ? sanitize_text_field( $postargs[ "merchantId" ] ) : "";
			$postargs[ "catalogId" ] = isset( $postargs[ "catalogId" ] ) ? sanitize_text_field( $postargs[ "catalogId" ] ) : "";
			$postargs[ "noticeEmail" ] = isset( $postargs[ "noticeEmail" ] ) ? sanitize_email( $postargs[ "noticeEmail" ] ) : "";

			$feed = (new BingFeed())
				->setName( $postargs[ "name" ] )
				->setCountry($postargs[ "country" ] )
				->setRefreshRate( $postargs[ "refreshRate" ] )
				->setProductTypes( $postargs[ "productTypes" ] )
				->setPushtype( $postargs[ "pushType" ] );

			if ( !empty( $postargs[ "noticeEmail" ] ) ) {
				$feed->setNoticeEmail( $postargs[ "noticeEmail" ] );
			}

			if ( !empty( $postargs[ "merchantId" ] ) ) {
				$feed->setMerchantId( $postargs[ "merchantId" ] );
			}

			if ( ! empty( $postargs['catalogId'] ) ) {
				$feed->setCatalogId( $postargs['catalogId'] );
			}

			if ( !empty( $postargs[ "id" ] ) ) {
				$feed->setId( intval($postargs[ "id" ] ) );
			}

			if ( !empty( $postargs[ "categoryMappings" ] ) && is_array( $postargs[ "categoryMappings" ] ) ) {
				$feed->setCategoryMapping($postargs[ "categoryMappings" ] );
			}

			if ( !empty( $postargs[ "utm" ] ) && is_array( $postargs[ "utm" ] ) ) {
				$feed->setUtm($postargs["utm"]);
			}

			if ( !empty( $postargs[ "mappings" ] ) && is_array( $postargs[ "mappings" ] ) ) {
				$feed->setMappings($postargs[ "mappings" ] );
			}

			if ( !empty( $postargs[ "filters" ] ) && is_array( $postargs[ "filters" ] ) ) {
				foreach( $postargs[ "filters" ] as $filter ) {
					$filter[ "if_type" ] = $filter[ "ifFieldType" ];
					$filter[ "value_type" ] =  ( !empty( $filter[ "valueType" ] ) ? Field::CUSTOM_FIELD : $filter[ "valueFieldType" ] );

					$feed->setFilter($filter);
				}
			}

			if ( !empty( $postargs[ "rules" ] ) && is_array( $postargs[ "rules" ] ) ) {
				foreach( $postargs[ "rules" ] as $rule ) {
					$rule[ "if_type" ] = $rule[ "ifFieldType" ];
					$rule[ "value_type" ] =  ( !empty( $rule[ "valueType" ] ) ? Field::CUSTOM_FIELD : $rule[ "valueFieldType" ] );
					$rule[ "then_type" ] = $rule[ "thenFieldType" ];
					$rule[ "is_type" ] =  ( !empty( $rule[ "isType" ] ) ? Field::CUSTOM_FIELD : $rule[ "isFieldType" ] );
					$feed->setRule( $rule );
				}
			}

			$post_id = $feed->save(true);
			return $post_id;
		}

		return new \WP_Error( "no_title", __( "Unable to create feed: No title or product types", "muzaara-woopf-bing" ));
	}
}

function getFeed(int $postId) {
	$post = get_post( $postId );
	$feed = null;

	if ( $post && $post->post_type == MUZAARA_WOOPF_BING_POST_TYPE ) {
		$feedType = get_post_meta($postId, "muzaara_woopf_bing_feed_type", true);
		switch( $feedType ) {
			case "bing":
				$feed = new BingFeed($postId);
			break;
			default:
				$feed = null;
		}

		if ( $feed ) {
			$feed->setName($post->post_title)
				->setPushType( get_post_meta($postId, "muzaara_woopf_bing_push_type", true) )
				->setRefreshRate( get_post_meta($postId, "muzaara_woopf_bing_refresh_rate", true) )
				->setProductTypes(get_post_meta( $postId, "muzaara_woopf_bing_product_types" ))
				->setStatus($post->post_status)
				->setLastRefreshed( get_post_meta( $postId, "muzaara_woopf_bing_last_refreshed", true ) )
				->setDumpURL( get_post_meta( $postId, "muzaara_woopf_bing_dump_url", true ) )
				->setMerchantId( get_post_meta( $postId, "muzaara_woopf_bing_merchant_id", true ) )
				->setCatalogId( get_post_meta( $postId, "muzaara_woopf_bing_catalog_id", true ) )
				->setRunningStatus( (bool) get_post_meta( $postId, "muzaara_woopf_bing_running_status", true ) )
				->setCountry( get_post_meta( $postId, "muzaara_woopf_bing_country", true ) );

			$feed->setNoticeEmail( get_post_meta( $postId, "muzaara_woopf_bing_notice_email", true ) );
			$categories = get_post_meta( $postId, "muzaara_woopf_bing_category_mapping", true);
			$mappings = get_post_meta( $postId, "muzaara_woopf_bing_mappings", true );
			$filters = get_post_meta( $postId, "muzaara_woopf_bing_filters", true );
			$rules = get_post_meta( $postId, "muzaara_woopf_bing_rules", true );
			$utm = get_post_meta( $postId, "muzaara_woopf_bing_utm", true );
			$lastRefreshed = get_post_meta( $postId, "muzaara_woopf_bing_last_refreshed", true );
			$productIds = get_post_meta( $postId, "muzaara_woopf_bing_product_ids", true );

			if ( !empty( $productIds ) ) {
				$feed->setProductIds( $productIds );
			}

			if ( $categories && is_array( $categories ) ) {
				foreach( $categories as $category ) {
					$feed->setCategoryMap($category);
				}
			}

			if ( $mappings && is_array( $mappings ) ) {
				foreach( $mappings as $mapping ) {
					$feed->setMapping( $mapping );
				}
			}

			if ( $filters && is_array( $filters ) ) {
				foreach( $filters as $filter ) {
					$feed->setFilter( $filter );
				}
			}

			if ( $rules && is_array( $rules ) ) {
				foreach( $rules as $rule ) {
					$feed->setRule( $rule );
				}
			}

			if ( $utm && is_array( $utm ) ) {
				$feed->setUtm( $utm );
			}

			if ( $lastRefreshed ) {
				$feed->setLastRefreshed(intval( $lastRefreshed) );
			}
		}
	}

	return $feed;
}

function getFeeds(array $postargs = array()) {
	$defaults = array(
		"post_type"         =>  MUZAARA_WOOPF_BING_POST_TYPE,
		"post_status"       =>  array( "publish", "draft" ),
		"order"             =>  "DESC",
		"orderby"           =>  "date",
		"posts_per_page"    =>  -1
	);

	$postargs = array_merge( $defaults, $postargs);

	$posts = get_posts($postargs);
	if ( $posts ) {
		$posts = array_map(function( $post ) {
			return getFeed($post->ID);
		}, $posts);
		$posts = array_values( array_filter( $posts ) );
	}

	return $posts;
}

function pauseFeed($feed) {
	if ( !is_object( $feed ) ) {
		$feed = getFeed(intval($feed));

		if ( !$feed )
			return false;
	}

	$feed->setStatus( "draft" )
		->save();

	do_action( "muzaara_woopf_bing_feed_paused", $feed );

	return true;
}

function resumeFeed( $feed ) {
	if ( !is_object( $feed ) ) {
		$feed = getFeed(intval($feed));

		if ( !$feed )
			return false;
	}

	$feed->setStatus( "publish" )
		->save(true);

   
	do_action( "muzaara_woopf_bing_feed_resumed", $feed );

	return true;
}

function deleteFeed(int $feedId ) {
	$feed = getFeed( $feedId );

	if ( $feed ) {
		$feed->delete();

		return true;
	}

	return false;
}

function findFeedMatch( $product_id, $exact = false ) {
	$product = wc_get_product( $product_id );
	$ret = array();

	if ( $product ) {
		$postargs = array(
			"post_status"   =>  "publish",
			"post_type"     =>  MUZAARA_WOOPF_BING_POST_TYPE,
			"meta_key"      =>  "muzaara_woopf_bing_product_types",
			"meta_value"    =>  $product->get_type(),
			"meta_compare"  =>  "IN",
			"nopaging"      =>  true,
			"posts_per_page"    =>  -1
		);
		$feeds = getFeeds( $postargs );

		if ( $feeds ) {
			foreach( $feeds as $feed ) {
				if ( $feed->executeFilters( $product ) ) {
					if ( $exact ) {
						$ret[] = $feed;
						break;
					}

					$ret[] = $feed;
				}
			}
		}
	}

	return $ret;
}

function pushProduct( $product_id ) {
	global $muzaara_woopf_bing;

	if ( $muzaara_woopf_bing->is_ready() ) {
		$matches = findFeedMatch($product_id);
		$matches = array_filter( $matches, function($feed) { return $feed->getPushType() == 1; });

		foreach( $matches as $feed ) {
			$feed->pushProduct( $product_id );
		}
	}
}

function deleteProduct( $product_id ) {
	global $muzaara_woopf_bing;

	if ( $muzaara_woopf_bing->is_ready() ) {
		$matches = findFeedMatch($product_id);
		$matches = array_filter( $matches, function($feed) { return $feed->getPushType() == 1; });

		if ( $matches ) {
			$matches[0]->deleteProduct( $product_id );
		}
	}
}