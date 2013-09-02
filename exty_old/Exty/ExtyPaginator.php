<?php
/**
  * Create the code for PagingToolbar
  * 
  * @param string $id   The id of the Toolbar
  * @param ExtyStore $gridStore   The store of the grid
  * @param string $dockedPosition The position where the paginator should be alligned. Choose from Exty::DOCKED_POSITION_
  */
class ExtyPaginator extends ExtyComponent{
    protected $_component=Exty::COMPONENT_PAGINATOR;
    protected $_storeId;
    protected $_dockedPosition;
    
 /**
  * Create the code for PagingToolbar
  * 
  * @param string $id   The id of the Toolbar
  * @param ExtyStore $gridStore   The store of the grid
  * @param string $dockedPosition The position where the paginator should be alligned. Choose from Exty::DOCKED_POSITION_
  */
    public function __construct($id, ExtyStore $gridStore, $dockedPosition){
        $this->_dockedPosition=$dockedPosition;
        $this->_storeId=$gridStore->getStoreId();
        parent::__construct($id, $this->_component);
        $this->setDefaultOptions();
               
    }
    /**
     * Return the Ext.define code
     * @param boolean $test [optional] True to write code to file for test 
     * @return string
     */
    public function getDefineCode($test=false){
        $this->setReadyCode(ExtY::extCreate(parent::getComponentId()));        
        return parent::getDefineCode($test);
    }
    /**
     * Set default options to the paging toolbar
     */
    private function setDefaultOptions(){
        $code=<<<SENCHA
                store:'$this->_storeId',
                displayInfo: true,
                dock:'$this->_dockedPosition',
SENCHA;
        parent::setConfig($code);
    }
    /**
     * Return the docked position of the paging toolbar
     * @return string
     */
    public function getDockedPosition(){
        return $this->_dockedPosition;
    }
}