<?php
/**
 * Create a date column for the grid
 * 
 * Parameters
 * @param   string  $text The title for the column
 * @param   string  $dataIndex The name of the field to display, as declared in ExtyStore
 * @param   string  $format [optional] The display format for the date. Default to 'd/m/Y'
 */
class ExtyGridColumnDate extends ExtyColumnGrid{
    protected $_type=ExtyColumnGrid::COLUMN_DATE;
    /**
     * @param   string  $text The title for the column
     * @param   string  $dataIndex The name of the field to display, as declared in ExtyStore
     * @param   string  $format [optional] The display format for the date. Default to 'd/m/Y'
     */
    public function __construct($text,$dataIndex,$format=''){
        parent::__construct($this->_type,$text,$dataIndex);
        
        if ($format)
            parent::configureColumn("format:'$format'");
        else
            parent::configureColumn("format:'d/m/Y'");
            
    }
    
}