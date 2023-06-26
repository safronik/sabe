<?php

namespace Safronik\Layout\Settings;

use Safronik\Core\CodeTemplates\Hydrator;
use Safronik\Layout\Settings\Types\Page;

class Settings{
	
	use Hydrator;
	
	private $slug = '';
	private $values = [];
	
	/**
	 * @var Element[]
	 */
	public $structure = [];
	public $set;
	public $prefix = '';
	private $complex_settings_types = [
		'pages',
		'tabs',
		'sections',
		'fields',
		'children',
	];
	
	public function __construct( $parameters )
	{
		$this->castParams( $parameters );
		
		$this->slug     .= '_settings';
		$this->structure = $this->prepareStructure( $this->structure );
		
		$this->createElementSetFromStructure();
		
	    register_setting(
			$this->slug,
			$this->slug,
			array(
				'sanitize_callback' => [ $this, 'sanitize_' . $this->slug ],
				'type' => 'array',
				'description' => '"A" Security plugin settings',
				'show_in_rest' => false,
			)
	    );
	}
	
	private function prepareStructure( $settings_structure )
	{
		$settings_structure_tmp = $settings_structure;
		foreach( $settings_structure_tmp as $id => &$item ){
			$item['id']    = $id;
			$item['slug']  = $this->slug;
			$item['value'] = isset( $this->values[ $id ] ) ? $this->values[ $id ] : null;
			
			// Append tabs heading for all tabs on this level before first spotted tab
			if( $item['type'] === 'tab' ){
				$insert = [
					$item['id'] . '_heading' => [
						'active' => isset( $item['active'] ) ? $item['active'] : false,
						'type'   => 'tab_heading',
						'id'     => $item['id'] . '_heading',
						'title'  => $item['title'],
						'slug'   => $this->slug,
					]
				];
				$pos = array_search( $id, array_keys( $settings_structure_tmp ), true );
	            $settings_structure = array_merge(
	                array_slice($settings_structure, 0, $pos),
	                $insert,
	                array_slice($settings_structure, $pos)
	            );
			}
			
			if( ! empty( $item['children'] ) ){
				$item['children'] = $this->prepareStructure( $item['children'] );
			}
			
			$settings_structure[ $id ] = $item;
		}
		
		return $settings_structure;
	}
	
	private function createElementSetFromStructure()
	{
		foreach ( $this->structure as $element_id => $properties ){
			$this->set[ $element_id ] = Element::factory( $properties );
		}
	}
	
	public function initialize( $settings_set = [] )
	{
		$settings_set = $settings_set ?: $this->set;
		foreach ( $settings_set as $id => $elem ){
			
			if( $elem instanceof Page ){
				$elem->initializeElement();
				
				// Register sub-pages if they are
				if( ! empty( $elem->children ) ){
					$this->initialize( $elem->children );
				}
			}
		}
	}
	
	public function draw( $settings_set = [] )
	{
		$settings_set = $settings_set ?: $this->set;
		foreach ( $settings_set as $id => $elem ){
			$elem->draw();
		}
	}
	
	public function sanitize_Safronik_settings( $settings = [] )
	{
		
		
		return $settings;
	}
}