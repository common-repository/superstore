<?php

namespace Binarithm\Superstore\Localize;

use Binarithm\Superstore\Traits\Container;

/**
 * Superstore localize data controller class
 */
class Controller {

	use Container;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->container['admin_dashboard']  = new \Binarithm\Superstore\Localize\AdminDashboard\Controller();
		$this->container['seller_dashboard'] = new \Binarithm\Superstore\Localize\SellerDashboard\Controller();
		$this->container['seller_login']     = new SellerLogin();
		$this->container['stores']           = new Stores();
	}
}
