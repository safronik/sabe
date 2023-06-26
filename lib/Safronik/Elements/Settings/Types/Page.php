<?php

namespace Safronik\Layout\Settings\Types;

use Safronik\Layout\Settings\Element;

class Page extends Element{
	
	/**
	 * @param $element_properties
	 */
	public function __construct( $element_properties )
	{
		parent::__construct( $element_properties );
		
		$this->html_before = '';
		$this->html_after = '';
	}
	
	public function initializeElement()
	{
		// Top level menu
		if( ! isset( $this->parent ) ){
			add_action( 'admin_menu', function(){
				add_menu_page(
					$this->title,
					$this->title,
					'manage_options',
					$this->id,
					[ $this, 'draw' ],
					plugins_url( 'myplugin/images/icon.png' )
				);
			});
		}else{
			add_action( 'admin_menu', function() {
				add_submenu_page(
					$this->parent instanceof Page ? $this->parent->id : $this->parent,
					$this->title,
					$this->title,
					'manage_options',
					$this->id,
					[ $this, 'draw' ]
				);
			});
		}
	}
	
	public function drawHTMLBefore() {
		echo '<h1>' . $this->title . '</h1>';
		echo '<form method="POST" action="options.php">';
	}
	
	protected function drawElement()
	{
	
	}
	
	public function drawHTMLAfter()
	{
			settings_fields( $this->slug );  // название группы опций - register_setting( $option_group )
			submit_button();
		echo '</form>';
	}
}