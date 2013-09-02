<?php
/**
  * Create the code for Tab Panel
  * 
  * @param string $id   The id of the Tab Panel
  */
class ExtyTabpanel extends ExtyComponent{
    protected $_component=Exty::COMPONENT_TAB_PANEL;
    protected $_tabs=array();
    protected $_tabsConfig;
    protected $_required=array();
    protected $_requiredCode;
    protected $_initComponentCode;
    protected $_toolbars=array(); //array di oggetti toolbar
    


 /**
  * Create the code for Tab Panel
  * 
  * @param string $id   The id of the Tab Panel
  */
    public function __construct($id){
        parent::__construct($id, $this->_component);
        parent::setConfig('forceFit:true,');
    }
    /**
     * Set the starting code for init function
     * @return \ExtyGrid
     */
    protected function _setInitComponentCode(){
        $this->_setTabs();

        $this->_initComponentCode.=<<<SENCHA
                $this->_tabsConfig
SENCHA;
        return $this;
    }
    /**
     * Return the Ext.define code
     * @param boolean $test [optional] True to write code to file for test 
     * @return string
     */
    public function getDefineCode($test=false){
        $this->_setInitComponentCode();
        $this->_setRequired();
        parent::setConfig($this->_requiredCode);
        $this->setInitCode($this->_initComponentCode);
        $this->setReadyCode(Exty::extCreate(parent::getComponentId()));
        //if the grid has toolbars, add the addDocked code
        if (count($this->_toolbars)>0){
            foreach($this->_toolbars as $toolbar){
                $this->setReadyCode(Exty::extCreate($toolbar->getComponentId()));
                $this->setReadyCode(Exty::addDocked(parent::getComponentId(), $toolbar->getComponentId()));
            }
                
        }
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
    * TABPANEL CONFIGURATIONS
    */
    
    /**
     * Set fullscreen Tabpanel
     * @param boolean $boolean  True to make grid rendered full screen
     * @return \ExtyTabpanel
     */
    public function setFit($boolean){
        $val=boolString($boolean);
        $code=<<<SENCHA
                forceFit:$val,
SENCHA;
        $this->setConfig($code);
        return $this;
    }
    
    /**
     * Add a column to the grid
     * @param ExtyColumnGrid $column
     * @return \ExtyGrid
     */
    public function addTab(ExtyComponent &$component, $tabTitle){
        $type=$component->getComponentType();
        if($type==Exty::COMPONENT_GRID_PANEL){
            $xtypeMain=$component->getComponentId();
        }else{
            $xtypeMain='panel';
            $xtypeItem=$component->getComponentId();
        }
        $tabCode=<<<SENCHA
                {
                    xtype: '$xtypeMain',
                    title: '$tabTitle'
SENCHA;
        if($xtypeItem){
            $tabCode.=<<<SENCHA
                    ,items: [
                        {
                            xtype: '$xtypeItem'
                        }
                    ]
SENCHA;
        }
            $tabCode.='}';
        $this->_tabs[]=$tabCode;
        $this->_required[]=$xtypeItem;
        
        parent::_addDefineCode($component);
        return $this;
    }
    
    /**
     * Create the code for items
     * @return \ExtyTabpanel
     */
    protected function _setTabs(){
        $this->_tabsConfig="items:[";
        foreach ($this->_tabs as $key=>$value){
            if($key!=0)
                $this->_tabsConfig.=',';
            $this->_tabsConfig.=$value;
        }
        $this->_tabsConfig.=']';
        return $this;
    }
/**
     * Create the code for requires config
     * @return \ExtyTabpanel
     */
    protected function _setRequired(){
        $this->_requiredCode="required:[";
        foreach($this->_required as $key=>$val){
            if($key!=0)
                $this->_requiredCode.=',';
            $this->_requiredCode.="'$val'";
        }
        $this->_requiredCode.='],';
        return $this;
    }

    /**
     * Set the active tab
     * @param int $tabIndex The index of the tab (0 based)
     * @return \ExtyTabpanel
     */
    public function setActiveTab($tabIndex){
        parent::setConfig("activeTab: $tabIndex,");
        return $this;
    }
    
    /**
     * Hide the background color on the top bar
     * @return \ExtyTabpanel
     */
    public function setNoTitleBar(){
        parent::setConfig("plain: true,");
        return $this;
    }
}