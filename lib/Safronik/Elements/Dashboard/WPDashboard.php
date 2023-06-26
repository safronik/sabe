<?php

namespace Safronik\Layout\Dashboard;

class WPDashboard extends \Safronik\Layout\Dashboard\Dashboard{
	
	public function __construct( $params, $settings )
	{
		parent::__construct( $params, $settings );
		
		$this->app->resources->registerCSS( 'admin-common' );
		$this->app->resources->registerCSS( 'admin-settings' );
		$this->app->resources->registerCSS('table');
		$this->app->resources->registerJS(
			'admin-settings-class',
            [
				'hierarchy' => $this->settings->structure,
			]
		);
	}
	
	/**
	 * Actions for admins
	 *
	 * @return void
	 */
	public function initAdminActions()
	{
		$this->activationRedirect(); // Redirects to the settings page when plugin been activated
		$this->changeAppView();
	}
	
	/**
	 * Function for redirect to settings
	 *
	 * @return void
	 */
	private function activationRedirect()
	{
		if ( $this->app->state->activation_redirect && ! isset( $_GET['activate-multi'] ) ) {
			$this->app->state->activation_redirect = 0;
			wp_redirect( $this->app->data->settings_link );
		}
	}
	
	public function changeAppView()
	{
		global $pagenow;
		
		if ( $pagenow === 'plugins.php' ){
	        add_filter('plugin_action_links_' . $this->app->base_name, function($links){
				$settings_link = '<a href="options-general.php?page=asec_main">' . __('Settings') . '</a>';
			    array_unshift($links, $settings_link);
		        
		        return $links;
	        }, 10, 2);
	    }
	}
	
	protected function appendBanner()
	{
		// TODO: Implement appendBanner() method.
	}
	
	protected function appendWidget()
	{
		// TODO: Implement appendWidget() method.
	}
}