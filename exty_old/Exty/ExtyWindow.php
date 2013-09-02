<?php
/**
  * Create the code for Window
  * 
  * @param string $id   The id of the Window
  * @param string $renderTo [Optional] The container (div) where the component has to be rendered
  */
class ExtyWindow extends ExtyComponent{
    protected $_component=Exty::COMPONENT_WINDOW;
    protected $_items=array();
    protected $_itemsConfig;
    protected $_initComponentCode;
    protected $_startDocked;
    protected $_dockedItems;
    protected $_autoshow=true;
 /**
  * Create the code for Form Panel
  * 
  * @param string $id   The id of the Grid
  * @param string $renderTo [Optional] The container (div) where the component has to be rendered
  */
    public function __construct($id, $renderTo=''){
        
        parent::__construct($id, $this->_component, $renderTo);
        
        $this->_startDocked=<<<SENCHA
                dockedItems: [
                
SENCHA;
        
    }
    /**
     * Set the starting code for init function
     * @return \ExtyWin
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
        if ($this->_autoshow)
            parent::setConfig ('autoShow:true,');
        else
            parent::setConfig ('autoShow:false,');
        $this->_setInitComponentCode();
        $this->setInitCode($this->_initComponentCode);
        $this->setReadyCode(ExtY::extCreate(parent::getComponentId()));
        
        return parent::getDefineCode($test);
    }
    /**
     * Return the whole code for the view 
     * Ext.define of Store and Ggrid + Ext.onReday code with Ext.create
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
     * @return \ExtyWindow
     */
    public function setTitle($title){
        $code=<<<SENCHA
                title:'$title',
SENCHA;
        $this->setConfig($code);
        return $this;
    }
    /**
     * Disable autoshow
     * @return \ExtyWindow
     */
    public function disableAutoshow(){
        $this->_autoshow=false;
        return $this;
    }
 
    
    /*
     * ITEMS
     */
    
 
    

    /**
     * Create the code for items
     * @return \ExtyWindow
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

    

}