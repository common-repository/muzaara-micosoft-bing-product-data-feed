<?php 
namespace Muzaara\Bing\ProductFeed\Object;
use function Muzaara\Bing\ProductFeed\Helpers\ProductFieldInit;
use function Muzaara\Bing\ProductFeed\Helpers\filterInit;
use function Muzaara\Bing\ProductFeed\Helpers\fieldInit;
use function Muzaara\Bing\ProductFeed\Helpers\ruleInit;
use function Muzaara\Bing\Engine\Functions\Access\getAccess;
use function Muzaara\Bing\Engine\Functions\Access\getDeveloperToken;

class BingFeed extends \Muzaara\Bing\ProductFeed\Abs\Feed {
	const PUSH_TO_GOOGLE = 1;
	const PUSH_TO_URL = 2;
	const BING_API_BASE = 'https://content.api.bingads.microsoft.com/shopping/v9.1/bmc/';
	const UPDATE_FIELDS = [ 'sale_price', 'price', 'availability', 'sale_price_effective_date' ];

	protected $type;
	protected $merchantId;
	protected $catalogId;
	protected $noticeEmail;

	protected $utm = array(
		'utm_source'    => '',
		'utm_term'      => '',
		'utm_content'   => '',
		'utm_medium'    => '',
		'utm_campaign'  => ''
	);

	public function __construct(int $id = 0) {
		
		parent::__construct($id);

		$this->setType( 'bing' );
	}

	public function setMerchantId( $id ) {
		$this->merchantId = $id;

		return $this;
	}

	public function setCatalogId( $id ) {
		$this->catalogId = $id;

		return $this;
	}

	public function setNoticeEmail( $email ) {
		if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$this->noticeEmail = $email;
			return true;
		}

		return false;
	}

	public function getCatalogId() {
		return apply_filters( 'muzaara_woopf_bing_get_feed_catalog_id', $this->catalogId, $this );
	}

	public function getMerchantId() {
		return apply_filters( 'muzaara_woopf_bing_get_feed_merchant_id', $this->merchantId, $this );
	}

	public function getNoticeEmail() {
		return apply_filters( 'muzaara_woopf_bing_get_feed_notice_email', (!$this->noticeEmail ? get_bloginfo( 'admin_email' ) : $this->noticeEmail), $this );
	}

	public function setUtm(array $utms) {
		foreach( $this->utm as $key => $value ) {
			$this->utm[ $key ] = !empty( $utms[$key] ) ? esc_attr(sanitize_text_field( $utms[ $key ] ) ) : '';
		}

		return $this;
	}

	public function getUtm( $product = null ) {
		$utm = $this->utm;
		if ( $product ) {
			$utm[ 'utm_term' ] = str_ireplace( '[product_id]', $product->get_id(), $this->utm[ 'utm_term' ] );
		}
		return apply_filters( 'muzaara_woopf_bing_get_feed_utm', $utm, $this );
	}

	public function setMapping(array $mapping) {
		if ( !empty( $mapping[ 'slug' ] ) ) {
			$field = new Field($mapping['slug'], '', $mapping[ 'type' ]);
			
			if ( $mapping[ 'type' ] == Field::CUSTOM_FIELD ) {
				$field->setValue($mapping['slug']);
			}

			if ( isset( $mapping[ 'prefix' ] ) ) {
				$field->setPrefix($mapping['prefix']);
			}

			if ( isset( $mapping[ 'suffix' ] ) ) {
				$field->setSuffix($mapping['suffix']);
			}

			if ( isset( $mapping[ 'gField' ] ) ) {
				$field->setProductField(
					ProductFieldInit( (object) array( 'slug' => $mapping[ 'gField' ] ) ) 
				);
			}

			$this->mappings[] = $field;
		}

		return $this;
	}

	public function setFilter(array $filter) {
		if ( !empty( $filter[ 'if' ] ) ) {
			$condition = filterInit($filter[ 'condition' ]);
			
			if ( $condition->getCondition() ) {
				$fieldA = fieldInit( $filter[ 'if' ], $filter[ 'if_type' ] );
				$fieldB = fieldInit( $filter[ 'value' ], $filter[ 'value_type' ] );

				$condition->setStmt($fieldA, $fieldB, $filter[ 'then' ] );
				$this->filters[] = $condition;
			}
		}
	}

	public function setRule(array $rule) {
		if ( !empty( $rule[ 'if' ] ) ) {
			$condition = ruleInit( $rule[ 'condition' ] );

			if ( $condition->getCondition() ) {
				$fieldA = fieldInit( $rule[ 'if' ], $rule[ 'if_type' ] );
				$fieldB = fieldInit( $rule[ 'value' ], $rule[ 'value_type' ] );
				$fieldC = fieldInit( $rule[ 'then' ], $rule[ 'then_type' ] );
				$fieldD = fieldInit( $rule[ 'is' ], $rule[ 'is_type'] );

				$condition->setStmt($fieldA, $fieldB, $fieldC, $fieldD );
				$this->rules[] = $condition;
			}
		}
	}

	public function executeFilters( $product ) {
		$ret = true;

		foreach( $this->filters as $filter ) {
			$ret = $filter->execute( $product ) == Filter::__INCLUDE__;
		}

		return $ret;
	}

	public function executeRules( $product ) {
		foreach( $this->getRules() as $rule ) {
			if ( $rule->execute( $product ) ) {
				$stmt = $rule->getStmt();

				foreach( $this->getMappings() as $index => $field) {
					if ( $field->getType() != Field::CUSTOM_FIELD && $field->getSlug() == $stmt->C->getSlug() ) {
						$newValue = new Field($stmt->D->getSlug(), '', $stmt->D->getType() );
						$newValue->setProductField( $this->mappings[$index]->getProductField() ); // Retain the Google field
						$newValue->setPrefix( $this->mappings[$index]->getPrefix() );
						$newValue->setSuffix( $this->mappings[$index]->getSuffix() );

						$this->mappings[$index] = $newValue;
					}
				}
			}
		}
	}

	public function getFilters() : array {
		return apply_filters( 'muzaara_woopf_bing_get_feed_filters', $this->filters, $this );
	}

	public function getRules() : array {
		return apply_filters( 'muzaara_woopf_bing_get_feed_rules', $this->rules, $this );
	}

	public function toArgs() {
		$postargs = array(
			'post_title' => $this->name,
			'post_status'       =>  $this->status,
			'post_type'         =>  MUZAARA_WOOPF_BING_POST_TYPE,
			'meta_input'        =>  array(
				'muzaara_woopf_bing_push_type'           =>  $this->pushType,
				'muzaara_woopf_bing_refresh_rate'        =>  $this->refreshRate,
				'muzaara_woopf_bing_country'             =>  $this->country,
				'muzaara_woopf_bing_category_mapping'    =>  $this->categoryMapping,
				'muzaara_woopf_bing_mappings'            =>  array(),
				'muzaara_woopf_bing_filters'             =>  array(),
				'muzaara_woopf_bing_rules'               =>  array(),
				'muzaara_woopf_bing_utm'                 =>  $this->utm,
				'muzaara_woopf_bing_feed_type'           =>  $this->type,
				'muzaara_woopf_bing_merchant_id'         =>  $this->merchantId,
				'muzaara_woopf_bing_catalog_id'         =>  $this->catalogId,
				'muzaara_woopf_bing_notice_email'        =>  $this->noticeEmail,
				'muzaara_woopf_bing_running_status'      =>  $this->runningStatus === true,
				'muzaara_woopf_bing_product_ids'         =>  $this->productIds
			)
		);

		
		if ( !$this->id || ( $this->id && $this->lastRefreshed ) ) {
			$postargs[ 'meta_input' ][ 'muzaara_woopf_bing_last_refreshed' ] = $this->lastRefreshed;
		}

		if ( $this->dumpURL ) {
			$postargs[ 'meta_input' ][ 'muzaara_woopf_bing_dump_url' ] = $this->dumpURL;
		}
		
		if ( $this->id ) {
			$postargs[ 'ID' ] = $this->id;
		}

		if ( $this->mappings ) {
			$mappings = array_map(function( $field ) {
				return array(
					'slug'      =>  $field->getSlug(),
					'type'      =>  $field->getType(),
					'prefix'    =>  $field->getPrefix(),
					'suffix'    =>  $field->getSuffix(),
					'gField'    =>  $field->getProductField()->getSlug()
				);
			}, $this->mappings );

			$postargs[ 'meta_input' ][ 'muzaara_woopf_bing_mappings' ] = $mappings;
		}

		if ( $this->filters ) {
			$filters = array_map(function( $filter ) {
				$stmt = $filter->getStmt();
				return array(
					'if'            =>  $stmt->A->getSlug(),
					'if_type'       =>  $stmt->A->getType(),
					'value'         =>  $stmt->B->getSlug(),
					'value_type'    =>  $stmt->B->getType(),
					'condition'     =>  $filter->getCondition(),
					'then'          =>  $stmt->istrue == Filter::__EXCLUDE__ ? 0 : 1
				);
			}, $this->filters );

			$postargs[ 'meta_input' ][ 'muzaara_woopf_bing_filters' ] = $filters;
		}

		if ( $this->rules ) {
			$rules = array_map(function( $rule ) {
				$stmt = $rule->getStmt();
				return array(
					'if'            =>  $stmt->A->getSlug(),
					'if_type'       =>  $stmt->A->getType(),
					'value'         =>  $stmt->B->getSlug(),
					'value_type'    =>  $stmt->B->getType(),
					'then'          =>  $stmt->C->getSlug(),
					'then_type'     =>  $stmt->C->getType(),
					'is'            =>  $stmt->D->getSlug(),
					'is_type'       =>  $stmt->D->getType(),
					'condition'     =>  $rule->getCondition()
				);
			}, $this->rules );

			$postargs[ 'meta_input' ][ 'muzaara_woopf_bing_rules' ] = $rules;
		}

		return apply_filters( 'muzaara_woopf_bing_feed_to_postargs', $postargs, $this );
	}

	public function save( $push_products = false ) {
		$postargs = $this->toArgs();
		$post_id = 0;

		do_action( 'muzaara_woopf_bing_before_feed_create', $this );
		if ( !$this->id ) {
			$post_id = wp_insert_post( $postargs, true );
			
		} else {
			$post_id = wp_update_post( $postargs, true );
		}

		if ( !is_wp_error( $post_id ) && $post_id ) {
			/* 
				Because we need to add duplicate key of product types so as to be able to do a meta_query in WP_Query for faster product matching; we need to add it separately, as duplicate field is not supported in $postarr. So, product type will not appear in $this->toArgs()
			*/

			delete_post_meta( $post_id, 'muzaara_woopf_bing_product_types' ); // delete and add again to prevent duplicates

			foreach( $this->productTypes as $productType ) {
				add_post_meta($post_id, 'muzaara_woopf_bing_product_types', $productType, false);
			}

			$this->id = $post_id;
			if ( $this->pushType == 1 && $postargs[ 'post_status' ] == 'publish' && $push_products ) {
				$this->pushBatchProducts();
			}
		}

		do_action( 'muzaara_woopf_bing_after_feed_create', $this );

		return $post_id;
	}

	public function findProducts( $exclude_already_pushed = true ) : array {
		$args = array(
			'limit'     =>  -1,
			'status'    =>  'publish',
			'type'      =>  $this->getProductTypes()
		);

		if ( $this->pushType == 1 && $exclude_already_pushed ) { // It pushes to Google 
			$args[ 'custom_meta' ] = array(
				array(
					'key'       =>  'muzaara_woopf_bing_content_id',
					'value'     =>  'foo', // The bug https://core.trac.wordpress.org/ticket/23268
					'compare'   =>  'NOT EXISTS'
				)
			);
		}

		$query = new \WC_Product_Query( $args );

		$products = array_filter($query->get_products(), array( $this, 'executeFilters' ));
		if ( in_array( 'variable', $this->getProductTypes() ) ) {
				foreach ( $products as $index => $product ) {
						if ( $product->is_type( 'variable' ) ) {
								unset( $products[ $index ] );
								$products = array_merge( $products, $product->get_available_variations( 'objects' ) );
						}
				}
		}

		return apply_filters( 'muzaara_woopf_bing_find_feed_products', $products, $this );
	}

	private function generateFeedContent( $products, $fp ) {
		$mappings = $this->getMappings();

		if ( $mappings ) {
			$header = array_map( function( $field ) {
				$slug = $field->getProductField()->getSlug();
				return $slug;
			}, $mappings );

			$data = sprintf( "%s\n", implode( "\t", $header ) );
			fwrite( $fp, $data );

			foreach( $products as $product ) {
				$hasCat = false;
				$this->executeRules( $product );
				$line_raw_buf = array();

				foreach( $mappings as $field ) {
					$value = sprintf( '%s%s%s', $field->getPrefix(), $field->getValue( $product ), $field->getSuffix() );
					$productField = $field->getProductField()->getSlug();

					if ( $field->getSlug() == 'link' ) {
						$utms = array_filter( $this->getUtm( $product ) );
						if ( $utms )
							$value = sprintf( '%s?%s', $value, http_build_query( $utms ) );
					}
					
					if ( in_array( $productField, [ 'price', 'sale_price' ] ) ) {
						$value = sprintf( '%s %s', $value, get_woocommerce_currency() );
					} else if ( in_array( $productField, ['shipping_width', 'shipping_length' ] ) ) {
						$value = sprintf( '%s %s', $value, get_option('woocommerce_dimension_unit') );
					} else if ( $productField == 'shipping_weight' ) {
						$value = sprintf( '%s %s', $value, get_option('woocommerce_weight_unit') );
					}

					$line_raw_buf[] = $value;

					if ( $field->getSlug() == 'product_category' ) {
						$hasCat = true;
					}
				}

				$line_buf = sprintf( "%s\n", implode( "\t", $line_raw_buf ) );
				fwrite( $fp, $line_buf );

				// if ( !$hasCat ) {
				// 	$terms = get_the_terms( $product->get_id(), 'product_cat' );
				// 	$mappedCategories = $this->getCategoryMapping();
				// 	$terms = array_filter( $terms, function( $term ) use ( $mappedCategories ) {
				// 		$ret = array_filter( $mappedCategories, function( $category ) use ( $term ) {
				// 			return $category[ 'term_id' ] == $term->term_id;
				// 		});

				// 		return !empty( $ret );
				// 	});

				// 	if ( $terms ) {
				// 		$mappedCategory = array_filter($mappedCategories, function( $m ) use( $terms ) {
				// 			return $m[ 'term_id' ] = $terms[0]->term_id;
				// 		});

				// 		$xw->startElementNs( 'g', 'product_category', null );
				// 		$xw->text( $mappedCategories[0][ 'category' ] );
				// 		$xw->endElement();
						
				// 	}
				// }
			}
		}

	}

	public function generateDump() {
		set_time_limit(0);
		$products = $this->findProducts();
		$filename = strtolower(sprintf( '%s_%d.txt', preg_replace( '/[^\w\d]/', '_', $this->getName() ), $this->getId() ));
		$this->setRunningStatus( true )->save();

		if ( !file_exists( MUZAARA_WOOPF_BING_DUMP_PATH ) ) {
			mkdir(MUZAARA_WOOPF_BING_DUMP_PATH);
		}

		$filepath = sprintf( '%s%s', MUZAARA_WOOPF_BING_DUMP_PATH, $filename );

		$fp = fopen($filepath, 'w');
		$xw = $this->generateFeedContent($products, $fp );

		fclose($fp);

		$this->setDumpURL( sprintf( '%s%s', MUZAARA_WOOPF_BING_DUMP_URL, $filename ) );
		$this->setRunningStatus( false );
		$affected_products = array_map( function( $product ) { return $product->get_id(); }, $products );
		$this->setProductIds( $affected_products );
		$this->setLastRefreshed(time())->save();
	}

	public function createProductData( $wc_product ) : array {
		$this->executeRules( $wc_product );
		$existingId = $wc_product->get_meta( 'muzaara_woopf_bing_content_id', true );
		$data = array();

		foreach( $this->getMappings() as $field ) {
			$productField = $field->getProductField()->getSlug();

			if ( $existingId && ! in_array( $productField, self::UPDATE_FIELDS ) ) {
				continue;
			}

			$value = sprintf( '%s%s%s', $field->getPrefix(), $field->getValue( $wc_product ), $field->getSuffix() );

			if ( in_array( $productField, array( 'sale_price', 'price' ) ) ) {
				$value = array(
					'value'		=> $value,
					'currency'	=> get_woocommerce_currency(),
				);
			} else if ( in_array( $productField, array( 'shipping_weight' ) ) ) {
				$value = array(
					'value' => $value,
					'unit'	=> get_option('woocommerce_weight_unit'),
				);
			}

			$data[ $this->toCamelCase( $productField ) ] = $value;
		}

		if ( ! $existingId ) {
			$data['offerId']		= $wc_product->get_id();
			$data['targetCountry']		= $this->getCountry();
			$data['contentLanguage']	= strstr( get_locale(), '_', true );
			$data['channel']		= 'online';
		}

		return $data;
	}

	private function toCamelCase( string $str ) {
		return lcfirst( str_replace( '_', '', ucwords( $str, '_' ) ) );
	}

	private function sendError( string $error ) {
		$err = __( "Some product(s) encountered error while trying to push to Bing Content API. Error log attached", 'muzaara-woopf-bing' );

		$filepath = sprintf( '%s/%d.txt', get_temp_dir(), mt_rand() );
		$fp = fopen( $filepath, 'w+' );

		if ( $fp ) {
			fwrite( $fp, $error );
			fclose( $fp );
			wp_mail(
				$this->getNoticeEmail(),
				sprintf( __( 'Error pushing Bulk Product using Bing Content API', 'muzaara-woopf-bing' ) ),
				$err,
				'',
				[ $filepath ]
			);

			@unlink( $filepath );
		}
	}

	public function pushBatchProducts( $products = null ) {
		if ( null === $products ) {
			$products = $this->findProducts();
		}
		$access_token = getAccess();
		$dev_token = getDeveloperToken();

		if ( ! $access_token || ! $dev_token || ! $products ) {
			return;
		}
		
		$entries = array();
		foreach( $products as $index => $product ) {
			$entry = $this->createProductData( $product );

			$entries[] = array(
				'method'	=> 'insert',
				'product'	=> $entry,
				'merchantId'	=> $this->getMerchantId(),
				'batchId'	=> $index,
			);
		} 

		$url = sprintf( '%s%s/products/batch', self::BING_API_BASE, $this->getMerchantId() );

		if ( $this->getCatalogId() ) {
			$url .= '?bmc-catalog-id=' . $this->getCatalogId();
		}

		$batchRequest = wp_remote_post( $url,
			array(
				'body'		=> wp_json_encode( array( 'entries' => $entries ) ),
				'timeout'	=> 60,
				'compress'	=> true,
				'data_format'	=> 'body',
				'headers'	=> array(
					'Content-Type'		=> 'application/json',
					'DeveloperToken'	=> $dev_token,
					'AuthenticationToken'	=> $access_token['access_token'],
				)
			)
		);

		$body = json_decode( wp_remote_retrieve_body( $batchRequest ) );

		if ( $body && $body->entries ) {
			$err = '';
			foreach ( $body->entries as $entry_response ) {
				if ( ! empty( $entry_response->errors->errors ) ) {
					$err .= sprintf(
						"Product ID: #%d\n=====\n%s\n\n",
						$products[ $entry_response->batchId ]->get_id(),
						json_encode( $entry_response->errors->errors, JSON_PRETTY_PRINT )
					);
					continue;
				}

				$products[ $entry_response->batchId ]->update_meta_data(
					'muzaara_woopf_bing_content_id',
					sanitize_text_field( $entry_response->product->id )
				);
				$products[ $entry_response->batchId ]->save_meta_data();
				$productIds[] = $products[ $entry_response->batchId ]->get_id();
			}

			if ( $err ) {
				$this->sendError( $err );
			}
		}
	}

	public function deleteProduct( $product_id ) {
		$product = wc_get_product( $product_id );
		$access_token = getAccess();
		$dev_token = getDeveloperToken();
		if ( $product && $access_token ) {
			$id = $product->get_meta( 'muzaara_woopf_bing_content_id', true );
			if ( $id ) {
				$url = $url = sprintf( '%s%s/products/%s', self::BING_API_BASE, $this->getMerchantId(), $id );

				$request = wp_remote_request( $url, array(
					'method'	=>	'DELETE',
					'timeout'	=>	60,
					'headers'	=>	array(
						'DeveloperToken'	=> $dev_token,
						'AuthenticationToken'	=> $access_token['access_token'],
					)
				) );
			}
		}
	}

	public function pushProduct( $product_id ) {
		$product = wc_get_product( $product_id );
		$merchantId = $this->getMerchantId();
		$access_token = getAccess();
		$dev_token = getDeveloperToken();
		$existingId = $product->get_meta( 'muzaara_woopf_bing_content_id', true );

		if ( $this->getPushType() == 1 && ! empty( $access_token ) && $product && $merchantId ) {
			if ( $product->is_type( 'variable' ) ) {
				$variations = $product->get_available_variations( 'objects' );
				if ( $variations )
					$this->pushBatchProducts( $variations );
			} else {
				$url = $url = sprintf( '%s%s/products', self::BING_API_BASE, $this->getMerchantId() );

				if ( $existingId ) {
					$url .= '/' . $existingId;
				} elseif ( $this->getCatalogId() ) {
					$url .= '?bmc-catalog-id=' . $this->getCatalogId();
				}

				$this->setRunningStatus( true )->save();

				$request = wp_remote_post( $url,
					array(
						'body'		=> wp_json_encode( $this->createProductData( $product ) ),
						'timeout'	=> 60,
						'data_format'	=> 'body',
						'headers'	=> array(
							'Content-Type'		=> 'application/json',
							'DeveloperToken'	=> $dev_token,
							'AuthenticationToken'	=> $access_token['access_token'],
						)
					)
				);

				$body = json_decode( wp_remote_retrieve_body( $request ) );

				if ( $body ) {
					if ( ! empty( $body->error->errors ) ) {
						$this->sendError(
							sprintf(
								"Product ID: #%d\n=====\n%s\n",
								$product->get_id(),
								json_encode( $body->error, JSON_PRETTY_PRINT )
							)
						);
					} else {
						if ( ! $existingId ) {
							$product->update_meta_data( 'muzaara_woopf_bing_content_id', sanitize_text_field( $body->id ) );
							$product->save_meta_data();
						}
						$this->setProductId( $product->get_id() );
					}
				}

				$this->setRunningStatus( false )->save();
			}
		}
	}
}