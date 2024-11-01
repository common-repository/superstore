<?php
namespace Binarithm\Superstore\Widgets;

use Binarithm\Superstore\Traits\Container;

/**
 * Superstore widgets controller class
 */
class Controller {
	use Container;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$widgets = apply_filters(
			'superstore_widgets',
			array(
				'best_selling_products' => 'Binarithm\Superstore\Widgets\BestSellingProducts',
				'top_rated_products'    => 'Binarithm\Superstore\Widgets\TopRatedProducts',
				'featured_products'     => 'Binarithm\Superstore\Widgets\FeaturedProducts',
				'latest_products'       => 'Binarithm\Superstore\Widgets\LatestProducts',
			)
		);

		foreach ( $widgets as $widget_id => $widget_class ) {
			register_widget( $widget_class );
		}

		$this->container = $widgets;
	}

	/**
	 * Check if widget class exists
	 *
	 * @param string $widget_id Widget ID.
	 * @return bool
	 */
	public function is_exists( $widget_id ) {
		return isset( $this->container[ $widget_id ] ) && class_exists( $this->container[ $widget_id ] );
	}

	/**
	 * Get widget id from widget class
	 *
	 * @param string $widget_class Widget class.
	 * @return bool|string Returns widget id if found, outherwise returns false
	 */
	public function get_id( $widget_class ) {
		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		return array_search( $widget_class, $this->container );
	}
}
