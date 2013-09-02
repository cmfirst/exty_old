<?php

/**
 * Create the code for Ext Store
 * 
 * Parameters
 * @param   string  $id The id of the store
 * @param   string  $type   [optional]The type of the proxy. Default to json
 */
class ExtyTreeStore extends ExtyStore{
    protected $_component= Exty::COMPONENT_TREESTORE;
    protected $_autosync=false;
    
      
    
/**
 *  
 * Parameters
 * @param   string  $id The id of the store
 * @param   string  $type   [optional]The type of the proxy. Default to json. Choose from ExtyStore::
 */
    public function __construct($id,$type=Exty::STORE_JSON_STORE){
        parent::__construct($id, $type);
 }
 /**
 * 
  * Create the code for the proxy. Called by constructor. Overrides parent's method
  */
 protected function _createProxy($id){
        $this->_proxy=<<<SENCHA
                proxy: {
                    type: '$this->_proxyType',
                    url: '$this->_url',
                    reader: {
                        type: 'json',
                        root:'children'
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
     * Return the parameters for load function. Overrides parent's method
     * @return string
     */
    protected function _getLoadFunctionParameters(){
        $parameters="store, node, records, successful, eOpts";
        return $parameters;
    }
 
}