<?php

namespace Safronik\Layout\Settings\Types;

use Safronik\Layout\Settings\Element;

class Checkbox extends Element{
	
	/**
	 * @param $element_properties
	 */
	public function __construct( $element_properties )
	{
		parent::__construct( $element_properties );
		
		if( $this->parent instanceof self && ! $this->parent->value ){
			$this->disabled = 1;
		}
	}
	
	protected function drawElement()
	{
		echo "<input
			type='checkbox'
			class='{$this->block}-input" . ($this->disabled ? ' --disabled' : '') ." {$this->slug}'
			id='{$this->slug}_{$this->id}'
			name='{$this->slug}[{$this->id}]'
			value='1'"
		     . ($this->disabled ? 'tabindex="-1"' : '')
		     . ($this->value ? ' checked' : '')
		     . ' />';
		echo "<label
			for='{$this->slug}_{$this->id}'
			class='{$this->block}-title" . ($this->disabled ? ' --disabled' : '') ."'>
				$this->title
			</label>";
		echo "<p
			class='{$this->block}-description'>
				$this->description
			</p>";
	}
}