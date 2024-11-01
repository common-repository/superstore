<?php
	$thumbnail_src = SUPERSTORE_ASSETS_DIR . '/images/superstore-logo-black.png';
?>
<div class="notice superstore-upgrade-notice" style="border-left-color: #008b80;">
	<div class="thumbnail">
		<a href="<?php echo esc_url( 'https://binarithm.com' ); ?>" target="_blank">
			<img src="<?php echo esc_url( $thumbnail_src ); ?>" alt="<?php echo esc_attr( 'Superstore' ); ?>">
		</a>
	</div>
	<div class="content">
		<h2><?php echo esc_html_e( 'Thank you for using Superstore!', 'superstore' ); ?></h2>
		<span><?php echo esc_html_e( 'Superstore PRO is upcoming with many more exclusive features! Level upgrading feature (bronze, silver and gold seller) is one of the most important features in Superstore PRO. ', 'superstore' ); ?>
			<a href="<?php echo esc_url( 'https://binarithm.com/superstore' ); ?>" target="_blank"><?php echo esc_html_e( 'Get notified', 'superstore' ); ?></a>
		</span>
		<span><?php echo esc_html_e( 'on launch date and never miss the `First Launch` exclusive offers.', 'superstore' ); ?>
		</span>
		<div class="btn" style="margin-top: 20px;">
			<a href="<?php echo esc_url( 'https://binarithm.com/superstore' ); ?>" class="button button-primary promo-btn" target="_blank"><?php echo esc_html_e( 'See more features', 'superstore' ); ?>
			</a>
		</div>
	</div>
	<span class="prmotion-close-icon dashicons dashicons-no-alt"></span>
	<div class="clear"></div>
</div>

<style>
	.superstore-upgrade-notice {
		padding: 20px;
		box-sizing: border-box;
		position: relative;
		background-repeat: no-repeat;
		background-size: cover;
	}

	.superstore-upgrade-notice .prmotion-close-icon{
		position: absolute;
		top: 15px;
		right: 15px;
		cursor: pointer;
	}

	.superstore-upgrade-notice .thumbnail {
		width: 8%;
		float: left;
	}

	.superstore-upgrade-notice .thumbnail img{
		width: 100%;
		height: auto;
		box-sizing: border-box;
	}

	.superstore-upgrade-notice .thumbnail a{
		text-decoration: none;
		background: unset;
	}

	.superstore-upgrade-notice .content {
		float:left;
		margin-left: 30px;
		width: 75%;
	}

	.superstore-upgrade-notice .content h2 {
		margin: 3px 0px 5px;
		font-size: 20px;
		font-weight: bold;
		color: #555;
		line-height: 25px;
	}

	.superstore-upgrade-notice .content p {
		font-size: 14px;
		text-align: justify;
		color: #666;
		margin-bottom: 10px;
	}

	.superstore-upgrade-notice .content .btn a {
		border: none;
		box-shadow: none;
		height: 31px;
		line-height: 30px;
		border-radius: 3px;
		background: #3e3091;
		text-shadow: none;
		width: 140px;
		text-align: center;
	}

	.superstore-upgrade-notice .content .features img {
		width: 5%;
		height: auto;
		box-shadow: -3px 3px 5px rgba(0, 0, 0, .3);
		margin-right: 20px;
		box-sizing: border-box;
		border-radius: 5px;
	}

	.superstore-upgrade-notice .content .features a {
		text-decoration: none;
		background: unset;
	}
</style>

<script type='text/javascript'>
	jQuery(document).ready(function($){
		$('body').on('click', '.superstore-upgrade-notice span.prmotion-close-icon', function(e) {
			e.preventDefault();

			var self = $(this);

			wp.ajax.send( 'superstore_dismiss_upgrade_notice', {
				data: {
					superstore_upgrade_notice_dismissed: true,
					nonce: '<?php echo esc_attr( wp_create_nonce( 'superstore_admin' ) ); ?>'
				},
				complete: function( resp ) {
					self.closest('.superstore-upgrade-notice').fadeOut(200);
				}
			} );
		});
	});
</script>
