<?php

namespace TestApp\Controller;

use Tools\Controller\Controller;

/**
 * Use Controller instead of AppController to avoid conflicts
 *
 * @property \Tools\Controller\Component\CommonComponent $Common
 */
class CommonComponentTestController extends Controller {

	/**
	 * @var array
	 */
	public $autoRedirectActions = ['allowed'];

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Tools.Common');
	}

}
