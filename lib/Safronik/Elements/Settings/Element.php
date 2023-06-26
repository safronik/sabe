<?php

namespace Safronik\Layout\Settings;

use Safronik\Core\CodeTemplates\Hydrator;

abstract class Element{
	
	use Hydrator;
	
	protected $output = true;
	protected $html = '';
	protected $slug = '';
	
	// New ones
	public $id = '';
	public $type = 'checkbox';
	public $title = 'Title placeholder';
	public $icon = 'unknown';
	public $hidden = 0;
	public $callback;
	public $disabled = 0;
	public $required = 0;
	public $children = [];
	public $parent;
	public $value;
	public $block = '';
	public $description = '';
	public $classes = [];
	
	// Old ones
	public $name = '';
	public $html_before = '';
	public $html_after = '';
	public $js_before = '';
	public $js_after = '';

	abstract protected function drawElement();
	
	public function __construct( $params )
	{
		$this->castParams( $params );
		
		foreach( $this->children as $child_id => &$child ){
			$child['parent'] = $this;
			$child = self::factory( $child );
		}
		unset( $child_id, $child );
		
		$this->block       = $this->slug . '_' . $this->type;
		
		$this->appendCSSClasses();
		
		$this->html_before = '<div class="' . implode( ' ', $this->classes ) . '" id="' . $this->id . '">';
		$this->html_after  = '</div>';
	}
	
	/**
	 * @param $element_properties
	 * @param $id
	 *
	 * @return Element
	 */
	public static function factory( $element_properties )
	{
		switch ( $element_properties['type'] ){
			
			// Complex
			case 'page':        return new Types\Page( $element_properties );
			case 'tab':         return new Types\Tab( $element_properties );
			case 'tab_heading': return new Types\TabHeading( $element_properties );
			case 'section':     return new Types\Section( $element_properties );
			
			// Atomic
			case 'text':     return new Types\Text( $element_properties );
			case 'plain':    return new Types\Plain( $element_properties );
			case 'checkbox': return new Types\Checkbox( $element_properties );
			case 'radio':    return new Types\Radio( $element_properties );
			case 'checkbox': return new Types\Select( $element_properties );
			case 'textarea': return new Types\Textarea( $element_properties );
			case 'time':     return new Types\Time( $element_properties );
			case 'number':   return new Types\Number( $element_properties );
			
			// Special
			case 'hidden':   return new Types\Hidden( $element_properties );
			
			default: return new Types\Checkbox( $element_properties );
		}
	}

	
	public function draw()
	{
		$this->drawHTMLBefore();
		$this->drawJSBefore();

		// Custom output for the element
		$this->callback
			? call_user_func( $this->callback )
			: $this->drawElement();
		
		$this->drawChildren();
		
		$this->drawJSAfter();
		$this->drawHTMLAfter();
	}

	protected function drawChildren()
	{
		$out = '';
		// Draw children
		if( $this->children ) {
			foreach ( $this->children as $child ) {
				$out .= $child->draw();
			}
		}
		
		return $out;
	}
	
	/**
	 * @param $elements
	 *
	 * @return void
	 */
	public function appendChildren( $elements = [] )
	{
		foreach ($elements as $element){
			$this->appendChild( $element );
		}
	}
	
	
	private function appendChild( Element $element )
	{
		$element->parent                = $this;
		$this->children[ $element->id ] = $element;
	}
	
	public function drawHTMLBefore()
	{
		echo $this->html_before;
	}
	
	public function drawHTMLAfter()
	{
		echo $this->html_after;
	}
	
	public function drawJSBefore()
	{
		echo $this->js_before;
	}
	
	public function drawJSAfter()
	{
		echo $this->js_after;
	}
	
	protected function appendCSSClasses()
	{
		$this->classes[] = $this->block;
	}
}