<?php

namespace Binarithm\Superstore;

/**
 * Superstore media files/attachments controller class
 */
class Media {

	/**
	 * Read a seller files
	 *
	 * @param array $seller_id Seller ID.
	 * @param array $args Filter files.
	 * @return array
	 */
	public function get_seller_files( $seller_id, $args = array() ) {
		$files = array();

		if ( ! $seller_id ) {
			return $files;
		} else {
			$seller_id = absint( $seller_id );
		}

		if ( ! user_can( $seller_id, 'manage_superstore' ) ) {
			return $files;
		}

		$defaults = array(
			'numberposts' => -1,
			'post_type'   => 'attachment',
			'author'      => $seller_id,
			'post_status' => array(
				'publish',
				'pending',
				'draft',
				'auto-draft',
				'future',
				'private',
				'inherit',
				'trash',
			),
		);

		$args = wp_parse_args( $args, $defaults );

		$files = get_posts( $args );

		return $files;
	}

	/**
	 * Get total storage occupied.
	 *
	 * @param array $args Filter item.
	 * @return float
	 */
	public function get_storage_occupied( $args = array() ) {
		$defaults = array(
			'numberposts' => -1,
			'post_type'   => 'attachment',
			'post_status' => array(
				'publish',
				'pending',
				'draft',
				'auto-draft',
				'future',
				'private',
				'inherit',
				'trash',
			),
		);

		$args = wp_parse_args( $args, $defaults );

		$files = get_posts( $args );

		$size = 0;

		foreach ( $files as $file ) {
			if ( wp_get_attachment_metadata( $file->ID ) && array_key_exists( 'filesize', wp_get_attachment_metadata( $file->ID ) ) ) {
				$size += wp_get_attachment_metadata( $file->ID )['filesize'];
			}
		}

		return $size;
	}

	/**
	 * Get total storage available of a seller.
	 *
	 * @param array $args Filter item.
	 * @return float
	 */
	public function get_seller_storage_available( $args = array() ) {
		$storage_occupied = (int) $this->get_storage_occupied( $args );
		$storage_limit    = 0;
		$user_meta        = false;

		$unlimited_storage_for_all = superstore_get_option( 'unlimited_storage_for_all_sellers', 'superstore_seller', 'yes' );

		if ( 'yes' === $unlimited_storage_for_all ) {
			return -1; // -1 means unlimited.
		}

		if ( $args['author'] ) {
			$user_meta = get_user_meta( $args['author'], 'superstore_storage_limit', true );
			if ( -1 === (int) $user_meta ) {
				return -1;
			}
		}

		if ( $user_meta ) {
			// Value number will be saved in MB. Need to convert into byte.
			$inbyte        = 1000000 * (float) $user_meta;
			$storage_limit = $inbyte;
		}

		$available = $storage_limit - $storage_occupied;

		if ( empty( $storage_limit ) ) {
			return $storage_limit;
		}

		return $available;
	}
}
