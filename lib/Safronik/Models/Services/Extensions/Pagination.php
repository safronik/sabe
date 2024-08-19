<?php


namespace Safronik\Models\Services\Extensions;

trait Pagination{
    
    private int $amount = 15;
    private int $offset = 0;
    private int $page_number;
    
    public function setAmount( int $amount ): void
    {
        $this->amount = $amount;
    }
    
    public function setOffset( int $offset ): void
    {
        $this->offset = $offset;
    }
    
    public function getAmount(): int
    {
        return $this->amount;
    }
    
    public function getOffset(): int
    {
        return $this->offset;
    }
    
    protected function calculatePagination( $page )
    {
        $this->page_number = $page;
        $this->offset      = ( $this->page_number - 1 ) * $this->amount;
    }
    
    
}