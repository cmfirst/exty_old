<?php

class ExtyToolbarFieldSearch{
    protected $_type;
    protected $_iconCls;
    protected $_code;
    protected $_gridStoreId;
    protected $_function;
    private $_xtype=Exty::XTYPE_TEXTFIELD;
    
    const SEARCH_CHANGE='change';
    const SEARCH_ENTER='specialkey';
 
    public function __construct($type){
        $this->_type=$type;
        
        $this->_code=<<<SENCHA
                {
                    xtype: '$this->_xtype',
                    id: 'search',
                    emptyText:'search...',
                    width: 250,
                    name: 'search',
                    listeners: {
                        $this->_type: {
                            fn: me.onCerca,
                            scope: me
                        }
                    }
                 }
SENCHA;
       
            return $this;
    }
    /**
     * Return the textfield code
     * @return string
     */
    public function getCode(){
        return $this->_code;
    }
    
    public function getSearchFunction(){
        switch($this->_type){
            case self::SEARCH_ENTER:
                $this->_function=<<<SENCHA
            ,onCerca: function(field, e, eOpts) {
                if (e.getKey() == e.ENTER || e.getKey() == e.TAB) {
                    var toolbar=field.up('toolbar');
                    var grid=toolbar.ownerCt;
                    var store=grid.store;
                    store.load({
                        params: {
                            search: field.value
                        }
                    });
                }
            }
            
SENCHA;
                break;
            case self::SEARCH_CHANGE:
                $this->_function=<<<SENCHA

            ,onCerca: function(field, newValue, oldValue, eOpts) {
                    if (newValue.length>=3 || newValue.length==0){
                        var toolbar=field.up('toolbar');
                        var grid=toolbar.ownerCt;
                        var store=grid.store;
                        store.load({
                            params: {
                                search: newValue
                            }
                        });
                    }
                }
            
SENCHA;
                break;
            
        }
        
            return $this->_function;
        
    }
    
   

}