<?php 
namespace Muzaara\Bing\ProductFeed;
defined( "ABSPATH" ) || exit;
use function \Muzaara\Bing\Engine\Functions\Bing\getAccounts;
use function \Muzaara\Bing\Engine\Functions\Bing\sendInvitation;

use function \Muzaara\Bing\ProductFeed\Helpers\createFeed;
use function Muzaara\Bing\ProductFeed\Helpers\getFeeds;
use function Muzaara\Bing\ProductFeed\Helpers\pauseFeed;
use function Muzaara\Bing\ProductFeed\Helpers\deleteFeed;
use function Muzaara\Bing\ProductFeed\Helpers\resumeFeed;
use function Muzaara\Bing\ProductFeed\Helpers\getFeed;
use Muzaara\Bing\ProductFeed\Object\Field;

class Ajax {
	protected $app;

	public function __construct( App $app ) {
		$this->app = $app;

		$this->actions();
	}

	private function actions() {
		add_action( "wp_ajax_muzaara_woopf_bing_checkAuth", array($this, "checkAuth" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_getAccounts", array($this, "getAccounts" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_linkAccount", array($this, "linkAccount" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_checkLink", array($this, "checkLink" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_getProductFields", array($this, "getProductFields" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_getProductCategories", array($this, "getProductCategories" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_searchCat", array($this, "searchCat" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_getConditions", array( $this, "getConditions" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_createChannel", array($this, "createChannel" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_getProductTypes", array($this, "getProductTypes" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_getFeeds", array( $this, "getFeeds" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_pauseFeed", array( $this, "pauseFeed" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_deleteFeed", array( $this, "deleteFeed" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_resumeFeed", array( $this, "resumeFeed" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_getFeed", array( $this, "getFeed" ) );
		add_action( "wp_ajax_muzaara_woopf_bing_runFeed", array( $this, "runFeed" ) );
	}

	private function hasAccess() {
		return current_user_can($this->app->cap );
	}

	public function checkAuth() {
		if ( current_user_can( $this->app->cap ) ) {
			$has_access = $this->app->has_access();
			if ( $has_access ) {
				wp_send_json_success( $has_access );
			} 

			wp_send_json_error( $has_access );
		}
	}

	public function getAccounts() {
		if ( ! extension_loaded( 'soap' ) ) {
			wp_send_json_error( __( "This plugin requires PHP SOAP module loaded. Please contact your server administrator", "muzaara-woopf-bing" ) );
		}
		if ( current_user_can( $this->app->cap ) ) {
			try {
				$getAccountsInfo = getAccounts();
				$accounts = array_filter( $getAccountsInfo->AccountsInfo->AccountInfo, function( $account ) {
					return true; // 'Active' === $account->AccountLifeCycleStatus;
				});

				if( $accounts ) {
					wp_send_json_success( array( "accounts" => array_values( $accounts ) ) );
				}

				wp_send_json_error( __( "No Ads account found", "muzaara-woopf-bing" ) );
			} catch ( \SoapFault $e ) {
				wp_send_json_error(
					sprintf(
						__( 'Error fetching accounts: %s:%s', 'muzaara-woopf-bing' ),
						$e->faultcode,
						$e->faultstring
					) 
				);
			} catch ( \Exception $e ) {
				wp_send_json_error(
					sprintf(
						__( 'Error fetching accounts: %s:%s %s', 'muzaara-woopf-bing' ),
						$e->getCode(),
						$e->getMessage(),
						$e->getTraceAsString()
					)
				);
			}
		}
		wp_send_json_error();
	}

	public function linkAccount() {
		if ( current_user_can($this->app->cap) && !empty( $_POST[ "account_id" ] ) ) {
			$account = sanitize_text_field( $_POST[ "account_id"] );

			$link = sendInvitation( $account );
			if ( $link ) {
				$link = json_decode($link);
				if ( $link->status ) {
					wp_send_json_success();
				}
				wp_send_json_error( $link );
			}

			wp_send_json_error(array("data" => __( "Unable to connect to API Server. Please contact plugin support", "muzaara-woopf-bing" ) ) );
		}

		wp_send_json_error();
	}

	public function checkLink() {
		if ( current_user_can( $this->app->cap ) ) {
			if ( $this->app->is_ready() ) {
				wp_send_json_success();
			}
		}

		wp_send_json_error();
	}

	public function getProductFields() {
		if ( current_user_can($this->app->cap) ) {
			$channels = $this->app->getInstance( "Muzaara\Bing\ProductFeed\Channels" );

			$fields = $channels->getProductFields();
			$gfields = $channels->getBingFields();
		  
			$fields = array_map(function( $field ) {
				return array( "name" => $field->getName(), "slug" => $field->getSlug(), "type" => $field->getType(), "typeFriendly" => $field->getTypeFriendly() );
			}, $fields );

			$gfields = array_map(function( $field ) {
				return array(
					"name" => $field->getName(),
					"slug" => $field->getSlug(),
					"group" => $field->getGroup(),
					"groupFriendly" => $field->getGroupName()
				);
			}, $gfields );

			wp_send_json(array(
				"success" => true, 
				"data" => array( "product" => $fields, "google" => $gfields )
			));
		}
	}

	public function getProductCategories() {
		global $wp_version;

		if ( $wp_version < 4.5 ) {
			$terms = get_terms("product_cat", array(
				"hide_empty" => false
			));
		} else {
			$terms = get_terms(array(
				"taxonomy" => "product_cat",
				"hide_empty" => false 
			));
		}

		if ( !is_wp_error($terms) ) {
			$categories = array_map(function($term) {
				return array(
					"id" => $term->term_id,
					"name" => $term->name,
					"parent" => $term->parent
				);
			}, $terms);

			wp_send_json_success( $categories );
		}

		wp_send_json_error();
	}


	public function searchCat() {
		if ( current_user_can( $this->app->cap ) ) {
			$search = $this->app->search_category(sanitize_text_field($_POST["cat_q"]));

			if ( $search ) {
				wp_send_json_success( $search );
			}

			wp_send_json_error();
		}
	}

	public function getConditions() {
		if ( current_user_can($this->app->cap) ) {
			$channelsInstance = $this->app->getInstance( "Muzaara\Bing\ProductFeed\Channels" );
			$conditions = array_map(function($con) {
				return array(
					"name" => $con->getName(),
					"condition" => $con->getCondition()
				);
			}, $channelsInstance->getFilterConditions() );

			wp_send_json_success( array( "filter" => $conditions ) );
		}
	}

	public function createChannel() {
		if ( current_user_can($this->app->cap ) ) {
			$post_id = createFeed($_POST);
			if ( !is_wp_error( $post_id ) )  {
				wp_send_json_success(array( "ID" => $post_id));
			}

			wp_send_json_error( $post_id->get_error_message() );
		}
	}

	public function getProductTypes() {
		if ( current_user_can( $this->app->cap ) ) {
			wp_send_json_success( wc_get_product_types() );
		}
	}

	public function getFeeds() {
		if ( current_user_can( $this->app->cap ) ) {
			$feeds = getFeeds();
			$productTypes = wc_get_product_types();

			$feeds = array_map(function( $feed ) use ($productTypes) {
				$feedProductTypes = $feed->getProductTypes();
				$post = $feed->getPost();

				$feedProductTypes = array_filter($productTypes, function( $type ) use ( $feedProductTypes ) {
					return in_array($type, $feedProductTypes);
				}, ARRAY_FILTER_USE_KEY );

				$nextRefresh = $feed->getNextRefresh();

				return array(
					"name" => $feed->getName(),
					"id" => $feed->getId(),
					"product_types" => $feedProductTypes,
					"country" => $feed->getCountry(),
					"push_type" => $feed->getPushType(),
					"refresh_rate" => $feed->getRefreshRate(),
					"is_active" => $post->post_status == "publish",
					"dump_url" => $feed->getDumpURL(),
					"merchantId" => $feed->getMerchantId(),
					"catalogId"	=> $feed->getCatalogId(),
					"total_products" => count( $feed->getProductIds() ),
					"running_status" => $feed->getRunningStatus(),
					"last_refreshed" => $feed->getLastRefreshed() ? date(sprintf( "%s %s", get_option( "date_format"), get_option( "time_format" )), $feed->getLastRefreshed() ) : "",
					"next_refresh" => $nextRefresh ? date(sprintf( "%s %s", get_option( "date_format"), get_option( "time_format" )), $nextRefresh) : "",
					"date_created" => sprintf( "%s %s", get_the_date("", $post), get_the_time("", $post) )
				);
			}, $feeds );

			wp_send_json_success( $feeds );
		}
	}

	public function pauseFeed() {
		if ( $this->hasAccess() && !empty($_POST[ "feed_id" ] ) ) {
			pauseFeed( intval($_POST[ "feed_id" ] ) );
		}
	}

	public function deleteFeed() {
		if ( $this->hasAccess() && !empty( $_POST[ "feed_id" ] ) ) {
			$deleted = deleteFeed( intval( $_POST[ "feed_id" ] ) );

			if ( $deleted ) {
				wp_send_json_success();
			}

			wp_send_json_error();
		}
	}

	public function resumeFeed() {
		if ( $this->hasAccess() && !empty( $_POST[ "feed_id" ] ) ) {
			$resumed = resumeFeed( intval( $_POST[ "feed_id"] ) );

			if ( $resumed ) {
				wp_send_json_success();
			}

			wp_send_json_error();
		}
	}

	public function runFeed() {
		if ( $this->hasAccess() && !empty( $_POST[ "feed_id" ] ) ) {
			$feed = getFeed($_POST[ "feed_id" ]);
			if ( $feed ) {
				$feed->generateDump();
				wp_send_json_success($feed->getDumpURL());
			}
		}
	}

	public function getFeed() {
		if ( $this->hasAccess() && !empty( $_POST[ "feed_id" ] ) ) {
			$feedId = intval( $_POST[ "feed_id" ] );
			$feed = getFeed( $feedId );

			if ( $feed ) {
				$data = array(
					"name"              =>  $feed->getName(),
					"country"           =>  $feed->getCountry(),
					"pushType"          =>  $feed->getPushType(),
					"refreshReate"      =>  $feed->getRefreshRate(),
					"productTypes"      =>  $feed->getProductTypes(),
					"utm"               =>  $feed->getUtm(),
					"categoryMappings"  =>  array(),
					"filters"           =>  array(),
					"rules"             =>  array(),
					"mappings"          =>  array(),
					"merchantId"        =>  $feed->getMerchantId(),
					"catalogId"	    => $feed->getCatalogId(),
					"noticeEmail"       =>  $feed->getNoticeEmail()
				);

				foreach( $feed->getCategoryMapping() as $mapping ) {
					$data[ "categoryMappings" ][$mapping[ "term_id"]] = $mapping[ "category" ];
				}

				foreach( $feed->getFilters() as $filter ) {
					$stmt = $filter->getStmt();

					$data["filters"][] = array(
						"if"                =>  $stmt->A->getSlug(),
						"condition"         =>  $filter->getCondition(),
						"value"             =>  $stmt->B->getSlug(),
						"then"              =>  intval($filter->getThen()),
						"valueType"         =>  intval($stmt->B->getType() == Field::CUSTOM_FIELD),
						"ifFieldType"       =>  $stmt->A->getType(),
						"valueFieldType"    =>  $stmt->B->getType()
					);
				}

				foreach( $feed->getRules() as $rule ) {
					$stmt = $rule->getStmt();

					$data[ "rules" ][] = array(
						"if"                =>  $stmt->A->getSlug(),
						"condition"         =>  $rule->getCondition(),
						"value"             =>  $stmt->B->getSlug(),
						"then"              =>  $stmt->C->getSlug(),
						"is"                =>  $stmt->D->getSlug(),
						"isFieldType"       =>  $stmt->D->getType(),
						"isType"            =>  intval($stmt->D->getType() == Field::CUSTOM_FIELD),
						"ifFieldType"       =>  $stmt->A->getType(),
						"thenFieldType"     =>  $stmt->C->getType(),
						"valueType"         =>  intval($stmt->B->getType() == Field::CUSTOM_FIELD),
						"valueFieldType"    =>  $stmt->B->getType()
					);
				}

				foreach( $feed->getMappings() as $mapping ) {
					$data[ "mappings" ][] = array(
						"productField"          =>  $mapping->getSlug(),
						"gField"                =>  $mapping->getProductField()->getSlug(),
						"prefix"                =>  $mapping->getPrefix(),
						"suffix"                =>  $mapping->getSuffix(),
						"type"                  =>  intval($mapping->getType() == Field::CUSTOM_FIELD),
						"productFieldType"      =>  $mapping->getType()
					);
				}

				wp_send_json_success( $data );
			}
		} 

		wp_send_json_error( array( "msg" => "not_found" ), 404 );
	}
}