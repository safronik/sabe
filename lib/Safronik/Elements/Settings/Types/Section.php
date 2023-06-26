<?php

namespace Safronik\Layout\Settings\Types;

use Safronik\Layout\Settings\Element;

class Section extends Element{
	
	/**
	 * @param $element_properties
	 */
	public function __construct( $element_properties )
	{
		parent::__construct( $element_properties );
	}
	
	protected function drawElement()
	{
		echo "<h3 class='{$this->block}-title'>{$this->title}</h3>";
	}
}