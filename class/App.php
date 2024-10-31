<?php 
namespace Muzaara\Bing\ProductFeed;
defined( 'ABSPATH' ) || exit;
use function \Muzaara\Bing\Engine\Functions\Access\isManagerLinked;
use function \Muzaara\Bing\Engine\Functions\Plugins\addActive;
use function \Muzaara\Bing\Engine\Functions\Plugins\removeActive;
use function \Muzaara\Bing\Engine\Functions\Plugins\getMenuIconSvg;
use function \Muzaara\Bing\Engine\Functions\Plugins\isActive;
use function \Muzaara\Bing\Engine\Functions\Access\hasAccess;
use function \Muzaara\Bing\Engine\Functions\Access\generateOAuthURL;
use function \Muzaara\Bing\ProductFeed\Helpers\findFeedMatch;

class App {
	private $l10n;
	public $cap = 'manage_options';
	public $google_cat = array();

	public function __construct() {
		$this->includes();
		$this->load_texts();
		$this->actions();
		
		new Ajax($this);
	}

	private function load_texts() {
		$this->l10n = new \StdClass;

		$this->l10n->hello = 'Hello World';
		$this->l10n->parentHeader			= __( 'Bing Product Feed', 'muzaara-woopf-bing' );
		$this->l10n->linkGoogle				= __( 'Link Bing Account', 'muzaara-woopf-bing' );
		$this->l10n->linkingAccount			= __( 'Linking Bing Account...', 'muzaara-woopf-bing' );
		$this->l10n->linkGoogleDesc			= __( 'You are required to authenticate your Bing account before using this service', 'muzaara-woopf-bing' );
		$this->l10n->no_account_found			= __( 'No Bing Ads account found.', 'muzaara-woopf-bing' );
		$this->l10n->chooseAccount			= __( 'Choose Ad Account', 'muzaara-woopf-bing');
		$this->l10n->linkAccount			= __( 'Link Account', 'muzaara-woopf-bing' );
		$this->l10n->linking				= __( 'Linking...', 'muzaara-woopf-bing' );
		$this->l10n->linkError				= __( 'Unable to link account, please try another account. If error persists, kindly contact plugin support with the below error:', 'muzaara-woopf-bing' );
		$this->l10n->active				= __( 'Active', 'muzaara-woopf-bing' );
		$this->l10n->channelName			= __( 'Channel Name', 'muzaara-woopf-bing' );
		$this->l10n->refreshRate			= __( 'Refresh Rate', 'muzaara-woopf-bing' );
		$this->l10n->noChannels				= __( 'No channels created yet.', 'muzaara-woopf-bing');
		$this->l10n->createNew				= __( 'Create New Channel', 'muzaara-woopf-bing' );
		$this->l10n->channelCountry			= __( 'Channel Country', 'muzaara-woopf-bing' );
		$this->l10n->pushType				= __( 'Push Type', 'muzaara-woopf-bing' );
		$this->l10n->refreshRate			= __( 'Refresh Rate', 'muzaara-woopf-bing' );
		$this->l10n->continue				= __( 'Continue', 'muzaara-woopf-bing' );
		$this->l10n->daily				= __( 'Daily', 'muzaara-woopf-bing' );
		$this->l10n->weekly				= __( 'Weekly', 'muzaara-woopf-bing' );
		$this->l10n->hourly				= __( 'Hourly', 'muzaara-woopf-bing' );
		$this->l10n->pushToGoogle			= __( 'Push to Bing', 'muzaara-woopf-bing' );
		$this->l10n->pushURL				= __( 'Generate URL', 'muzaara-woopf-bing' );
		$this->l10n->cancel				= __( 'Cancel', 'muzaara-woopf-bing' );
		$this->l10n->googleFields			= __( 'Product Fields', 'muzaara-woopf-bing' );
		$this->l10n->prefix				= __( 'Prefix', 'muzaara-woopf-bing' );
		$this->l10n->suffix				= __( 'Suffix', 'muzaara-woopf-bing' );
		$this->l10n->productField			= __( 'Product Field', 'muzaara-woopf-bing' );
		$this->l10n->fieldMapping			= __( 'Field Mapping', 'muzaara-woopf-bing' );
		$this->l10n->goBack				= __( 'Go back', 'muzaara-woopf-bing' );
		$this->l10n->categoryMapping			= __( 'Category Mapping', 'muzaara-woopf-bing' );
		$this->l10n->freeText				= __( 'Free Text?', 'muzaara-woopf-bing' );
		$this->l10n->remove				= __( 'Remove', 'muzaara-woopf-bing' );
		$this->l10n->addNewMapping			= __( 'Add New Mapping', 'muzaara-woopf-bing' );
		$this->l10n->productCategory			= __( 'Product Category', 'muzaara-woopf-bing' );
		$this->l10n->googleCategory			= __( 'Bing Category', 'muzaara-woopf-bing' );
		$this->l10n->enterToSearch			= __( 'Enter category name to search', 'muzaara-woopf-bing' );
		$this->l10n->catMappingDesc			= sprintf( __( "Map WooCommerce Categories to Google Product Categories. Enter in the below text input to search. More guide can be found here <a href='%s' target='_blank'>here</a>", 'muzaara-woopf-bing' ), 'https://support.google.com/merchants/answer/6324436?hl=en' );
		$this->l10n->filters				= __( 'Filters', 'muzaara-woopf-bing' );
		$this->l10n->if					= __( 'If', 'muzaara-woopf-bing' );
		$this->l10n->condition				= __( 'Condition', 'muzaara-woopf-bing' );
		$this->l10n->then				= __( 'Then', 'muzaara-woopf-bing' );
		$this->l10n->value				= __( 'Value', 'muzaara-woopf-bing' );
		$this->l10n->include				= __( 'Include', 'muzaara-woopf-bing' );
		$this->l10n->exclude				= __( 'Exclude', 'muzaara-woopf-bing' );
		$this->l10n->newFilter				= __( 'Add New Filter', 'muzaara-woopf-bing' );
		$this->l10n->rules				= __( 'Rules', 'muzaara-woopf-bing' );
		$this->l10n->addRule				= __( 'Add New Rule', 'muzaara-woopf-bing' );
		$this->l10n->becomes				= __( 'Becomes', 'muzaara-woopf-bing' );
		$this->l10n->saveContinue			= __( 'Save & Continue', 'muzaara-woopf-bing' );
		$this->l10n->noRules				= __( 'No rules created yet', 'muzaara-woopf-bing' );
		$this->l10n->googleAnalytics			= __( 'UTM Parameters', 'muzaara-woopf-bing' );
		$this->l10n->campaignSource			= __( 'Campaign Source', 'muzaara-woopf-bing' );
		$this->l10n->campaignMedium			= __( 'Campaign Medium', 'muzaara-woopf-bing' );
		$this->l10n->campaignTerm			= __( 'Campaign Term (use [product_id] to be replaced with product ID)', 'muzaara-woopf-bing' );
		$this->l10n->campaignContent			= __( 'Campaign Content', 'muzaara-woopf-bing' );
		$this->l10n->campaignCampaign			= __( 'Campaign Name', 'muzaara-woopf-bing' );
		$this->l10n->createChannel			= __( 'Create Channel', 'muzaara-woopf-bing' );
		$this->l10n->errorCheckRules			= __( 'Unable to proceed. Check Rules and fill missing fields', 'muzaara-woopf-bing' );
		$this->l10n->errorCheckFilters			= __( 'Unable to proceed. Check Filters and fill missing fields', 'muzaara-woopf-bing' );
		$this->l10n->errorCheckMaps			= __( 'Unable to proceed. Check field mapping and fill missing fields', 'muzaara-woopf-bing' );
		$this->l10n->channelSummary			= __( 'Channel Summary', 'muzaara-woopf-bing' );
		$this->l10n->includeProductTypes		= __( 'Product Types', 'muzaara-woopf-bing' );
		$this->l10n->productTypes			= __( 'Product Types', 'muzaara-woopf-bing' );
		$this->l10n->dateCreated			= __( 'Date Created', 'muzaara-woopf-bing' );
		$this->l10n->lastRefreshed			= __( 'Last Refreshed', 'muzaara-woopf-bing' );
		$this->l10n->running				= __( 'Running', 'muzaara-woopf-bing' );
		$this->l10n->paused				= __( 'Paused', 'muzaara-woopf-bing' );  
		$this->l10n->status				= __( 'Status', 'muzaara-woopf-bing' );
		$this->l10n->everyHours				= __( 'Every %d hours', 'muzaara-woopf-bing' );
		$this->l10n->country				= __( 'Country', 'muzaara-woopf-bing' );
		$this->l10n->nextRefresh			= __( 'Next Refresh Time', 'muzaara-woopf-bing' );
		$this->l10n->pause				= __( 'Pause', 'muzaara-woopf-bing' );
		$this->l10n->resume				= __( 'Resume', 'muzaara-woopf-bing' );
		$this->l10n->deleteConfirmation			= __( 'You are about to delete %s Channel. Continue?', 'muzaara-woopf-bing' );
		$this->l10n->edit				= __( 'Edit', 'muzaara-woopf-bing' );
		$this->l10n->editChannel			= __( 'Edit Channel', 'muzaara-woopf-bing' );
		$this->l10n->saveChanges			= __( 'Save Changes', 'muzaara-woopf-bing' );
		$this->l10n->savingChanges			= __( 'Saving Changes', 'muzaara-woopf-bing' );
		$this->l10n->creatingChannel			= __( 'Creating Channel', 'muzaara-woopf-bing' );
		$this->l10n->dumpURL        			= __( 'URL/Store ID', 'muzaara-woopf-bing' );
		$this->l10n->merchantId     			= __( 'Store ID', 'muzaara-woopf-bing' );
		$this->l10n->noticeEmail    			= __( 'Notification E-mail', 'muzaara-woopf-bing' );
		$this->l10n->runNow         			= __( 'Run Now', 'muzaara-woopf-bing' );
		$this->l10n->totalProducts  			= __( 'Total Products', 'muzaara-woopf-bing' );
		$this->l10n->searching      			= __( 'Searching...', 'muzaara-woopf-bing' );
		$this->l10n->catalogId				= __( 'Bing Catalog ID (optional)', 'muzaara-woopf' );
	}

	public function search_category($q, $fallback = false) {
		$taxonomies = get_transient( 'muzaara_woopf_bing_taxonomies' );

		do_action( 'muzaara_woopf_bing_before_tax_search', $q);

		if ( empty( $taxonomies ) ) {
			$req = wp_remote_get($fallback ? MUZAARA_WOOPF_BING_TAX_URL_FALLBACK : MUZAARA_WOOPF_BING_TAX_URL, array( 'sslverify' => false ));
			if ( !is_wp_error( $req ) ) {
				if ( $req[ 'response' ][ 'code' ] != 200 && !$fallback ) {
					$this->search_category($q, true);
				} else {
					if ( $req['body'] ) {
						foreach( explode( "\n", trim($req[ 'body' ]) ) as $line ) {
							if ( $line[0] == '#' )
								continue;

							$split = preg_split( '/\s\-\s/', $line);
							$taxonomies[$split[0]] = $split[1];
						}

						set_transient( 'muzaara_woopf_bing_google_categories', $taxonomies, 900 );
					} else {
						$taxonomies = array();
						// delete_transient( 'muzaara_woopf_bing_google_categories' );
					}
					
				}
			}
		}
		
		$ret = array();

		if ( $q && ( strlen($q) >= 3 || is_numeric($q ) ) ) {
			if ( is_numeric($q) && isset( $taxonomies[$q] ) ) {
				$ret = array( $taxonomies[$q] );
			} else {
				$ret = preg_grep( sprintf('/%s/i', preg_quote($q)), $taxonomies );
			}

			do_action( 'muzaara_woopf_bing_after_category_search', $ret, $q);
		}

		return apply_filters( 'muzaara_woopf_bing_category_search_results', $ret, $q );
	}

	public function includes() {
		// Abstracts
		require_once 'abstract/WooField.php';
		require_once 'abstract/Condition.php';
		require_once 'abstract/Feed.php';

		// Helpers
		require_once MUZAARA_WOOPF_BING_PATH . 'helpers/filters.php';
		require_once MUZAARA_WOOPF_BING_PATH . 'helpers/rules.php';
		require_once MUZAARA_WOOPF_BING_PATH . 'helpers/fields.php';
		require_once MUZAARA_WOOPF_BING_PATH . 'helpers/productfields.php';
		require_once MUZAARA_WOOPF_BING_PATH . 'helpers/feeds.php';
		require_once MUZAARA_WOOPF_BING_PATH . 'helpers/cron.php';

		// Mains
		require_once 'Ajax.php';
		require_once 'Channels.php';

		do_action( 'muzaara_woopf_bing_include_files' );
	}

	public function getInstance($classname) {
		if (empty($this->{$classname})) {
			$this->{$classname} = new $classname($this);
		}

		return $this->{$classname};
	}

	public function has_access() {
		return hasAccess( [ MUZAARA_BING_SCOPES[ 'ads' ] ] );
	}

	public static function activation() {
		if ( defined( 'MUZAARA_BING_FUNC_PATH' ) ) {
			require_once MUZAARA_BING_FUNC_PATH . 'plugins.php';

			addActive(MUZAARA_WOOPF_BING_BASE);
		}

		if ( !defined( 'MUZAARA_WOOPF_BING_DUMP_PATH' ) ) {
			trigger_error( __( 'WordPress upload path could not be determined. Please contact plugin support', 'muzaara-woopf-bing' ), E_USER_ERROR  );
		}

		if ( !wp_next_scheduled( MUZAARA_WOOPF_BING_CRON_ACTION ) ) {
			wp_schedule_event( time(), '3mins', MUZAARA_WOOPF_BING_CRON_ACTION );
		}
	}

	public static function deactivation() {
		if ( defined( 'MUZAARA_BING_FUNC_PATH' ) ) {
			require_once MUZAARA_BING_FUNC_PATH . 'plugins.php';

			removeActive(MUZAARA_WOOPF_BING_BASE);
			\Muzaara\Bing\Engine\Functions\Access\unlink();
		}

		if ( ( $timestamp = wp_next_scheduled( MUZAARA_WOOPF_BING_CRON_ACTION ) ) ) {
			wp_unschedule_event($timestamp, MUZAARA_WOOPF_BING_CRON_ACTION );
		}
	}

	private function actions() {
		add_action( 'admin_menu', array( $this, 'create_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_init', array($this, 'check_dep'));

	 
			add_action( 'init', array($this, 'register_post_type' ) );
			add_filter( 'manage_edit-product_columns', array( $this, 'add_wc_column' ) );
			add_action( 'manage_product_posts_custom_column', array($this, 'wc_col_val' ), 10, 2 );
			add_filter( 'cron_schedules', array($this, 'add_cron_schedules' ) );
			add_action( MUZAARA_WOOPF_BING_CRON_ACTION, '\Muzaara\Bing\ProductFeed\Helpers\processSchedules' );
			add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( $this, 'append_custom_field' ), 10, 2 );

			add_action( 'woocommerce_update_product', '\Muzaara\Bing\ProductFeed\Helpers\pushProduct' );
			add_action( 'before_delete_post', '\Muzaara\Bing\ProductFeed\Helpers\deleteProduct' );
			add_action( 'wp_trash_post', '\Muzaara\Bing\ProductFeed\Helpers\deleteProduct' );
		
	}

	public function append_custom_field( $query, $vars ) {
		if ( !empty( $vars[ 'custom_meta' ] ) ) {
			$query[ 'meta_query' ] = $vars[ 'custom_meta' ];
		}

		return $query;
	}

	public function add_cron_schedules( $schedules ) {
		$schedules[ '3mins' ] = array(
			'interval' => 3*60,
			'display' => __( 'Every 3 minutes', 'muzaara-woopf-bing' )
		);

		return $schedules;
	}

	public function add_wc_column($cols) {
		$n = [];
		foreach( $cols as $key => $name ) {
			$n[$key] = $name;
			if ( $key == 'product_cat' ) {
				$n[ 'matching_feeds' ] = __( 'Matching Feeds', 'muzaara-woopf-bing' );
			}
		}

		if ( !isset( $n[ 'matching_feeds' ] ) ) { // In case somehow they deleted the product_cat column
			$n[ 'matching_feeds' ] = __( 'Matching Feeds', 'muzaara-woopf-bing' );
		}

		return $n;
	}

	public function wc_col_val( $colname, $post_id ) {
		if ( $colname == 'matching_feeds' ) {
			$matches = array_map( function( $feed ) {
				return $feed->getName();
			}, findFeedMatch( $post_id ) );

			echo esc_html( implode( ', ', $matches) );
		}
	}

	public function register_post_type() {
		register_post_type(MUZAARA_WOOPF_BING_POST_TYPE, array(
			'public' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_in_rest' => false,
			'delete_with_user' => false
		));
	}

	public function check_dep() {
		if ( is_admin() && current_user_can('activate_plugins') && ( !defined( 'MUZAARA_BING_PATH' ) || !class_exists( 'woocommerce' ) ) ) {
			add_action( 'admin_notices', function() {
				?><div class='error'><p><?php printf( __( '%s plugin requires WooCommerce to be installed and active', 'muzaara-woopf-bing' ), MUZAARA_WOOPF_BING_BASE )?></p></div><?php 
			});

			deactivate_plugins( MUZAARA_WOOPF_BING_BASE );
			
			if ( isset( $_GET[ 'activate' ] ) ) 
				unset( $_GET[ 'activate' ] );
		}
	}

	public function enqueue( $hook ) {
		if ( !defined( 'MUZAARA_BING_FUNC_PATH' ) || ! in_array( $hook, [ 'toplevel_page_muzaara-woopf-bing', 'muzaara-product-feed_page_muzaara-woopf-bing' ] ) ) {
			return;
		}
		require_once MUZAARA_BING_FUNC_PATH . 'access.php';
		wp_enqueue_script( 'muzaara-woopf-bing', sprintf( '%sjs/muzaara-woopf-bing.js', MUZAARA_WOOPF_BING_ASSET_URL ), array(), null, true );
		wp_enqueue_style(  'muzaara-woopf-bing', sprintf( '%scss/admin.css', MUZAARA_WOOPF_BING_ASSET_URL ) );
		wp_localize_script( 'muzaara-woopf-bing', 'MUZAARA_WOOPF_BING', array(
			'ajax' => admin_url( 'admin-ajax.php' ),
			'l10n' => $this->l10n,
			'hasAccess' => $this->is_ready() ? 1 : 0,
			'oauthUrl' => generateOAuthURL(
				array( MUZAARA_BING_SCOPES[ 'ads' ] ),
				'bing'
			)
		));
	}

	public function is_ready() {
		return $this->has_access() && isManagerLinked();
	}

	public function create_menu() {
		global $menu;

		if ( defined( 'MUZAARA_BING_FUNC_PATH' ) ) {
			require_once MUZAARA_BING_FUNC_PATH . 'plugins.php';

			if ( isActive( 'muzaara-product-feed/muzaara-product-feed.php' ) || isActive( 'muzaara-google-content-api-data-feed/muzaara-product-feed.php' ) ) {
				add_submenu_page( 'muzaara-woopf', __( 'Google', 'muzaara-woopf-bing'), __( 'Google', 'muzaara-woopf-bing'), 'manage_options', 'muzaara-woopf' );
				add_submenu_page( 'muzaara-woopf', __( 'Bing', 'muzaara-woopf-bing' ), __( 'Bing', 'muzaara-woopf-bing' ), 'manage_options', 'muzaara-woopf-bing', array( $this, 'show_page' ) );
			} else {
				add_menu_page( __( 'Muzaara Bing Product Feed', 'muzaara-woopf-bing' ), __( 'Muzaara Bing Product Feed', 'muzaara-woopf-bing' ), 'manage_options', 'muzaara-woopf-bing', array( $this, 'show_page'), getMenuIconSvg() );
			}
		}
	}

	public function show_page() {
		require_once sprintf( '%stemplate/page.php', MUZAARA_WOOPF_BING_PATH );
	}
}
