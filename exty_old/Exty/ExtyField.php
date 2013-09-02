<?php
/**
     * Create the code for every field, ready to be added to items
     * @param string $type  The type of the field. Choose from costant ExtyField_FIELD_
     * @param string $label The label of the field
     * @param string $name  The name value of the field
     * @param boolean $required True to set the field required
     * @param ExtyStore $comboStore [optional] The store for the combobox, only in case of combobox field
     * @return \ExtyField
     */
class ExtyField{
    protected $_xtype;
    protected $_required;
    protected $_options;
    protected $_name;
    protected $_label;
    protected $_code;
    protected $_comboStore;
    protected $_radio=array();
    protected $_check=array();
    
    const FIELD_DISPLAY='displayfield';
    const FIELD_TEXT='textfield';
    const FIELD_PASSWORD='passwordfield';
    const FIELD_EMAIL='emailfield';
    const FIELD_NUMBER='numberfield';
    const FIELD_DATE='datefield';
    const FIELD_COMBO='combobox';
    const FIELD_RADIOGROUP='radiogroup';
    const FIELD_CHECKBOXGROUP='checkboxgroup';
    const FIELD_CHECKBOX='checkboxfield';
    const FIELD_RADIO='radiofield';
   
    /**
     * Create the code for every field, ready to be added to items
     * @param string $type  The type of the field. Choose from costant ExtyField_FIELD_
     * @param string $label The label of the field
     * @param string $name  The name value of the field
     * @param boolean $required True to set the field required
     * @param ExtyStore $comboStore [optional] The store for the combobox, only in case of combobox field
     * @return \ExtyField
     */
    public function __construct($type,$label,$name,$required=false,ExtyStore $comboStore=null){
        if ($type==self::FIELD_EMAIL || $type==self::FIELD_PASSWORD)
            $this->_xtype=self::FIELD_TEXT;
        else    
            $this->_xtype=$type;
        $this->_name=$name;
        $this->_label=$label;
        $this->_required=$required;
        if($comboStore)
            $this->_comboStore=$comboStore;
        
        $this->_code=<<<SENCHA
                {
                    xtype: '$this->_xtype',
                    fieldLabel: '$this->_label',
                    flex: 1,
                    anchor:'100%',
                    name: '$name',
                    id: '$name'
                    
SENCHA;
            if($this->_required)
                $this->_options.=<<<SENCHA
                    ,allowBlank:false
SENCHA;
            $this->_options.=$this->_getDefaultExtraOptions($type);
            $this->_code.=$this->_options;
            return $this;
    }
    /**
     * Return the field code
     * @return string
     */
    public function getCode(){
        $this->_code.='}';
        return $this->_code;
    }
    /**
     * Return extra default parameters based on the type of the field
     * @param string $type The type of the field. 
     * @return type
     */
    protected function _getDefaultExtraOptions($type){
        $options='';
        switch ($type){
            case self::FIELD_NUMBER:
                $options.=<<<SENCHA
                    ,hideTrigger: true,
                    decimalSeparator: ','
SENCHA;
            break;
            case self::FIELD_DATE:
                $options.=<<<SENCHA
                    ,format: 'd/m/Y',
                    submitFormat: 'Ymd'
SENCHA;
                break;
            case self::FIELD_COMBO:
                $storeComboId=$this->_comboStore->getStoreId();
                $options.=<<<SENCHA
                        ,displayField: 'text',
                        queryMode: 'local',
                        store: '$storeComboId',
                        valueField: 'value'
SENCHA;
                break;
            case self::FIELD_PASSWORD:
                $options.= ",inputType: 'password'";
                break;
            case self::FIELD_EMAIL:
                $options.=<<<SENCHA
                    ,vtype: 'email',
                    validateOnChange:false
SENCHA;
                break;
            case self::FIELD_RADIOGROUP:
                $xtype=self::FIELD_RADIO;
                foreach ($this->_radio as $label=>$name){
                    $radioItems[]=<<<SENCHA
                            {
                                xtype: '$xtype',
                                boxLabel: '$label',
                                name:'$this->_name',
                                inputValue: '$name'
                            }
SENCHA;
                }
                $options.=",items:[";
                foreach($radioItems as $key=>$value){
                    $options.=$value;
                    if($key<(count($radioItems)-1))
                        $options.=",";
                }
                $options.="]";
                break;
                case self::FIELD_CHECKBOXGROUP:
                $xtype=self::FIELD_CHECKBOX;
                foreach ($this->_check as $label=>$name){
                    $radioItems[]=<<<SENCHA
                            {
                                xtype: '$xtype',
                                boxLabel: '$label',
                                name:'$name',
                                inputValue: true,
                                fieldLabel:''
                            }
SENCHA;
                }
                $options.=",items:[";
                foreach($radioItems as $key=>$value){
                    $options.=$value;
                    if($key<(count($radioItems)-1))
                        $options.=",";
                }
                $options.="]";
                break;
                case self::FIELD_CHECKBOX:
                $options.= <<<SENCHA
                        ,fieldLabel: '',
                        boxLabel: '$this->_label',
                        inputValue:true
SENCHA;
                break;
       }
            return $options;
    }

}