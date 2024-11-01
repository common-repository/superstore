<?php

namespace Binarithm\Superstore\Localize\SellerDashboard;

/**
 * Superstore seller dashboard localize product class
 */
class Product {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'superstore_frontend_localize_data', array( $this, 'add_data' ) );
	}

	/**
	 * Add data
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public function add_data( $data ) {
		$data['product'] = array(
			'tab' => array(
				'active_tab' => '/product',
				'tabs'       => $this->tabs(),
				'body'       => $this->tabs_body(),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_product_data', $data );
	}

	/**
	 * Add tabs
	 *
	 * @return array
	 */
	public function tabs() {
		$tabs = array(
			'products'        => array(
				'title' => __( 'Products', 'superstore' ),
				'route' => '/product',
			),
			'add_new_product' => array(
				'title' => __( 'Add new product', 'superstore' ),
				'route' => '/product/add-new-product',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_product_tabs', $tabs );
	}

	/**
	 * Tabs body data
	 *
	 * @return array
	 */
	public function tabs_body() {
		$data = array(
			'products'        => array(
				'table'             => array(
					'filterItems'  => $this->table_filters(),
					'links'        => $this->table_links(),
					'actions'      => $this->table_actions(),
					'headers'      => $this->table_headers(),
					'restEndpoint' => '/products/',
				),
				'single'            => $this->get_single_product_data(),
				'in_stock_text'     => __( 'In stock' ),
				'out_of_stock_text' => __( 'Out of stock' ),
			),
			'add_new_product' => array(
				'form'   => array(
					'fields'      => superstore_get_form_field_values_from_sections( $this->get_add_new_product_form_sections() ),
					'sections'    => $this->get_add_new_product_form_sections(),
					'submitEvent' => 'product/addNewProduct',
				),
				'notify' => apply_filters(
					'superstore_seller_dashboard_localize_add_new_product_notifications',
					array(
						'product_added' => __( 'Product added successfully', 'superstore' ),
					)
				),
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_product_tabs_body', $data );
	}

	/**
	 * Table top left links (Generally used to filter and count the table rows)
	 *
	 * @return array
	 */
	public function table_filters() {
		$filters = array(
			array(
				'title' => __( 'All', 'superstore' ),
				'name'  => 'all',
				'value' => null,
			),
			array(
				'title' => __( 'Published', 'superstore' ),
				'name'  => 'publish',
				'value' => 'yes',
			),
			array(
				'title' => __( 'Pending review', 'superstore' ),
				'name'  => 'pending',
				'value' => 'yes',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_product_table_filters', $filters );
	}

	/**
	 * Table top right links
	 *
	 * @return array
	 */
	public function table_links() {
		$links = array(
			array(
				'title' => __( 'Settings', 'superstore' ),
				'to'    => '/settings',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_product_table_links', $links );
	}

	/**
	 * Table actions with js namespace methods
	 *
	 * @return array
	 */
	public function table_actions() {
		$data = array(
			array(
				'title'  => __( 'Delete', 'superstore' ),
				'name'   => 'delete',
				'method' => 'product/delete',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_product_table_actions', $data );
	}

	/**
	 * Table headers
	 *
	 * @return array
	 */
	public function table_headers() {
		$headers = array(
			array(
				'text'     => 'Name',
				'sortable' => false,
				'value'    => 'name',
				'class'    => 'font-weight-bold',
			),
			array(
				'text'  => 'SKU',
				'value' => 'sku',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Stock',
				'value' => 'stock_status',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Price',
				'value' => 'price_html',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Date Created',
				'value' => 'date_created',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Sold',
				'value' => 'sold',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Views',
				'value' => 'views',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Type',
				'value' => 'type',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Status',
				'value' => 'status',
				'class' => 'font-weight-bold',
			),
			array(
				'text'  => 'Actions',
				'value' => 'action',
				'class' => 'font-weight-bold',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_product_table_headers', $headers );
	}

	/**
	 * Single product data
	 *
	 * @return array
	 */
	public function get_single_product_data() {
		$data = array(
			'product_not_found' => __( 'Product not found' ),
			'form'              => array(
				'fields'      => superstore_get_form_field_values_from_sections( $this->get_single_product_form_sections() ),
				'sections'    => $this->get_single_product_form_sections(),
				'submitEvent' => 'product/addNewProduct',
			),
		);

		return apply_filters( 'superstore_seller_dashboard_localize_single_product_data', $data );
	}

	/**
	 * Single product edit form sections
	 *
	 * @return array
	 */
	public function get_single_product_form_sections() {
		$drop_down_tags = array(
			'hide_empty' => 0,
		);

		$product_tags = get_terms( 'product_tag', $drop_down_tags );
		$product_cats = get_terms( 'product_cat', $drop_down_tags );
		$tags         = array();
		$cats         = array();

		foreach ( $product_tags as $key => $value ) {
			$tags[ $key ]['value'] = array(
				'id'   => $value->term_id,
				'name' => $value->name,
				'slug' => $value->slug,
			);
			$tags[ $key ]['label'] = $value->name;
		}

		foreach ( $product_cats as $key => $value ) {
			$cats[ $key ]['value'] = array(
				'id'   => $value->term_id,
				'name' => $value->name,
				'slug' => $value->slug,
			);
			$cats[ $key ]['label'] = $value->name;
		}

		$sections = array(
			array(
				'title'  => __( 'General', 'superstore' ),
				'fields' => array(
					'name'              => array(
						'name'     => 'name',
						'title'    => __( 'Product name', 'superstore' ),
						'type'     => 'text',
						'required' => 'yes',
					),
					'slug'              => array(
						'name'  => 'slug',
						'title' => __( 'Permalink slug', 'superstore' ),
						'hint'  => __( 'https://mysite.com/product/[product-slug]', 'superstore' ),
						'type'  => 'text',
					),
					'download_options'  => array(
						'name'      => 'download_options',
						'title'     => __( 'Download options', 'superstore' ),
						'slot_name' => 'download_options',
						'type'      => 'slot',
						'items'     => array(
							'downloadable'    => array(
								'name'  => 'downloadable',
								'label' => __( 'Downloadable', 'superstore' ),
							),
							'downloads'       => array(
								'name'            => 'downloads',
								'label'           => __( 'Downloadable files', 'superstore' ),
								'add_btn_text'    => __( 'Add file', 'superstore' ),
								'remove_btn_text' => __( 'Remove file', 'superstore' ),
								'choose_btn_text' => __( 'Choose', 'superstore' ),
								'file_text'       => __( 'Name', 'superstore' ),
								'url_text'        => __( 'File URL', 'superstore' ),
							),
							'download_limit'  => array(
								'name'  => 'download_limit',
								'hint'  => __( '-1 is for unlimited re-downloads.', 'superstore' ),
								'label' => __( 'Download limit', 'superstore' ),
							),
							'download_expiry' => array(
								'name'  => 'download_expiry',
								'label' => __( 'Download expiry days', 'superstore' ),
								'hint'  => __( 'Enter the number of days before a download link expires, or leave -1 for unlimited days.', 'superstore' ),
							),
						),
					),
					'virtual'           => array(
						'name'        => 'virtual',
						'title'       => __( 'Virtual', 'superstore' ),
						'type'        => 'checkbox',
						'true_value'  => true,
						'false_value' => false,
					),
					'regular_price'     => array(
						'name'     => 'regular_price',
						'title'    => __( 'Regular price', 'superstore' ),
						'type'     => 'number',
						'required' => 'yes',
					),
					'sale_price'        => array(
						'name'     => 'sale_price',
						'title'    => __( 'Sale price', 'superstore' ),
						'hint'     => __( 'Sale price must be less than regular price', 'superstore' ),
						'type'     => 'number',
						'required' => 'yes',
					),
					'categories'        => array(
						'name'     => 'categories',
						'title'    => __( 'Categories', 'superstore' ),
						'type'     => 'autocomplete',
						'items'    => $cats,
						'required' => 'yes',
						'multiple' => 'yes',
					),
					'tags'              => array(
						'name'     => 'tags',
						'title'    => __( 'Tags', 'superstore' ),
						'type'     => 'autocomplete',
						'items'    => $tags,
						'multiple' => 'yes',
					),
					'short_description' => array(
						'name'  => 'short_description',
						'title' => __( 'Short description', 'superstore' ),
						'type'  => 'text_editor',
					),
					'description'       => array(
						'name'  => 'description',
						'title' => __( 'Description', 'superstore' ),
						'type'  => 'text_editor',
					),
					'sold'              => array(
						'name'  => 'sold',
						'title' => __( 'Total sold', 'superstore' ),
						'type'  => 'read_only',
					),
					'views'             => array(
						'name'  => 'views',
						'title' => __( 'Total views', 'superstore' ),
						'type'  => 'read_only',
					),
				),
			),
			array(
				'title'  => __( 'Media', 'superstore' ),
				'fields' => array(
					'thumbnail_image' => array(
						'name'            => 'thumbnail_image',
						'cropping_width'  => 600,
						'cropping_height' => 400,
						'title'           => __( 'Product image', 'superstore' ),
						'type'            => 'file',
					),
					'gallery_images'  => array(
						'name'            => 'gallery_images',
						'cropping_width'  => 100,
						'cropping_height' => 100,
						'title'           => __( 'Product gallery images', 'superstore' ),
						'type'            => 'files',
					),
				),
			),
			array(
				'title'  => __( 'Advanced', 'superstore' ),
				'fields' => array(
					'sold_individually'  => array(
						'name'        => 'sold_individually',
						'title'       => __( 'Sold individually', 'superstore' ),
						'hint'        => __( 'Limit purchases to 1 item per order', 'superstore' ),
						'type'        => 'checkbox',
						'true_value'  => true,
						'false_value' => false,
					),
					'status'             => array(
						'name'  => 'status',
						'title' => __( 'Status', 'superstore' ),
						'hint'  => __( 'If current status is pending review you can not change status', 'superstore' ),
						'type'  => 'select',
						'items' => array(
							array(
								'name'  => __( 'Published', 'superstore' ),
								'value' => 'publish',
							),
							array(
								'name'  => __( 'Draft', 'superstore' ),
								'value' => 'draft',
							),
						),
					),
					'catalog_visibility' => array(
						'name'  => 'catalog_visibility',
						'title' => __( 'Visibility', 'superstore' ),
						'type'  => 'select',
						'items' => array(
							array(
								'name'  => __( 'Catalog and search', 'superstore' ),
								'value' => 'visible',
							),
							array(
								'name'  => __( 'Catalog', 'superstore' ),
								'value' => 'catalog',
							),
							array(
								'name'  => __( 'Search', 'superstore' ),
								'value' => 'search',
							),
							array(
								'name'  => __( 'Hidden', 'superstore' ),
								'value' => 'hidden',
							),
						),
					),
					'purchase_note'      => array(
						'name'  => 'purchase_note',
						'title' => __( 'Purchase note', 'superstore' ),
						'type'  => 'textarea',
					),
					'reviews_allowed'    => array(
						'name'        => 'reviews_allowed',
						'title'       => __( 'Enable Reviews', 'superstore' ),
						'type'        => 'checkbox',
						'true_value'  => true,
						'false_value' => false,
					),
				),
			),
		);

		if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
			$sections[] = array(
				'title'  => __( 'Inventory management', 'superstore' ),
				'fields' => array(
					'inventory' => array(
						'name'      => 'inventory',
						'title'     => __( 'Inventory', 'superstore' ),
						'type'      => 'slot',
						'slot_name' => 'inventory',
						'items'     => array(
							'sku'              => array(
								'name'  => 'sku',
								'label' => __( 'SKU', 'superstore' ),
							),
							'stock_status'     => array(
								'name'   => 'stock_status',
								'label'  => __( 'Stock status', 'superstore' ),
								'type'   => 'radio',
								'groups' => array(
									array(
										'label' => __( 'In stock', 'superstore' ),
										'value' => 'instock',
									),
									array(
										'label' => __( 'Out of stock', 'superstore' ),
										'value' => 'outofstock',
									),
								),
							),
							'manage_stock'     => array(
								'name'        => 'manage_stock',
								'label'       => __( 'Enable stock management', 'superstore' ),
								'type'        => 'checkbox',
								'true_value'  => true,
								'false_value' => false,
							),
							'stock_quantity'   => array(
								'name'  => 'stock_quantity',
								'label' => __( 'Stock quantity', 'superstore' ),
								'type'  => 'number',
							),
							'backorders'       => array(
								'name'  => 'backorders',
								'label' => __( 'Allow backorders', 'superstore' ),
								'type'  => 'select',
								'items' => array(
									array(
										'name'  => __( 'Do not allow', 'superstore' ),
										'value' => 'no',
									),
									array(
										'name'  => __( 'Allow but notify customer', 'superstore' ),
										'value' => 'notify',
									),
									array(
										'name'  => __( 'Allow', 'superstore' ),
										'value' => 'yes',
									),
								),
							),
							'low_stock_amount' => array(
								'name'  => 'low_stock_amount',
								'label' => __( 'Low stock threshold', 'superstore' ),
								'type'  => 'number',
							),
						),
					),
				),
			);
		}

		return apply_filters( 'superstore_seller_dashboard_localize_single_seller_form_sections', $sections );
	}

	/**
	 * Add new product form sections
	 *
	 * @return array
	 */
	public function get_add_new_product_form_sections() {
		$drop_down_tags = array(
			'hide_empty' => 0,
		);

		$product_tags = get_terms( 'product_tag', $drop_down_tags );
		$product_cats = get_terms( 'product_cat', $drop_down_tags );
		$tags         = array();
		$cats         = array();

		foreach ( $product_tags as $key => $value ) {
			$tags[ $key ]['value'] = array(
				'id'   => $value->term_id,
				'name' => $value->name,
				'slug' => $value->slug,
			);
			$tags[ $key ]['label'] = $value->name;
		}

		foreach ( $product_cats as $key => $value ) {
			$cats[ $key ]['value'] = array(
				'id'   => $value->term_id,
				'name' => $value->name,
				'slug' => $value->slug,
			);
			$cats[ $key ]['label'] = $value->name;
		}

		$sections = array(
			array(
				'title'  => __( 'General', 'superstore' ),
				'fields' => array(
					'name'          => array(
						'name'     => 'name',
						'title'    => __( 'Product name', 'superstore' ),
						'type'     => 'text',
						'required' => 'yes',
					),
					'regular_price' => array(
						'name'     => 'regular_price',
						'title'    => __( 'Regular price', 'superstore' ),
						'type'     => 'number',
						'required' => 'yes',
					),
					'sale_price'    => array(
						'name'     => 'sale_price',
						'title'    => __( 'Sale price', 'superstore' ),
						'hint'     => __( 'Sale price must be less than regular price', 'superstore' ),
						'type'     => 'number',
						'required' => 'yes',
					),
					'categories'    => array(
						'name'     => 'categories',
						'title'    => __( 'Categories', 'superstore' ),
						'type'     => 'autocomplete',
						'multiple' => 'yes',
						'items'    => $cats,
						'required' => 'yes',
						'default'  => array(
							$cats[0]['value'],
						),
					),
					'tags'          => array(
						'name'     => 'tags',
						'title'    => __( 'Tags', 'superstore' ),
						'type'     => 'autocomplete',
						'multiple' => 'yes',
						'items'    => $tags,
					),
					'description'   => array(
						'name'  => 'description',
						'title' => __( 'Description', 'superstore' ),
						'type'  => 'text_editor',
					),
				),
			),
			array(
				'title'  => __( 'Media', 'superstore' ),
				'fields' => array(
					'thumbnail_image' => array(
						'name'            => 'thumbnail_image',
						'cropping_width'  => 600,
						'cropping_height' => 400,
						'title'           => __( 'Product image', 'superstore' ),
						'type'            => 'file',
					),
					'gallery_images'  => array(
						'name'            => 'gallery_images',
						'cropping_width'  => 100,
						'cropping_height' => 100,
						'title'           => __( 'Product gallery images', 'superstore' ),
						'type'            => 'files',
						'default'         => array(),
					),
				),
			),
		);
		return apply_filters( 'superstore_seller_dashboard_localize_add_new_product_sections', $sections );
	}
}
