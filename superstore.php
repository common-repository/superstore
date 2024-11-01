<?php
/**
 * Plugin Name: Superstore
 * Plugin URI: https://wordpress.org/plugins/superstore/
 * Description: A plugin that helps you to make your e-commerce multi vendor marketplace.
 * Version: 1.0.0
 * Author: Binarithm
 * Author URI: https://binarithm.com/
 * Text Domain: superstore
 * Domain Path: /languages/
 * WC requires at least: 3.0.0
 * WC tested up to: 8.4.0
 * License: GPLv3
 *
 * @package Superstore
 */

/**
 * *********************************************************************
 * Copyright (C) 2022 Binarithm
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see https://www.gnu.org/licenses/gpl-3.0.en.html
 * **********************************************************************
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'SUPERSTORE_PLUGIN_FILE' ) ) {
	define( 'SUPERSTORE_PLUGIN_FILE', __FILE__ );
}

// Include the main final Superstore class.
if ( ! class_exists( 'Superstore', false ) ) {
	include_once dirname( SUPERSTORE_PLUGIN_FILE ) . '/includes/Superstore.php';
}

/**
 * Returns the main instance of Superstore.
 *
 * @return Superstore
 */
function superstore() {
	return Superstore::instance();
}

// Let's go :).
superstore();
