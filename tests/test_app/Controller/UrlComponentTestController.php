<?php

namespace TestApp\Controller;

use Tools\Controller\Controller;

/**
 * Use Controller instead of AppController to avoid conflicts
 *
 * @property \Tools\Controller\Component\UrlComponent $Url
 */
class UrlComponentTestController extends Controller {

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Tools.Url');
	}

}
