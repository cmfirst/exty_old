<?php
/**
 * Create the code for editor for columns
 * 
 * Parameters
 * @param   string  $type The type of editor. Choose from ExtyColumnGrid::EDITOR_
 * @param   boolean $required   True to set the field as required
 * @param ExtyStore [optional]$comboId The id of the store to create in case of combobox editor
 */
class ExtyGridColumnEditor{
    protected $_type;
    protected $_code;
    protected $_storeCombo;
    
    /**
 * Parameters
 * @param   string  $type The type of editor. Choose from ExtyColumnGrid::EDITOR_
 * @param   boolean $required   True to set the field as required
 * @param ExtyStore [optional]$comboId The id of the store to create in case of combobox editor
 */
    public function __construct($type, $required=false,ExtyStore $comboStore=null){
        $this->_type=$type;
        $allowBlak=$required?'false': 'true';
        $this->_code=<<<SENCHA
                ,editor: {
                        xtype: '$type',
                        allowBlank: $allowBlak,
                        msgTarget: 'title'
SENCHA;
        $options='';
        switch ($type){
            case 'numberfield':
                $options=<<<SENCHA
                    ,hideTrigger: true,
                    decimalSeparator: ','
SENCHA;
            break;
            case 'datefield':
                $options= ",format: 'd/m/Y'";
                break;
            case 'combobox':
                $this->_storeCombo=$comboStore;
                $storeComboId=$this->_storeCombo->getStoreId();
                $options=<<<SENCHA
                        ,displayField: 'text',
                        queryMode: 'local',
                        store: '$storeComboId',
                        valueField: 'value'
SENCHA;
                break;
            default: 
                $options='';
            }
            $this->_code.=$options.'}';
            return $this;
    }
    /**
     * Return the editor code
     * @return string
     */
    public function getCode(){
        return $this->_code;
    }
    /**
     * Return the store of the combobox
     * @return /ExtyStore
     */
    public function getComboStore(){
        return $this->_storeCombo;
    }
    
     /**
    * Return the type of the editor
    * @return string
    */
   public function getType(){
       return $this->_type;
   }
}