<?php



/**
 * Singleton class
 *
 */
class Exty
{
    protected static $_startJScript;
    protected static $_endJScript;
    protected static $_startReadyFunc;
    protected static $_readyFunc;
    protected static $_endReadyFunc;
    protected static $_container;
    protected static $_components=array();
    protected static $_stores=array();
    
    const AJAX_ACTION_SUFFIX='Request';
    
    const LOGIN_SUBMIT_AJAX='ajax';
    const LOGIN_SUBMIT_STANDARD='standard';
    
    const STORE_FIELD_FLOAT = 'float';
    const STORE_FIELD_DATE = 'date';
    const STORE_FIELD_INT = 'int';
    const STORE_FIELD_BOOLEAN = 'boolean';
    const STORE_FIELD_STRING = 'string';
    const STORE_FIELD_AUTO = 'auto';
    const STORE_JSON_STORE = 'ajax';
    const STORE_JSONP_STORE = 'jsonp';
    const STORE_DATEFMT_YMD = 'Ymd';
    const STORE_DATEFMT_DMY = 'd/m/Y';
    const STORE_DATEFMT_MDY = 'm/d/Y';
    
    const COMPONENT_TOOLBAR='toolbar.Toolbar';
    const COMPONENT_PAGINATOR='toolbar.Paging';
    const COMPONENT_CONTAINER='container.Container';
    const COMPONENT_FORM_PANEL='form.Panel';
    const COMPONENT_GRID_PANEL='grid.Panel';
    const COMPONENT_TAB_PANEL='tab.Panel';
    const COMPONENT_TREE_PANEL='tree.Panel';
    const COMPONENT_WINDOW='window.Window';
    const COMPONENT_STORE='data.Store';
    const COMPONENT_TREESTORE='data.TreeStore';
    
    const XTYPE_BUTTON='button';
    const XTYPE_TEXTFIELD='textfield';
    const XTYPE_FIELDSET='fieldset';
    
    const DOCKED_POSITION_TOP='top';
    const DOCKED_POSITION_BOTTOM='bottom';
    
    const LAYOUT_HORIZONTAL_ALIGN="type:'hbox'";
    const LAYOUT_VERTICAL_ALIGN="type:'anchor'";
    const LAYOUT_TABLE_2COLS="type:'table',columns:2";
    const LAYOUT_TABLE_3COLS="type:'table',columns:3";
    const LAYOUT_FIT="type:'fit'";
    
    const FORM_MSG_TARGET_UNDER="under";
    const FORM_MSG_TARGET_SIDE="side";
    const FORM_MSG_TARGET_TOOLTIP="qtip";
    const FORM_MSG_TARGET_TITLE="title";
    

    public static function startScript(){
        self::_createContainer();
        
        if(!self::$_startJScript){
            //<script> and MainContainer define code
            self::$_startJScript='<SCRIPT type="text/javascript">';
            self::$_startJScript.=self::$_container->getDefineCode();
        }
        return self::$_startJScript;
    }
    public static function startReadyFunc(){
        if(!self::$_startReadyFunc)
            self::$_startReadyFunc=<<<SENCHA
Ext.onReady(function(){
                var mainContainer=Ext.create('mainContainer');
                
SENCHA;
        return self::$_startReadyFunc;
    }
    public static function endReadyFunc(){
        if(!self::$_endReadyFunc)
            self::$_endReadyFunc=<<<SENCHA
});
SENCHA;
        return self::$_endReadyFunc;
    }
    public static function endScript(){
        if(!self::$_endJScript)
            self::$_endJScript='</SCRIPT>';
        return self::$_endJScript;
    }
    public static function extCreate($idComponent){
        $senchaCode='';
        if (!in_array($idComponent, self::$_components)){
            self::$_components[]=$idComponent;
            $senchaCode="var $idComponent=Ext.create('$idComponent');";
        }
        return $senchaCode;
    }
    public static function containerAdd($idComponent){
        $senchaCode="mainContainer.add($idComponent);";
        return $senchaCode;
    }
    /**
     * Set the sencha code for ready funciton to add a child docked component to a parent
     * @param string $idParent    The id of the parent component (es id of the Grid)
     * @param string $idChild     The id of the child component (es id of the toolbar)
     * @param string $dockedPos   The position to dock the child (top,botton,left,right)
     * @return string
     */
    public static function addDocked($idParent,$idChild){
        $senchaCode="$idParent.addDocked($idChild);";
        return $senchaCode;
    }
    
    protected static function _createContainer(){
        if(!self::$_container)
            self::$_container=new ExtyContainer('mainContainer', 'content');
        return;
    }
    public static function getAllComponents(){
        return self::$_components;
    }
    
    public static function setReadyCode($code){
        self::$_readyFunc.=$code;
        return;
    }
    public static function getReadyCode(){
        return self::$_readyFunc;
    }
    
        
}