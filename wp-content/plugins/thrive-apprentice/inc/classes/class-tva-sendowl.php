<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

final class TVA_SendOwl {
	/**
	 * Instance
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Connection
	 *
	 * @var mixed
	 */
	private static $connection;

	/**
	 * API Instance
	 *
	 * @var mixed
	 */
	private static $api;

	/**
	 * Sendowl Account Keys
	 *
	 * @var array
	 */
	private static $account_keys
		= array(
			'key'    => '',
			'secret' => '',
		);


	/**
	 * List of products
	 *
	 * @var $products
	 */
	private static $products = null;

	/**
	 * List of bundles
	 *
	 * @var $bundles
	 */
	private static $bundles = null;

	/**
	 * List of bundles and products
	 *
	 * @var $bundles
	 */
	private static $memberships = null;

	/**
	 * List of bundles and products
	 *
	 * @var $bundles
	 */
	private static $discounts = null;

	/**
	 * TVA_SendOwl constructor.
	 */
	private function __construct() {

		if ( class_exists( 'Thrive_Dash_List_Manager' ) && class_exists( 'Thrive_Dash_List_Connection_SendOwl' ) ) {
			self::$connection = Thrive_Dash_List_Manager::connectionInstance( 'sendowl' );
			if ( ! empty( self::$connection ) ) {
				self::$api = self::$connection->getApi();

				/**
				 * Set the account keys
				 */
				$default_keys = array(
					'key'    => '',
					'secret' => '',
				);

				$keys = get_option( 'tva_sendowl_account_keys', $default_keys );

				self::$account_keys = empty( $keys ) ? $default_keys : $keys;

				/**
				 * If the transients don't exist we will get 'false' here but we're not going to be handling
				 * this because the api responses will always return an array so we'll be either returning an
				 * empty array or an array of elements
				 */
				if ( self::$connection->isConnected() ) {
					$memberships = get_transient( 'thrive_sendowl_memberships' );
					if ( is_array( $memberships ) ) {
						self::$memberships = $memberships;
					}

					$products = get_transient( 'thrive_sendowl_products' );
					if ( is_array( $products ) ) {
						self::$products = $products;
					}

					$bundles = get_transient( 'thrive_sendowl_bundles' );
					if ( is_array( $bundles ) ) {
						self::$bundles = $bundles;
					}

					$discounts = get_transient( 'thrive_sendowl_discounts' );
					if ( is_array( $discounts ) ) {
						self::$discounts = $discounts;
					}
				}
			}
		}

	}

	/**
	 * Prevent cloning
	 */
	protected function __clone() {
	}

	/**
	 * Prevent wakeup
	 */
	protected function __wakeup() {
	}

	/**
	 * Return API
	 *
	 * @return mixed
	 */
	public static function get_api() {

		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		return self::$api;
	}

	/**
	 * Return the connection
	 *
	 * @return mixed|Thrive_Dash_List_Connection_SendOwl
	 */
	public static function get_connection() {

		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		return self::$connection;
	}


	/**
	 * @return null|TVA_SendOwl
	 */
	public static function instance() {
		// Check if instance is already exists
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		return self::$instance;
	}

	/**
	 * Return a list of products
	 *
	 * @param bool $forced
	 *
	 * @return array
	 */
	public static function get_products( $forced = false ) {
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		if ( self::is_connected() && ( null === self::$products || $forced ) ) {
			try {
				$sendowl_products = self::$api->getProducts( array( 'per_page' => 50 ) );
				self::$products   = $sendowl_products;

				$i = 2;
				while ( count( $sendowl_products ) === 50 ) {
					$sendowl_products = self::$api->getProducts( array( 'per_page' => 50, 'page' => $i ) );
					self::$products   = array_merge( self::$products, $sendowl_products );
					$i ++;
				}
			} catch ( Exception $e ) {

			}

			/**
			 * Set the transient for future use
			 */
			if ( is_array( self::$products ) ) {
				set_transient( 'thrive_sendowl_products', self::$products, WEEK_IN_SECONDS );
			}
		}
		if ( is_array( self::$products ) ) {
			foreach ( self::$products as $key => $product ) {
				self::$products[ $key ] = isset( $product['product'] ) ? $product['product'] : $product;
			}
		} else {
			self::$products = array();
		}


		return self::$products;
	}

	/**
	 * Return a product by it's ID
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function get_product_by_id( $id ) {
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		foreach ( self::get_products() as $product ) {
			if ( isset( $product['product'] ) ) {
				$product = $product['product'];
			}

			if ( (int) $product['id'] === (int) $id ) {
				return $product;
			}
		}

		return false;
	}

	/**
	 * Return a list of bundles
	 *
	 * @param bool $forced
	 *
	 * @return array
	 */
	public static function get_bundles( $forced = false ) {
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		if ( self::is_connected() && ( self::$bundles === null || $forced ) ) {
			try {
				$sendowl_bundles = self::$api->getBundles( array( 'per_page' => 50 ) );
				self::$bundles   = $sendowl_bundles;
				$i               = 2;
				while ( count( $sendowl_bundles ) === 50 ) {
					$sendowl_bundles = self::$api->getBundles( array( 'per_page' => 50, 'page' => $i ) );
					self::$bundles   = array_merge( self::$bundles, $sendowl_bundles );
					$i ++;
				}
			} catch ( Exception $e ) {
				self::$bundles = null;
			}

			/**
			 * Set the transient for future use
			 */
			if ( is_array( self::$bundles ) ) {
				set_transient( 'thrive_sendowl_bundles', self::$bundles, WEEK_IN_SECONDS );
			}
		}

		if ( is_array( self::$bundles ) ) {
			foreach ( self::$bundles as $key => $bundle ) {
				self::$bundles[ $key ] = isset( $bundle['package'] ) ? $bundle['package'] : $bundle;
			}
		} else {
			self::$bundles = array();
		}


		return self::$bundles;
	}

	/**
	 * Return a product by it's ID
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function get_bundle_by_id( $id ) {
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		foreach ( self::get_bundles() as $bundle ) {
			if ( isset( $bundle['package'] ) ) {
				$bundle = $bundle['package'];
			}

			if ( $bundle['id'] === (int) $id ) {
				return $bundle;
			}
		}

		return false;
	}

	/**
	 * Return the list of bundles and
	 *
	 * @param bool $forced
	 *
	 * @return array|mixed
	 */
	public static function get_memberships( $forced = false ) {
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}
		if ( self::is_connected() && ( null === self::$memberships || $forced ) ) {

			/**
			 * Get the products and bundles
			 */
			$sendowl_products = self::get_products( $forced );
			$sendowl_bundles  = self::get_bundles( $forced );

			$so_bundles  = array();
			$so_products = array();

			if ( ! empty( $sendowl_bundles ) ) {
				foreach ( $sendowl_bundles as $package ) {
					$so_bundles[] = array(
						'id'   => isset( $package['id'] ) ? $package['id'] : '',
						'name' => isset( $package['name'] ) ? $package['name'] : '',
					);
				}
			}

			if ( ! empty( $sendowl_products ) ) {
				foreach ( $sendowl_products as $product ) {
					$so_products[] = array(
						'id'   => isset( $product['id'] ) ? $product['id'] : '',
						'name' => isset( $product['name'] ) ? $product['name'] : '',
					);
				}
			}

			self::$memberships = array(
				'tag'               => TVA_Const::SENDOWL,
				'membership_levels' => $so_products,
				'bundles'           => $so_bundles,
			);

			/**
			 * Set the transient for future use
			 */
			set_transient( 'thrive_sendowl_memberships', self::$memberships, WEEK_IN_SECONDS );
			TVA_Sendowl_Manager::update_sendowl_products();
		}

		return self::$memberships;
	}

	/**
	 * Get the account keys
	 *
	 * @return array
	 */
	public static function get_account_keys() {
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		return self::$account_keys;
	}

	/**
	 * Set the account keys
	 *
	 * @param $keys
	 */
	public static function set_account_keys( $keys ) {
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		if ( is_array( $keys ) && isset( $keys['key'] ) && isset( $keys['secret'] ) ) {
			self::$account_keys = update_option( 'tva_sendowl_account_keys', $keys );
			self::$account_keys = $keys;
		}
	}

	/**
	 * Return the secret key
	 *
	 * @return string
	 */
	public static function get_account_keys_secret() {
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		return self::$account_keys['secret'];
	}

	/**
	 * Return the account key
	 *
	 * @return string
	 */
	public static function get_account_keys_key() {
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		return self::$account_keys['key'];
	}

	/**
	 * Check if sendowl is connected
	 *
	 * @return bool
	 */
	public static function is_connected() {
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		return self::$connection ? self::$connection->isConnected() : false;
	}

	/**
	 * Check if a lesson is protected by sendowl
	 *
	 * @param $lesson
	 *
	 * @return bool
	 */
	public static function is_lesson_protected_by_sendowl( $lesson ) {
		$return = false;
		if ( TVA_SendOwl::is_connected() ) {
			$restrictions = get_post_meta( $lesson->ID, 'tva_sendowl_restrictions', true );

			if ( ! empty( $restrictions ) ) {
				$return = true;
			}
		}

		return $return;
	}

	/**
	 * Return a list of discounts codes
	 *
	 * @param bool $forced
	 *
	 * @return array
	 */
	public static function get_discounts( $forced = false ) {
		if ( null === self::$instance ) {
			self::$instance = new TVA_SendOwl();
		}

		if ( self::is_connected() && ( null === self::$discounts || $forced ) ) {
			try {
				$sendowl_discounts = self::$api->getDiscounts( array( 'per_page' => 50 ) );
				self::$discounts   = $sendowl_discounts;
				$i                 = 2;

				while ( count( $sendowl_discounts ) === 50 ) {
					$args              = array(
						'per_page' => 50,
						'page'     => $i,
					);
					$sendowl_discounts = self::$api->getDiscounts( $args );
					self::$discounts   = array_merge( self::$discounts, $sendowl_discounts );

					$i ++;
				}
			} catch ( Exception $e ) {
				self::$discounts = null;
			}

			/**
			 * Set the transient for future use
			 */
			if ( is_array( self::$discounts ) ) {
				set_transient( 'thrive_sendowl_discounts', self::$discounts, WEEK_IN_SECONDS );
			}
		}

		self::$discounts = is_array( self::$discounts ) ? self::$discounts : array();

		foreach ( self::$discounts as $key => $discount ) {
			if ( ! empty( $discount['discount_code']['expires_at'] ) ) {
				$expired = strtotime( $discount['discount_code']['expires_at'] ) < strtotime( current_time( 'F d, Y h:i:s A' ) );

				if ( $expired ) {
					unset( self::$discounts[ $key ] );
				}
			}
		}

		return array_values( self::$discounts );
	}

	/**
	 * Localize Sendowl data
	 *
	 * @return array
	 */
	public function localize() {

		if ( ! TVA_SendOwl::is_connected() ) {
			return array();
		}

		return array(
			'discounts'              => self::get_discounts(),
			'bundles'                => self::get_bundles(),
			'products'               => self::get_products(),
			'thankyou_page'          => get_option( 'tva_thankyou_page', array() ),
			'checkout_page'          => get_option( 'tva_checkout_page', array() ),
			'thankyou_multiple_page' => get_option( 'tva_thankyou_multiple_page', array() ),
		);
	}
}
