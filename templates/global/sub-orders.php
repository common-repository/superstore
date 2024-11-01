<?php
/**
 * Superstore sub order template on customer order page
 *
 * @package superstore
 */

?>

<header>
	<h2><?php esc_html_e( 'Sub Orders', 'superstore' ); ?></h2>
</header>

<div>
	<strong><?php esc_html_e( 'Note:', 'superstore' ); ?></strong>
	<?php
	esc_html_e(
		'The products of this order are from different sellers. Each seller will manage their orders individually.',
		'superstore'
	);
	?>
</div>

<table class="shop_table my_account_orders table table-striped">

	<thead>
		<tr>
			<th class="order-number"><span class="nobr"><?php esc_html_e( 'Order', 'superstore' ); ?></span></th>
			<th class="order-date"><span class="nobr"><?php esc_html_e( 'Date', 'superstore' ); ?></span></th>
			<th class="order-status"><span class="nobr"><?php esc_html_e( 'Status', 'superstore' ); ?></span></th>
			<th class="order-total"><span class="nobr"><?php esc_html_e( 'Total', 'superstore' ); ?></span></th>
			<th class="order-actions">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( $sub_orders as $order_post ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$order      = new WC_Order( $order_post->ID );
			$item_count = $order->get_item_count();
			$date       = wc_format_datetime( $order->get_date_created(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) );
			?>
			<tr class="order">
				<td class="order-number">
					<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
						#<?php echo esc_html( $order->get_order_number() ); ?>
					</a>
				</td>
				<td class="order-date">
					<?php // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date ?>
					<time datetime="<?php echo esc_attr( date( 'Y-m-d', strtotime( $date ) ) ); ?>" title="<?php echo esc_attr( strtotime( $date ) ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ); ?></time>
				</td>
				<td class="order-status" style="text-align:left; white-space:nowrap;">
					<?php echo isset( $statuses[ 'wc-' . superstore_get_prop( $order, 'status' ) ] ) ? esc_html( $statuses[ 'wc-' . superstore_get_prop( $order, 'status' ) ] ) : esc_html( superstore_get_prop( $order, 'status' ) ); ?>
				</td>
				<td class="order-total">
					<?php echo wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'superstore' ), $order->get_formatted_order_total(), $item_count ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment ?>
				</td>
				<td class="order-actions">
					<?php
						$actions = array();

						$actions['view'] = array(
							'url'  => $order->get_view_order_url(),
							'name' => __( 'View', 'superstore' ),
						);

						$actions = apply_filters( 'superstore_my_account_my_sub_orders_actions', $actions, $order );

						// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						foreach ( $actions as $key => $action ) {
							echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
						}
						?>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
