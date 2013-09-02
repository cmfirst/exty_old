<?php
 /**
 * Create a number column for the grid
 * 
 * @param string $text The title for the column
 * @param string $dataIndex The name of the field to display, as declared in ExtyStore
 * @param boolean $hideNullValue [optional] False to show all value. Default to true, hide value=0 and value=''
 * @param boolean $currency [optional] True to render the value with currency. Default to false
*/
class ExtyGridColumnNumber extends ExtyColumnGrid{
    protected $_type=ExtyColumnGrid::COLUMN_NUMBER;
    protected $_currency='Ext.util.Format.currency';
    protected $_hideNull;
    protected $_rendererFunction;
 /**   
 * @param string $text The title for the column
 * @param string $dataIndex The name of the field to display, as declared in ExtyStore
 * @param boolean $hideNullValue [optional] False to show all value. Default to true, hide value=0 and value=''
 * @param boolean $currency [optional] True to render the value with currency. Default to false
*/ 
    public function __construct($text,$dataIndex,$hideNullValue=true,$currency=false){
        parent::__construct($this->_type,$text,$dataIndex);
        
        //se voglio nascondere valori nulli: chiamo hideNullVale e imposto la rendererFunction
        if($hideNullValue){
            $this->_hideNullValue($currency);
            parent::setRenderer($this->_rendererFunction);
        }else{
        //se voglio mostrare '€': chiamo setCurrency e imposto la rendererFunction
            if($currency){
               $this->_setCurrency(); 
                parent::setRenderer($this->_rendererFunction);
            }
        }
        
        //di default la numbercolumn è allineata a destra:
        parent::setAlign('right');
    }
    /**
     * Hide value=0 and value=''
     * @param boolean $currency
     * @return \ExtyNumbercolumn
     */
    private function _hideNullValue($currency){
        //di default, è considerato null value lo 0
        if (!$currency)
            $return="Ext.util.Format.number(value,'0.0,0')";
        else
            $return="$this->_currency(value)";
        //codice per la renderer function
        //controlla se value è ==0 o =='' 
        $this->_rendererFunction=<<<SENCHA
                if (value==0 || value=='')
                    return '';
                else 
                        return $return;
                    
SENCHA;
        return $this;
    }
    /**
     * Set value rendered with currency
     * @return \ExtyNumbercolumn
     */
    private function _setCurrency(){
        $this->_rendererFunction=<<<SENCHA
                return $this->_currency(value);
SENCHA;
        return $this;
    }
}