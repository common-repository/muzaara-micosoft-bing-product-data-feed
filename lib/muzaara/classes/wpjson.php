<?php 
namespace Muzaara\Bing\Engine;
use function \Muzaara\Bing\Engine\Functions\Access\getSiteHash;
use function \Muzaara\Bing\Engine\Functions\Access\addAccess;

if ( !class_exists(__NAMESPACE__ . "\WPJSON" ) ) {
	class WPJSON {
		public function __construct() {
			$this->hook();
		}

		private function hook() {
			add_action( "rest_api_init", array( $this, "register_routes"));
		}

		public function register_routes() {
				\register_rest_route( "muzaara/bing/v1", "access", array(
					"methods" => "POST",
					"callback" => array( $this, "add_access" ),
					"args" => array(
						"access" => array(
							"required" => true
						),
						"scopes" => array(
							"required" => true
						),
						"key" => array(
							"required" => true
						)
					)
				) );

				\register_rest_route( "muzaara/bing/v1", "devKey", array(
					"methods" => "POST",
					"callback" => array( $this, "setDevKey" ),
					"args" => array(
						"dev_key" => array(
							"required" => true
						)
					)
				) );
		}
		
		public function setDevKey( \WP_REST_Request $request )
		{
			set_transient( "muzaara_bing_dev_key", $request[ "dev_key" ], HOUR_IN_SECONDS );
		}

		public function add_access( \WP_REST_Request $request )
		{
			$access = unserialize( $request[ "access" ] );
			$scopes = !empty($request["scopes"]) ? unserialize($request["scopes"]) : array();
			$key = getSiteHash();

			if( !$access ) {
				return new \WP_Error( "invalid data", "Invalid data sent" );
			}

			if( $key != $request[ "key" ] || rest_url( "muzaara/bing" ) !== $access[ "api" ] ) {
				return new \WP_Error( "verification_mismatch", "Invalid key" );
			}

			if( empty( $access[ "access_token" ] ) || empty( $access[ "expires_in" ] ) || empty( $access[ "scope" ] ) || empty( $access[ "token_type" ] ) || empty( $access[ "created" ] ) ) {
				return new \WP_Error( "invalid_access_token", "Invalid Access Token" );
			}

			addAccess($access, $scopes);
			do_action( "muzaara_access_added", $access );
			// $this->init->gapi->getAccountData();
			return new \WP_REST_Response( $access );
		}
	}
}
