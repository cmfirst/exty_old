<?php
/**
 * Create a simple column for the grid
 * 
 * Parameters
 * @param   string  $text The title for the column
 * @param   string  $dataIndex The name of the field to display, as declared in ExtyStore
 */
class ExtyGridColumnTree extends ExtyColumnGrid{
    protected $_type=ExtyColumnGrid::COLUMN_TREE;
/**
 * 
 * @param   string  $test The title for the column
 * @param   string  $dataIndex The name of the field to display, as declared in ExtyStore
 */
    public function __construct($text,$dataIndex){
        parent::__construct($this->_type,$text,$dataIndex);
    }
    
}