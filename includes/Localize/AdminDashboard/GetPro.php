<?php

namespace Binarithm\Superstore\Localize\AdminDashboard;

/**
 * Superstore admin dashboard localize get pro class
 */
class GetPro {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'superstore_admin_localize_data', array( $this, 'add_data' ) );
	}

	/**
	 * Add data
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public function add_data( $data ) {
		$data['get_pro'] = array(
			'upcoming'     => array(
				'title'       => __( 'Superstore PRO is Upcoming!!!', 'superstore' ),
				'notify_desc' => __( 'Notify me on launch date and get <span class="font-weight-bold">First Launch</span> exclusive offers!!!', 'superstore' ),
				'email_label' => __( 'Enter your email', 'superstore' ),
				'btn_title'   => __( 'Notify Me', 'superstore' ),
			),
			'top_features' => $this->get_top_features(),
			'comparison'   => $this->get_comparison(),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_get_pro_data', $data );
	}

	/**
	 * Get top pro features
	 *
	 * @return array
	 */
	public function get_top_features() {
		$data = array(
			'section_title' => __( 'TOP 16 PRO FEATURES', 'superstore' ),
			'see_more'      => array(
				'title' => __( 'See more features' ),
				'link'  => 'https://binarithm.com/superstore',
			),
			'content'       => array(
				array(
					'title'       => 'Seller level upgrade',
					'description' => 'Get genuine and professional sellers by upgrading seller level to Bronze, Silver or Gold and set level-wise different facilities.',
					'icon'        => 'mdi-trophy',
					'iconColor'   => 'rgba(255, 0, 102, .7)',
					'btnColor'    => 'rgba(255, 0, 102, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/seller-level',
				),
				array(
					'title'       => 'Level-wise commission',
					'description' => 'Admin can set different commission rates for different seller levels (Example=> bronze 10%, silver 15%, gold 20%).',
					'icon'        => 'mdi-sack-percent',
					'iconColor'   => 'rgba(102, 0, 255, .7)',
					'btnColor'    => 'rgba(102, 0, 255, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/commission',
				),
				array(
					'title'       => 'Level-wise automatic payment',
					'description' => 'Send seller earnings instantly, schedully, or on seller request and system can be set level-wise also.',
					'icon'        => 'mdi-credit-card-clock',
					'iconColor'   => 'rgba(255, 64, 0, .7)',
					'btnColor'    => 'rgba(255, 64, 0, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/payment',
				),
				array(
					'title'       => 'Popular payment gateways',
					'description' => 'Add paypal, stripe, amazon pay, apple pay, google pay and many more popular payment gateways.',
					'icon'        => 'mdi-credit-card',
					'iconColor'   => 'rgba(0, 204, 170, .7)',
					'btnColor'    => 'rgba(0, 204, 170, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/payment-gateways',
				),
				array(
					'title'       => 'Shipping and order tracking',
					'description' => 'This feature can manage orders live tracking, delivery with DHL, Shipstation etc',
					'icon'        => 'mdi-truck-fast',
					'iconColor'   => 'rgba(153, 0, 255, .7)',
					'btnColor'    => 'rgba(153, 0, 255, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/shipping',
				),
				array(
					'title'       => 'Return and warranty',
					'description' => 'Return and warranty feature for your marketplace.',
					'icon'        => 'mdi-sync-circle',
					'iconColor'   => 'rgba(0, 180, 224, .7)',
					'btnColor'    => 'rgba(0, 180, 224, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/return-and-warranty',
				),
				array(
					'title'       => 'Reports and analytics',
					'description' => 'A detail reports and analytics for admin and seller both. Google analytics is also provided.',
					'icon'        => 'mdi-chart-areaspline',
					'iconColor'   => 'rgba(255, 0, 162, .7)',
					'btnColor'    => 'rgba(255, 0, 162, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/reports-and-analytics',
				),
				array(
					'title'       => 'Verifications',
					'description' => 'Verify seller address, identity, phone, email, social ids, business legality etc.',
					'icon'        => 'mdi-account-check',
					'iconColor'   => 'rgba(72, 164, 30, .7)',
					'btnColor'    => 'rgba(72, 164, 30, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/verification',
				),
				array(
					'title'       => 'Geolocation',
					'description' => 'Helps customer to find and search their nearest store.',
					'icon'        => 'mdi-map-marker-radius',
					'iconColor'   => 'rgba(143, 204, 0, .7)',
					'btnColor'    => 'rgba(143, 204, 0, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/geolocation',
				),
				array(
					'title'       => 'Customer complaint',
					'description' => 'Customer can complain for a bad or fake product(added by seller) to the admin.',
					'icon'        => 'mdi-chat-question',
					'iconColor'   => 'rgba(255, 187, 0, .7)',
					'btnColor'    => 'rgba(255, 187, 0, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/complaint',
				),
				array(
					'title'       => 'Live communications',
					'description' => 'Customers can live chat with sellers on Whatsapp, Messenger etc.',
					'icon'        => 'mdi-whatsapp',
					'iconColor'   => 'rgba(0, 153, 51, .7)',
					'btnColor'    => 'rgba(0, 153, 51, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/communication',
				),
				array(
					'title'       => 'Employee manager',
					'description' => 'Sellers can manage their employees/staff/team and allocate different tasks to different employees.',
					'icon'        => 'mdi-account-group',
					'iconColor'   => 'rgba(0, 30, 255, .7)',
					'btnColor'    => 'rgba(0, 30, 255, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/employee',
				),
				array(
					'title'       => 'SEO and digital marketing',
					'description' => 'SEO for stores and integration of facebook, twitter etc social marketing APIs.',
					'icon'        => 'mdi-store-search',
					'iconColor'   => 'rgba(255, 0, 0, .7)',
					'btnColor'    => 'rgba(255, 0, 0, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/seo-and-digital-marketing',
				),
				array(
					'title'       => 'Social login & following',
					'description' => 'Sellers can login or register with social IDs and customer can follow a store.',
					'icon'        => 'mdi-facebook',
					'iconColor'   => 'rgba(24, 119, 242, .7)',
					'btnColor'    => 'rgba(24, 119, 242, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/social-login-and-following',
				),
				array(
					'title'       => 'Subscriptions',
					'description' => 'Not only from commission, but admin can earn from product, storage etc subscriptions also.',
					'icon'        => 'mdi-account-plus',
					'iconColor'   => 'rgba(255, 187, 0, .7)',
					'btnColor'    => 'rgba(255, 187, 0, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/subscription',
				),
				array(
					'title'       => '24/7 Support',
					'description' => 'Binarithm team gives full support. A complete marketplace setup also provided on your first installation.',
					'icon'        => 'mdi-face-agent',
					'iconColor'   => 'rgba(255, 77, 0, .7)',
					'btnColor'    => 'rgba(255, 77, 0, .07)',
					'moreTitle'   => 'Know more',
					'moreLink'    => 'https=>//binarithm.com/superstore/docs/support',
				),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_get_pro_top_features', $data );
	}

	/**
	 * Get free vs pro comparison table data
	 *
	 * @return array
	 */
	public function get_comparison() {
		$data = array(
			'section_title' => __( 'FREE VS PRO COMPARISON', 'superstore' ),
			'see_more'      => array(
				'title' => __( 'See more' ),
				'link'  => 'https://binarithm.com/superstore',
			),
			'headers'       => array(
				'features' => __( 'FEATURES', 'superstore' ),
				'free'     => __( 'FREE', 'superstore' ),
				'pro'      => __( 'PRO', 'superstore' ),
			),
			'rows'          => array(
				array( 'Seller management', true, true ),
				array( 'Product management', true, true ),
				array( 'Order management', true, true ),
				array( 'Files & storage management', true, true ),
				array( 'Store open close schedule', true, true ),
				array( 'Fast and advanced single page dashboard for admin and seller both', 'limited', true ),
				array( 'Frontend store listing', 'limited', true ),
				array( 'Customizable ui designs and exclusive templates', 'limited', true ),
				array( 'Fully customizable restrictions, rules and capabilities', 'limited', true ),
				array( 'Reports and analytics for admin and seller both', 'limited', true ),
				array( 'Level-wise advanced automatic payment system', false, true ),
				array( 'Almost all popular payment gateways', false, true ),
				array( 'Level-wise commission management system', false, true ),
				array( 'Seller level upgrading to bronze, silver and gold', false, true ),
				array( 'Return and warranty', false, true ),
				array( 'Advanced shipping system with order tracking', false, true ),
				array( 'Seller staff/team/employee management', false, true ),
				array( 'Seller address, identity, phone, email, social ids, business legality etc verification', false, true ),
				array( 'Level-wise seller announcements', false, true ),
				array( 'Fake/defective/violence product complaint from seller', false, true ),
				array( 'SEO and digital marketing', false, true ),
				array( 'Social login and store following', false, true ),
				array( 'Earning from seller subscriptions', false, true ),
				array( 'Advanced live communications', false, true ),
				array( 'Geolocation(Stores near me)', false, true ),
				array( 'Multilingual', false, true ),
				array( '24/7 full support', false, true ),
			),
		);

		return apply_filters( 'superstore_admin_dashboard_localize_get_pro_comparison', $data );
	}
}
