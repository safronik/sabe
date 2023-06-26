<?php

namespace Safronik\Layout\Settings\Types;

use Safronik\Layout\Settings\Element;

class Text extends Element{
	
	protected function drawElement()
	{
		$out = 'text';
		
		return $out;
	}
}