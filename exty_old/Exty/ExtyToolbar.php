<?php
/**
  * Create the code for Toolbar
  * 
  * @param string $id   The id of the Toolbar
  */
class ExtyToolbar extends ExtyComponent{
    protected $_component=Exty::COMPONENT_TOOLBAR;
    protected $_items=array();
    protected $_itemsConfig;
    protected $_initComponentCode;
    protected $_dockedPosition;
    protected $_forms=array();
 /**
  * Create the code for Toolbar
  * 
  * @param string $id   The id of the Toolbar
  * @param string $dockedPosition [Optional] The position where the toolbar should be alligned. Default to TOP. Choose from Exty::DOCKED_POSITION_
  * @param string $renderTo [Optional] The container (div) where the component has to be rendered
 
  */
    public function __construct($id,$dockedPosition=Exty::DOCKED_POSITION_TOP, $renderTo=''){
        $this->_setDockedPosition($dockedPosition);
        parent::__construct($id, $this->_component, $renderTo);
               
    }
    /**
     * Set the starting code for init function
     * @return \ExtyToolbar
     */
    private function _setInitComponentCode(){
        $this->_setItems();
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
     * ITEMS/BUTTONS
     */
    /**
     * Add a standard button add. The button will show the given form o will call the actionEdit of the controller
     * @param string $text  The text to be displayed on the button
     * @param ExtyForm  $form   [Optional] The form to open in window
     * @return \ExtyToolbar
     */
    public function addBtnAdd($text,  ExtyForm $form=null){
       $this->_addBtn(ExtyButton::BUTTON_ADD, $text,$form);
       return $this;
    }
    
    /**
     * Add a standard button edit, to edit the record with a given form in a window
     * To edit the record in a new action, use $column->setLink() method
     * @param string $text  The text to be displayed on the button
     * @param ExtyForm  $form   The form to open in window
     * @return \ExtyToolbar
     */
    public function addBtnEdit($text, ExtyForm $form){
       $this->_addBtn(ExtyButton::BUTTON_EDIT, $text, $form);
       return $this;
    }
    /**
     * Add a standard button delete. 
     * @param string $text  The text to be displayed on the button
     * @return \ExtyToolbar
     */
    public function addBtnDelete($text){
       $this->_addBtn(ExtyButton::BUTTON_DELETE, $text);
       return $this;
    }
    /**
     * Add a standard button export xls. 
     * @param string $text  The text to be displayed on the button
     * @return \ExtyToolbar
     */
    public function addBtnExportXls($text){
       $this->_addBtn(ExtyButton::BUTTON_EXPORT_XLS, $text);
       return $this;
    }
    /**
     * Add a standard button export pdf. 
     * @param string $text  The text to be displayed on the button
     * @return \ExtyToolbar
     */
    public function addBtnExportPdf($text){
       $this->_addBtn(ExtyButton::BUTTON_EXPORT_PDF, $text);
       return $this;
    }
    /**
     * Add a custom button with a custom function
     * @param string $text  The text to be displayed on the button
     * @param string $funcName  The of the javascript function to call. 
     * The function has to be writter in a js file included in the layout
     * @return \ExtyToolbar
     */
    public function addBtnCustom($text, $funcName){
       $this->_addBtn(ExtyButton::BUTTON_CUSTOM, $text, null, $funcName);
       return $this;
    }
    
    /**
     * Add a search field to the toolbar. The search will be performed on every key pressed (after 3 chars)
     * The field performs the store loading.
     * The search text will be send in 'search' param
     * @return \ExtyToolbar
     */
    public function addSearchOnChange(){
         $this->_addSearch(ExtyToolbarFieldSearch::SEARCH_CHANGE);
        return $this;
                
    }
    /**
     * Add a search field to the toolbar. The search will be performed on key ENTER pressed
     * The field performs the store loading.
     * The search text will be send in 'search' param
     * @param ExtyStore $store
     * @return \ExtyToolbar
     */
    public function addSearchOnEnter(){
        $this->_addSearch(ExtyToolbarFieldSearch::SEARCH_ENTER);
        return $this;
    }
    
 
    /**
     * Create the code for items
     * @return \ExtyToolbar
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
    
    private function _setDockedPosition($dockedPosition){
        $code=<<<SENCHA
                dock:'$dockedPosition',
SENCHA;
        parent::setConfig($code);
    }

   private function _addSearch($type){
        $searchField=new ExtyToolbarFieldSearch($type);
        $this->_items[]=$searchField->getCode();
        $searchFunction=$searchField->getSearchFunction();
        parent::_addFunction($searchFunction);
        return;
    }
    
    private function _addBtn($type,$text, ExtyForm $form=null, $funcName=''){
       $button=new ExtyButton($type,$text, $form, $funcName);
       if($form){
           if (array_search($form->getComponentId(), $this->_forms)===false){
                parent::_addDefineCode ($form);
                $this->_forms[]=$form->getComponentId();
           }
       }
       $this->_items[]=$button->getCode();
       $btnFunction=$button->getBtnFunction();
       parent::_addFunction($btnFunction);
       return $this;
    }

}