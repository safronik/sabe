<?php

namespace Safronik\Layout\Dashboard;

use Safronik\Layout\Settings\Settings;
use Safronik\Core\CodeTemplates\Hydrator;

abstract class Dashboard{
	
	use Hydrator;
	
	protected $app;
	
	/**
	 * @var Settings
	 */
	public $settings;
	protected $admin_bar;
	protected $widget;
	protected $banners;
	
	public function __construct( $params, $settings )
	{
		$this->castParams( $params );
		
		$this->settings = $settings;
		// @todo admin_bar
		// @todo admin_enqueue_scripts
	}
	
	abstract protected function appendBanner();
	abstract protected function appendWidget();
}