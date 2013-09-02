<?php

/**
 * Create the code for Ext Store
 * 
 * Parameters
 * @param   string  $id The id of the store
 * @param   string  $type   [optional]The type of the proxy. Default to json
 */
class ExtyStore{
    protected $_component= Exty::COMPONENT_STORE;
    protected $_startDefine;
    protected $_defineCode;
    protected $_endDefine;
    protected $_startConstructor;
    protected $_configs;
    protected $_endCode;
    protected $_storeId;
    protected $_storeType;
    protected $_proxyType;
    protected $_fields=array();
    protected $_proxy;
    protected $_url;
    protected $_autoload=true;
    protected $_autosync=true;
    protected $_emptyText='No record to display';
    
      
    
/**
 *  
 * Parameters
 * @param   string  $id The id of the store
 * @param   string  $type   [optional]The type of the proxy. Default to json. Choose from ExtyStore::
 */
    public function __construct($id,$type=Exty::STORE_JSON_STORE){
        $this->_storeId=$id.'Store';
        $this->_storeType=$type;
        $this->_proxyType=$type;
               
        $this->_url=Yii::app()->getBaseUrl().'/';
        if(Yii::app()->controller->module->id)
                $this->_url.=Yii::app()->controller->module->id.'/';
        $this->_url.=Yii::app()->controller->id.'/'.$id.Exty::AJAX_ACTION_SUFFIX;
//        $extend=Exty::COMPONENT_STORE;
        $this->_startDefine=<<<SENCHA
Ext.define('$this->_storeId', {
    extend: 'Ext.$this->_component',
SENCHA;
        $this->_startConstructor=<<<SENCHA
 
   constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
        storeId:'$this->_storeId',
SENCHA;
     $this->_createProxy($id);
 }
 /**
 * 
  * Create the code for the proxy. Called by constructor
  */
    protected function _createProxy($id){
        $this->_proxy=<<<SENCHA
                proxy: {
                    type: '$this->_proxyType',
                    url: '$this->_url',
                    reader: {
                        type: 'json',
                        root:'root'
                    },
                    writer: {
                        root:'update',
                        encode:'true'
                    }
                },
SENCHA;
        
        return $this;
    }
   /**
 * 
  * Close the code
  */
    protected function _endCode(){
        if ($this->_autoload)
            $autoload='autoLoad:true';
        else
            $autoload='autoLoad:false';
        if ($this->_autosync)
            $autosync='autoSync:true';
        else
            $autosync='autoSync:false';
        //add a listener to display alert if store is empty and to load store in case of column sorting
        $loadFunctionCode=$this->_getLoadFunction();
        $defaultListeners=<<<SENCHA
                listeners: {
                load: {
                    fn: me.onStoreLoad,
                    scope: me
                }
            }
SENCHA;
        $this->_endCode.=<<<SENCHA
            $defaultListeners,
            $autoload,
            $autosync    
        }, cfg)]);
    },
       $loadFunctionCode,
           
});
SENCHA;
        return $this;
    }
    
    /**
     * Get Ext.define code
     * @param boolean $test [optional] True to write the code in Exty/test/store_define.js file
     * @return string
     */
    public function getDefineCode($test=false){
        //preparo i field
        $this->_createFields();
        
        //preparo la stringa di chiusura del codice
        $this->_endCode(); 
       
        $this->_defineCode=$this->_startDefine.$this->_startConstructor.$this->_configs;
        $this->_defineCode.=$this->_proxy.$this->_endCode;
        
        if ($test)
            file_put_contents ('/protected/extensions/Exty/test/store_define.js', $this->_defineCode);
            
        return $this->_defineCode;
    }
    
    /**
     * Return store id
     * @return string
     */
    public function getStoreId(){
        return $this->_storeId;
    }
    
    /*
     * STORE CONFIGURATIONS
     */
    
    /**
     * Disable store autoload
     */
    public function disableAutoload(){
        $this->_autoload=false;
        return $this;
    }
    /**
     * Disable store AUTOSYNC
     */
    public function disableAutosync(){
        $this->_autoload=false;
        return $this;
    }
    /**
     * Set page size for paginator
     * @param int $number
     */
    public function setPageSize($number){
        $this->_configs.=<<<SENCHA
                pageSize:$number,
SENCHA;
        return $this;
    }
    /**
     * Add a field to the store
     * @param string $name  The name of the field as it comes from json
     * @param string $type  [optional] The type of the field. Default to 'auto'. Choose from ExtyStore::FIELD_
     * @param string $title [optional] The title of the column to display in grid. Default = $name
     * @param string $format    [optional] The format type, in case of date field. Default to 'Ymd'
     * @return \ExtyStore
     */
    public function addField($name,$type=null, $title='',$format=''){
        if(!$title)
            $title=$name;
        if ($type==Exty::STORE_FIELD_DATE&&!$format)
            $format=  Exty::STORE_DATEFMT_YMD;
        $this->_fields[]=array(
            'name'=>$name,
            'type'=>$type,
            'format'=>$format,
            'title'=>$title
                );
        return $this;
    }
/**
 * Create fields code from fields array
 * @return \ExtyStore
 */
    protected function _createFields(){
        $code='fields:[';
        foreach ($this->_fields as $key=>$val){
            $fieldName=$val['name'];
            if($val['type'])
                $fieldType=$val['type'];
            else
                $fieldType=Exty::STORE_FIELD_AUTO;
            $code.=<<<SENCHA
                {
                    name: '$fieldName',
                    type:'$fieldType'
SENCHA;
            if ($val['format']){
                $format=$val['format'];
                $code.=",dateFormat:'$format'";
            }
            $code.='}';
            //se non è l'ultimo field aggiungo una virgola
            if($key!=(count($this->_fields)-1))
                $code.=',';
        }
        $code.='],';
        $this->_configs.=$code;
        return $this;
    
    }
    
    /**
     * Return fields array
     * Used by Grid for autocolumn
     * @return Array
     */
    public function getFields(){
        return $this->_fields;
    }
    /**
     * Sets the text for alert when store is empty
     * @param string $text  The text for the alert
     * @return \ExtyStore
     */
    public function setEmptyText($text){
        $this->_emptyText=$text;
        return $this;
    }
       
    protected function _getLoadFunctionParameters(){
        $parameters="store, records, successful, eOpts";
        return $parameters;
    }
    
    protected function _getLoadFunction(){
        $parameters=$this->_getLoadFunctionParameters();
        $code=<<<SENCHA
                onStoreLoad: function($parameters){
                    if(records.length==0)
                        Ext.MessageBox.alert('Error','$this->_emptyText');
                }
SENCHA;
        return $code;
    }
    
    
}