<?php
/**
  * Create the code for Form Panel
  * 
  * @param string $id   The id of the Form
  */
abstract class ExtyFormpanel extends ExtyComponent{
    protected $_component=Exty::COMPONENT_FORM_PANEL;
    protected $_defaults=array();
    protected $_defaultConfigs;
    protected $_items=array();
    protected $_itemsConfig;
    protected $_initComponentCode;
    protected $_startDocked;
    protected $_dockedItems;
    protected $_comboStores=array();
    protected $_layout;
    protected $_title;
    protected $_url;
    protected $_values;
 /**
  * Create the code for Form Panel
  * 
  * @param string $id   The id of the Form
  * @param string $renderTo [Optional] The container (div) where the component has to be rendered
  */
    public function __construct($id, $renderTo=''){
        
        parent::__construct($id, $this->_component, $renderTo);
        //set starting default configurations
        $this->setFieldPadding(5);
        $this->setLabelWidth(70);
//        $this->setFieldMaxWidth(200);
        parent::setBorder(0);
        parent::setBodyPadding(10);
        parent::setLayout();
        $this->_startDocked=<<<SENCHA
                dockedItems: [
                
SENCHA;
        
    }
    /**
     * Set the starting code for init function
     * @return \ExtyForm
     */
    private function _setInitComponentCode(){
        $this->_setItems();
        if($this->_dockedItems){
            $this->_initComponentCode=<<<SENCHA
                    $this->_startDocked
                    $this->_dockedItems
                    ],
SENCHA;
        }
        $this->_initComponentCode.=<<<SENCHA
                $this->_itemsConfig
SENCHA;
        
        return $this;
    }
    /**
     * Return the Ext.define code
     * @param boolean $test [optional] True to write code to file for test 
     * @return string
     */
    public function getDefineCode($test=false){
//        $this->addDefaultToolbar();
        $this->_setDefaultConfigs();
        $this->_setInitComponentCode();
        $this->setInitCode($this->_initComponentCode);
        $this->setReadyCode(ExtY::extCreate(parent::getComponentId()));
        return parent::getDefineCode($test);
    }
    /**
     * Return the whole code for the view 
     * Ext.define of Form + Ext.onReday code with Ext.create
     * @param boolean $test [optional] True to write code to file for test
     * @return string
     */
    public function renderExt($test=false){
        $this->getDefineCode();
        return parent::renderExt($test);
    }
    /*
    * FORM CONFIGURATIONS
    */
    
    /**
     * Set the title of the form
     * @param string $title
     * @return \ExtyForm
     */
    public function setTitle($title){
        $this->_title=$title;
        $code=<<<SENCHA
                title:'$title',
SENCHA;
        $this->setConfig($code);
        return $this;
    }
    
    public function getTitle(){
        return $this->_title;
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
    
    /*
     * ITEMS
     */
    //TODO: FINIRE DI IMPLEMENTARE I FIELDSET
    /**
     * Add a fieldset to form panel
     * @param ExtyFieldset $fieldset    The fieldset object to add
     * @return \ExtyForm
     */
   public function addFieldset(ExtyFieldset $fieldset){
        $this->_items[]=$fieldset->getCode();
        $comboStores=$fieldset->getComboStores();
        $this->_comboStores=  array_merge($comboStores, $this->_comboStores);
        if(count($comboStores)>0){
            foreach ($comboStores as $store){
                parent::_addDefineCode($store, true);
            }
        }
        return $this;
    }
   
    
    /**
     * Add a textfield to form panel
     * @param string $label The label of the field
     * @param string $name  The name for the field 
     * @param boolean $required True to set the param required
     * @return \ExtyForm
     */
    public function addFieldText($label,$name,$required=false){
        $this->_addField(ExtyField::FIELD_TEXT,$label,$name,$required);
        return $this;
    }
    /**
     * Add a password field to form panel 
     * @param string $label The label of the field
     * @param string $name  The name for the field 
     * @param boolean $required True to set the param required
     * @return \ExtyForm
     */
    public function addFieldPassword($label,$name,$required=false){
        $this->_addField(ExtyField::FIELD_PASSWORD,$label,$name,$required);
        return $this;
    }
    /**
     * Add an email field to form panel. 
     * @param string $label The label of the field
     * @param string $name  The name for the field 
     * @param boolean $required True to set the param required
     * @return \ExtyForm
     */
    public function addFieldEmail($label,$name,$required=false){
        $this->_addField(ExtyField::FIELD_EMAIL,$label,$name,$required);
        return $this;
    }
    /**
     * Add a dafield to form panel. The value will be submitted in Ymd format 
     * @param string $label The label of the field
     * @param string $name  The name for the field 
     * @param boolean $required True to set the param required
     * @return \ExtyForm
     */
    public function addFieldDate($label,$name,$required=false){
        $this->_addField(ExtyField::FIELD_DATE,$label,$name,$required);
        return $this;
    }
    /**
     * Add a number field to form panel 
     * @param string $label The label of the field
     * @param string $name  The name for the field 
     * @param boolean $required True to set the param required
     * @return \ExtyForm
     */
    public function addFieldNumber($label,$name,$required=false){
        $this->_addField(ExtyField::FIELD_NUMBER,$label,$name,$required);
        return $this;
    }
    /**
     * Add a radio group to form panel. The selected value is passed as nameOfRadioGroup:valueOfItem (from $items array)
     * @param string $label   The label of the field
     * @param string $name    The name for the field
     * @param boolean $required True to set the param required
     * @param array $items  The array of items of radio group. It must be an associative array label=>name
     * @return \ExtyForm
     */
        
    public function addFieldRadioGroup($label,$name,$required, array $items){
        $this->_addField(ExtyField::FIELD_RADIOGROUP,$label,$name,$required,null,$items);
        return $this;
    }
    /**
     * Add a single checkbox to form panel. When checked, sets params 'name':true in POST request 
     * @param string $label The label of the field
     * @param string $name  The name of the field
     * @return \ExtyForm
     */
    public function addFieldCheckbox($label,$name){
        $this->_addField(ExtyField::FIELD_CHECKBOX,$label,$name,false,null);
        return $this;
    }
    /**
     * Add a checkbox group to form panel. The selected values are passed as valueOfItem(from $items array):true
     * @param string $label   The label of the field
     * @param string $name    The name for the field
     * @param boolean $required True to set the param required
     * @param array $items  The array of items of check group. It must be an associative array label=>name
     * @return \ExtyForm
     */
    public function addFieldCheckboxGroup($label,$name,$required, array $items){
        $this->_addField(ExtyField::FIELD_CHECKBOXGROUP,$label,$name,$required,null,$items);
        return $this;
    }
    /*
     * Add a combobox to form panel. 
     * @param string $label   The label of the field
     * @param string $name    The name for the field
     * @param boolean $required True to set the param required
     * @param ExtyStore $comboStore  The store for the combo values. The store must have field 'text' and 'value'
     * @return \ExtyForm
     */
    public function addFieldCombo($label,$name,$required=false, ExtyStore $comboStore){
        $this->_addField(ExtyField::FIELD_COMBO,$label,$name,$required,$comboStore);
        $this->_comboStores[]=$comboStore;
        parent::_addDefineCode($comboStore, true);
        return $this;
    }
   
    
    public function setValues(array $values){
        $this->_values=$values;
        $listener=<<<SENCHA
                beforerender: {
                        fn: me.onFormBeforeRender,
                        scope: me
                    }
SENCHA;
        $function=$this->_setFieldValues();
        parent::_addListener($listener);
        parent::_addFunction($function);
        return $this;
    }
    /**
     * Create the code for items
     * @return \ExtyForm
     */
    private function _setItems(){
        $this->_itemsConfig="items:[";
        foreach ($this->_items as $key=>$value){
            if($key!=0)
                $this->_itemsConfig.=',';
            $this->_itemsConfig.=$value;
        }
        $this->_itemsConfig.=']';
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
        parent::setConfig($this->_defaultConfigs);
        return $this;
    }
    
    /**
     * Called by all public addField functions. It creates the field code
     * @return \ExtyForm
     */
    private function _addField($type,$label,$name,$required=false,  ExtyStore $comboStore=null){
        $field=new ExtyField($type, $label, $name, $required, $comboStore);
        $this->_items[]=$field->getCode();
        return $this;
    }
     /**
     * Called by all public addBtn functions. It creates the button code
     * @return \ExtyForm
     */
    protected function _addBtn($type,$text,$action){
       $button=new ExtyButton($type,$text);
       $this->_items[]=$button->getCode();
       $btnFunction=$button->getBtnFunction();
       parent::_addFunction($btnFunction);
       return $this;
    }
    

    private function _setFieldValues(){
        $function=",onFormBeforeRender: function(component, eOpts){";
        foreach ($this->_values as $key=>$val){
            $function.=<<<SENCHA
                    if (Ext.getCmp('$key'))
                        Ext.getCmp('$key').setValue('$val');
SENCHA;
        }
        $function.='}';
        return $function;
    }
    
    public function getComboStores(){
        return $this->_comboStores;
    }
    
    
     
    /**
     * Add a button to perform form submit.
     * This method must be implemented in the extended class, 
     * according to the type of the form (classic or grid filter)
     */
    abstract public function addBtnSubmit($text);
}