<?php

use SkyVerge\WooCommerce\Facebook;

/**
 * Tests the Products class.
 */
class Products_Test extends \Codeception\TestCase\WPTestCase {


	/** @var \IntegrationTester */
	protected $tester;

	/** @var int excluded product category ID */
	private $excluded_category;


	/**
	 * Runs before each test.
	 */
	protected function _before() {

		$this->add_excluded_category();
	}


	/**
	 * Runs after each test.
	 */
	protected function _after() {

	}


	/** Test methods **************************************************************************************************/


	/** @see Facebook\Products::product_should_be_synced() */
	public function test_product_should_be_synced_simple() {

		$product = $this->get_product();

		$this->assertTrue( Facebook\Products::product_should_be_synced( $product ) );
	}


	/** @see Facebook\Products::product_should_be_synced() */
	public function test_product_should_be_synced_simple_in_excluded_category() {

		$product = $this->get_product();
		$product->set_category_ids( [ $this->excluded_category ] );

		$this->assertFalse( Facebook\Products::product_should_be_synced( $product ) );
	}


	/** @see Facebook\Products::product_should_be_synced() */
	public function test_product_should_be_synced_variation_in_excluded_category() {

		$product = $this->get_variable_product();
		$product->set_category_ids( [ $this->excluded_category ] );
		$product->save();

		foreach ( $product->get_children() as $child_id ) {
			$this->assertFalse( Facebook\Products::product_should_be_synced( wc_get_product( $child_id ) ) );
		}
	}


	/** @see Facebook\Products::enable_sync_for_products() */
	public function test_enable_sync_for_products() {

		$product = $this->get_product();

		Facebook\Products::disable_sync_for_products( [ $product ] );
		Facebook\Products::enable_sync_for_products( [ $product ] );

		// get a fresh product object to ensure the status is stored
		$product = wc_get_product( $product->get_id() );

		$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( $product ) );
	}


	/** @see Facebook\Products::enable_sync_for_products() for variable products  */
	public function test_enable_sync_for_products_variable() {

		$variable_product = $this->get_variable_product();

		Facebook\Products::disable_sync_for_products( [ $variable_product ] );
		Facebook\Products::enable_sync_for_products( [ $variable_product ] );

		// get a fresh product object to ensure the status is stored
		$variable_product = wc_get_product( $variable_product->get_id() );

		$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( $variable_product ) );
	}


	/** @see Facebook\Products::enable_sync_for_products() for variations  */
	public function test_enable_sync_for_products_variation() {

		$variable_product = $this->get_variable_product();

		Facebook\Products::disable_sync_for_products( [ $variable_product ] );
		Facebook\Products::enable_sync_for_products( [ $variable_product ] );

		// get a fresh product object to ensure the status is stored
		$variable_product = wc_get_product( $variable_product->get_id() );

		foreach ( $variable_product->get_children() as $child_product_id ) {
			$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( wc_get_product( $child_product_id ) ) );
		}
	}


	/** @see Facebook\Products::disable_sync_for_products() */
	public function test_disable_sync_for_products() {

		$product = $this->get_product();

		Facebook\Products::enable_sync_for_products( [ $product ] );
		Facebook\Products::disable_sync_for_products( [ $product ] );

		// get a fresh product object to ensure the status is stored
		$product = wc_get_product( $product->get_id() );

		$this->assertFalse( Facebook\Products::is_sync_enabled_for_product( $product ) );
	}


	/** @see Facebook\Products::disable_sync_for_products() for variable products */
	public function test_disable_sync_for_products_variable() {

		$variable_product = $this->get_variable_product();

		Facebook\Products::enable_sync_for_products( [ $variable_product ] );
		Facebook\Products::disable_sync_for_products( [ $variable_product ] );

		// get a fresh product object to ensure the status is stored
		$variable_product = wc_get_product( $variable_product->get_id() );

		$this->assertFalse( Facebook\Products::is_sync_enabled_for_product( $variable_product ) );
	}


	/** @see Facebook\Products::disable_sync_for_products() for variations */
	public function test_disable_sync_for_products_variation() {

		$variable_product = $this->get_variable_product();

		Facebook\Products::enable_sync_for_products( [ $variable_product ] );
		Facebook\Products::disable_sync_for_products( [ $variable_product ] );

		// get a fresh product object to ensure the status is stored
		$variable_product = wc_get_product( $variable_product->get_id() );

		foreach ( $variable_product->get_children() as $child_product_id ) {
			$this->assertFalse( Facebook\Products::is_sync_enabled_for_product( wc_get_product( $child_product_id ) ) );
		}
	}


	/** @see Facebook\Products::is_sync_enabled_for_product() for products that don't have a preference set */
	public function test_is_sync_enabled_for_product_defaults() {

		$product = $this->get_product();

		$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( $product ) );

		$variable_product = $this->get_variable_product();

		$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( $this->get_variable_product() ) );

		foreach ( $variable_product->get_children() as $child_product_id ) {
			$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( wc_get_product( $child_product_id ) ) );
		}
	}


	/** Helper methods ************************************************************************************************/


	/**
	 * Gets a new product object.
	 *
	 * @return \WC_Product
	 */
	private function get_product() {

		$product = new \WC_Product();
		$product->save();

		return $product;
	}


	/**
	 * Gets a new variable product object, with variations.
	 *
	 * @param int|int[] $children array of variation IDs, if unspecified will generate the amount passed (default 3)
	 * @return \WC_Product_Variable
	 */
	private function get_variable_product( $children = [] ) {

		$product = new \WC_Product_Variable();

		$product->save();

		$variations = [];

		if ( empty( $children ) || is_numeric( $children ) ) {

			$default_variations = 3;
			$total_variations   = 0 !== $children && empty( $children ) ? $default_variations : max( 0, (int) $children );

			for ( $i = 0; $i < $total_variations; $i++ ) {

				$variation = new \WC_Product_Variation();
				$variation->set_parent_id( $product->get_id() );
				$variation->save();

				$variations[] = $variation->get_id();
			}
		}

		$product->set_children( $variations );
		$product->save();

		return $product;
	}


	/**
	 * Adds and excluded category.
	 */
	private function add_excluded_category() {

		$category = wp_insert_term( 'Excluded category', 'product_cat' );

		$this->excluded_category = $category['term_id'];

		$settings = [
			'fb_sync_exclude_categories' => [ $this->excluded_category ]
		];

		update_option( 'woocommerce_' . \WC_Facebookcommerce::INTEGRATION_ID . '_settings', $settings );

		// ensure the settings are reloaded before tests
		facebook_for_woocommerce()->get_integration()->init_settings();
	}


}

