<?php
namespace Muzaara\Bing\Engine\Functions\Bing;

use \Microsoft\BingAds\Auth\OAuthWebAuthCodeGrant;
use \Microsoft\BingAds\Auth\AuthorizationData;
use \Microsoft\BingAds\Auth\OAuthTokens;
use \Microsoft\BingAds\V13\CustomerManagement\GetCustomersInfoRequest;
use \Microsoft\BingAds\V13\CustomerManagement\GetAccountsInfoRequest;
use \Microsoft\BingAds\Auth\ServiceClient;
use \Microsoft\BingAds\Auth\ServiceClientType;
use \Microsoft\BingAds\Auth\ApiEnvironment;
use function \Muzaara\Bing\Engine\Functions\Access\getAccess;
use function \Muzaara\Bing\Engine\Functions\Access\getDeveloperToken;
use function \Muzaara\Bing\Engine\Functions\Access\getSiteHash;

require_once MUZAARA_BING_LIB_PATH . 'BingAds-PHP-SDK/vendor/autoload.php';

if ( ! function_exists( __NAMESPACE__ . '\getCustomers' ) ) {
	function getAccounts() {
		$client = getClient( ServiceClientType::CustomerManagementVersion13 );

		if ( ! $client ) {
			return;
		}

		// $request = new GetCustomersInfoRequest();
		// $request->CustomerNameFilter = '';
		// $request->TopN = 5000;
		$request = new GetAccountsInfoRequest();
		$request->CustomerId = null;
		$request->OnlyParentAccounts = false;

		$response = $client->GetService()->GetAccountsInfo( $request );

		return $response;
	}
}

if ( ! function_exists( __NAMESPACE__ . '\getClient' ) ) {
	function getClient( $client_type ) {
		$access_token = getAccess();

		if ( $access_token ) {
			$oauthTokens = toOAuth( $access_token );
			$authentication = ( new OAuthWebAuthCodeGrant() )
				->withOAuthTokens( $oauthTokens );

			$authorizationData = ( new AuthorizationData() )
				->withAuthentication( $authentication )
				->withDeveloperToken( getDeveloperToken() );

			$serviceClient = new ServiceClient( $client_type, $authorizationData, ApiEnvironment::Production );
			return $serviceClient;
		}
	}
}

if ( ! function_exists( __NAMESPACE__ . '\toOAuth' ) ) {
	function toOAuth( array $access_token ) {
		$instance = ( new OAuthTokens() )
			->withAccessToken( $access_token['access_token'] )
			->withAccessTokenExpiresInSeconds( $access_token['expires_in' ] )
			->withResponseFragments( $access_token );

		return $instance;
	}
}

if ( ! function_exists( __NAMESPACE__ . '\sendInvitation' ) ) {
	function sendInvitation( $account_id ) {
		$siteHash = getSiteHash();

		$req = wp_remote_post( sprintf( '%sbing_invite_accept', MUZAARA_BING_API_URL ), array(
			'sslverify'	=> false,
			'timeout'	=> 120,
                        'body'		=> array(
				'account_id'	=> $account_id,
				'key'		=> $siteHash,
			),
		) );

		return wp_remote_retrieve_body( $req );
	}
}