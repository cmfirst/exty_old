<?php

class ExtyFieldset{
    private $_xtype=Exty::XTYPE_FIELDSET;
    protected $_code;
    protected $_title;
    protected $_border=0;
    protected $_items=array();
    protected $_itemsConfig;
    protected $_defaults=array();
    protected $_defaultConfigs;
    protected $_comboStores=array();
 
    public function __construct($title=null){
        if($title){
            $this->_title=$title;
            $this->showBorder();
        }
        $this->_code=<<<SENCHA
                {
                    xtype: '$this->_xtype',
                    border:$this->_border,
                    anchor: '100%',
                    padding:20,
SENCHA;
        $options='';
        if ($this->_title)
            $options=<<<SENCHA
                title:'$this->_title',
SENCHA;
            $this->_code.=$options;
            return $this;
    }
    /**
     * Return the button code
     * @return string
     */
    public function getCode(){
        $this->_setItemsConfig();
        $this->_setDefaultConfigs();
        if($this->_defaultConfigs!='')
            $this->_code.=$this->_defaultConfigs;
            
        $this->_code.=<<<SENCHA
                $this->_itemsConfig
                }
SENCHA;
        return $this->_code;
    }

    public function addFieldDisplay($label,$name){
        $this->_addField(ExtyField::FIELD_DISPLAY,$label,$name,false);
        return $this;
    }
    public function addFieldText($label,$name,$required=false){
        $this->_addField(ExtyField::FIELD_TEXT,$label,$name,$required);
        return $this;
    }
    public function addFieldPassword($label,$name,$required=false){
        $this->_addField(ExtyField::FIELD_PASSWORD,$label,$name,$required);
        return $this;
    }
    public function addFieldEmail($label,$name,$required=false){
        $this->_addField(ExtyField::FIELD_EMAIL,$label,$name,$required);
        return $this;
    }
    public function addFieldDate($label,$name,$required=false){
        $this->_addField(ExtyField::FIELD_DATE,$label,$name,$required);
        return $this;
    }
    public function addFieldNumber($label,$name,$required=false){
        $this->_addField(ExtyField::FIELD_NUMBER,$label,$name,$required);
        return $this;
    }
    public function addFieldCombo($label,$name,$required=false,  ExtyStore $comboStore){
        $this->_addField(ExtyField::FIELD_COMBO,$label,$name,$required,$comboStore);
        $this->_comboStores[]=$comboStore;
        return $this;
    }
    public function addFieldRadioGroup($label,$name,$required, array $items){
        $this->_addField(ExtyField::FIELD_RADIOGROUP,$label,$name,$required,null,$items);
        return $this;
    }
    public function addFieldCheckbox($label,$name){
        $this->_addField(ExtyField::FIELD_CHECKBOX,$label,$name,false,null);
        return $this;
    }
    public function addFieldCheckboxGroup($label,$name,$required, array $items){
        $this->_addField(ExtyField::FIELD_CHECKBOXGROUP,$label,$name,$required,null,$items);
        return $this;
    }
    
    private function _addField($type,$label,$name,$required=false,  ExtyStore $comboStore=null,array $items=null){
        if($type==ExtyField::FIELD_RADIOGROUP)
            $field=new ExtyFieldRadioGroup ($label, $name,$required,$items);
        else if($type==ExtyField::FIELD_CHECKBOXGROUP)
            $field=new ExtyFieldCheckboxGroup ($label, $name,$required,$items);
        else
            $field=new ExtyField($type, $label, $name, $required, $comboStore);
        $this->_items[]=$field->getCode();
        return $this;
    }
    private function _setItemsConfig(){
        $this->_itemsConfig="items:[";
        foreach ($this->_items as $key=>$value){
            if($key!=0)
                $this->_itemsConfig.=',';
            $this->_itemsConfig.=$value;
        }
        $this->_itemsConfig.=']';
        return $this;
    }
    public function showBorder(){
        $this->_border=1;
        return $this;        
    }
    
    /**
     * Set the default label width for all fields
     * @param int $int 
     * @return \ExtyForm
     */
    public function setLabelWidth($int){
        $this->_defaults[]="labelWidth: $int";
        return $this;
    }
    
     /**
     * Set the default padding for all fields
     * @param int $int
     * @return \ExtyForm
     */
    public function setFieldPadding($int){
        $this->_defaults[]="padding: $int";
        return $this;
        
    }
    /**
     * Set the default max width of all fields
     * @param int $int
     * @return \ExtyForm
     */
    public function setFieldMaxWidth($int){
        $this->_defaults[]="maxWidth: $int";
        return $this;
        
    }
    /**
     * Set the default target of error message
     * @param string    $target Select from Exty::FORM_MSG_TARGET_
     * @return \ExtyForm
     */
    public function setErrorTarget($target){
        $this->_defaults[]="msgTarget: '$target'";
        return $this;
        
    }

    /**
     * Create the code for default configs
     * @return \ExtyForm
     */
    private function _setDefaultConfigs(){
        $this->_defaultConfigs="defaults:{";
        foreach ($this->_defaults as $key=>$value){
            if($key!=0)
                $this->_defaultConfigs.=',';
            $this->_defaultConfigs.=$value;
        }
        $this->_defaultConfigs.='},';
        return $this;
    }
    
    public function getComboStores(){
        return $this->_comboStores;
    }
}