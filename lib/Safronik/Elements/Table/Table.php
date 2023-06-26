<?php

namespace Safronik\Layout\Table;

use Safronik\Core\DB\DB;

abstract class Table
{
	public $data = [];
	private $headings = [];
	private $out = '';
	
	public function __construct( $data )
	{
		$this->data     = $this->reflectData( $data );
		$this->headings = $this->reflectHeadings( array_keys( current( $data ) ) );
	}
	
	abstract public function reflectHeadings($raw_headings);
	abstract public function reflectData( $data );
	
	public function printTable()
	{
		echo $this->out;
	}
	
	public function render()
	{
		$this->out = '<table>';
			$this->out .= $this->renderHeadings();
			$this->out .= $this->renderBody();
			$this->out .= $this->renderFooter();
		$this->out .= '</table>';
	}
	
	private function renderHeadings()
	{
		$out = '<thead><tr>';
		foreach( $this->headings as $heading ){
			$out .=  '<td>' . $heading . '</td>';
		}
		$out .= '</tr></thead>';
		
		return $out;
	}
	
	private function renderBody()
	{
		$out = '<tbody>';
		foreach( $this->data as $row_data ){
			$out .= '<tr>';
			foreach( $row_data as $row_datum ){
				$out .= '<td>' . $row_datum . '</td>';
			}
			$out .= '</tr>';
		}
		$out .= '</tbody>';
		
		return $out;
	}
	
	private function renderFooter()
	{
		$out = '<tfoot><tr>';
		foreach( $this->headings as $heading ){
			$out .=  '<td>' . $heading . '</td>';
		}
		$out .= '</tr></tfoot>';

		return $out;
	}
}