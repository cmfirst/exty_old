<?php

/*
 * Classe base per tutti i componenti
 * il costruttore riceve id e tipo di componente
 * dispone dei metodi per settare le principali configurazioni comuni
 * metodo getDefineCode per ottenere solo il codice per il define
 * metodo getReadyCode per ottenere solo il codice per il create
 * metodo renderExt per ottenere tutto il codice per la vista (script+define+onReady+create+chiusura codice)
 * il codice Ã¨ suddiviso in:
 *      startDefine (riga define + extend, standard)
 *      configs (configurazioni, vengono implementate man mano con i vari metodi set)
 *      startInit (righe di inizio dell'initComponent, standard)
 *      initCode (configurazioni che vanno dentro l'initComponent, 
 *              viene implementato con il metodosetInitCode chiamato dalle classi che estendono ExtYComponent)
 *      endInit (righe di chiusura dell'initComponent, standard)
 *      endDefine (righe di chiusura del define, standard)
 */

class ExtyComponent {

    protected $_startDefine;
    protected $_idComponent;
    protected $_typeComponent;
    protected $_endDefine;
    protected $_configs;
    protected $_initCodeAdded = array();
    protected $_initCode;
    protected $_dockedItems = array();
    protected $_dockedCode;
    protected $_startInit;
    protected $_endInit;
    protected $_defineCode;
    protected $_readyCode;
    protected $_functions;
    protected $_width;
    protected $_height;
    public $senchaCode;
    protected $_addedComponent = array();
    protected $_definedComponent = array();
    public $parent;
    protected $_listeners = array();

    public function __construct($id, $component, $renderTo = '') {
        $this->_idComponent = $id;
        $this->_typeComponent = $component;
//chiama forzatamente setRender
//TODO: valutare se passargli un terzo paramento facoltativo 
//per indicare il container in cui renderizzarlo
        if ($renderTo != '')
            $this->setRenderTo($renderTo);
        $this->_startDefine = <<<SENCHA
Ext.define('$this->_idComponent', {
    extend: 'Ext.$this->_typeComponent',
    alias: 'widget.$this->_idComponent',
    id: '$this->_idComponent',
SENCHA;
        $this->_startInit = <<<SENCHA
                
    initComponent: function() {
        var me = this;
            Ext.applyIf(me, {
SENCHA;
        $this->_initCode = '';
        $this->_endInit = <<<SENCHA
               
            });
        me.callParent(arguments);
    }
SENCHA;
        $this->_endDefine = '});';
    }

    public function setInitCode($initCode) {
//        $this->_initCode .= $initCode;
        $this->_initCodeAdded[] = $initCode;
        return $this;
    }

    protected function _setInitForDefine() {

//        echo "<pre>";
//        var_dump($this->_initCodeAdded);
//        die();
        foreach ($this->_initCodeAdded as $key => $code) {
            if ($key != 0)
                $this->_initCode.=',';
            $this->_initCode.=$code;
        }
        return $this;
    }

    public function setConfig($code) {
        $this->_configs.=$code;
        return $this;
    }

    public function getConfig() {
        return $this->_configs;
    }

    public function getComponentId() {
        return $this->_idComponent;
    }

    public function getComponentType() {
        return $this->_typeComponent;
    }

    /**
     * Add the define code of the given component or store
     * @param ExtyComponent|ExtyStore $component
     * @param boolean $addReadyCode [Optional] Default to false. True if it's a component added to another
     * Es. Toolbar, Filterpanel...
     * @return \ExtyComponent
     */
    protected function _addDefineCode($component, $addReadyCode = false) {
//se è uno store o se è un componente aggiunto a un altro, aggiungo anche il ready code
        if ($component instanceof ExtyStore) {
            if (!in_array($component->getStoreId(), Exty::getAllComponents())) {
                $this->setReadyCode(Exty::extCreate($component->getStoreId()));
                $this->_defineCode.=$component->getDefineCode();
                return $this;
            }
        } else {
            if (!in_array($component->getComponentId(), Exty::getAllComponents())) {
                $this->_defineCode.=$component->getDefineCode();
                if ($addReadyCode) {
                    $this->setReadyCode(Exty::extCreate($component->getComponentId()));
                }
                return $this;
            }
//            return $this;
        }
    }

    /**
     * Add a customer listener to the component
     * @param string $event The name of the event
     * @param string $funcName  The javascript function to call
     * The function has to be writter in a js file included in the layout
     * @return \ExtyComponent
     */
    public function addCustomListener($event, $funcName) {
        $funcName = str_replace(' ', '', $funcName);
        $listener = <<<SENCHA
                    $event: {
                        fn: me.on$funcName,
                        scope: me
                    }
SENCHA;
        $this->_addListener($listener);
        $function = <<<SENCHA
                ,on$funcName : function(){
                    $funcName();
                }
SENCHA;
        $this->_addFunction($function);
        return $this;
    }

    /**
     * Add the given code to the array of listeners
     * @param string $code
     * @return \ExtyComponent
     */
    protected function _addListener($code) {
        $this->_listeners[] = $code;
        return $this;
    }

    protected function _setComponentListeners() {
        if (count($this->_listeners) > 0) {
            $listenerCode = "listeners:{";
            foreach ($this->_listeners as $key => $listener) {
                $listenerCode.=$listener;
                if ($key < (count($this->_listeners) - 1))
                    $listenerCode.=',';
            }
            $listenerCode.='}';
            $this->setInitCode($listenerCode);
        }
        return $this;
    }

    /**
     * Add the given function code to the define code of the component
     * @param string $code  The code of the function
     * @return \ExtyComponent
     */
    protected function _addFunction($code) {
        $this->_functions.=$code;
        return $this;
    }

    //DOCKED ITEMS

    public function addDockedToolbar($toolbar) {
        $xtype = $toolbar->getComponentId();
        $code = <<<SENCHA
                {
                    xtype: '$xtype'
                }
SENCHA;
        $this->_dockedItems[] = $code;
//        var_dump($this);
//        die();
        return $this;
    }

    protected function _setDockedCode() {
        if (count($this->_dockedItems) > 0) {
            $this->_dockedCode = 'dockedItems:[';
            foreach ($this->_dockedItems as $key => $code) {
                if ($key != 0)
                    $this->_dockedCode.=',';
                $this->_dockedCode.=$code;
            }
            $this->_dockedCode .= ']';
            $this->setInitCode($this->_dockedCode);
        }
        return $this;
    }

    private function _getLoadingMaskCode() {
        $code = <<<SENCHA
                , showLoadingMask: function(loadText)
{
         if (Ext.isEmpty(loadText)) loadText = 'Loading... Please wait';
                   Ext.Ajax.on('beforerequest', function () {
                   Ext.get('content').mask(loadText, 'loading')
         }, Ext.get('content'));
         Ext.Ajax.on('requestcomplete', Ext.get('content').unmask, Ext.get('content'));
         Ext.Ajax.on('requestexception', Ext.get('content').unmask, Ext.get('content'));
}
SENCHA;
        return $code;
    }

    /**
     * Return the Ext.define code of the component
     * @param boolean $test True to write the output in a Exty/test/define.js file
     * @return string
     */
    public function getDefineCode($test = false) {
//add listeners to initCode
        $this->_setComponentListeners();
        $this->_setDockedCode();
        $this->_setInitForDefine();
        $this->_defineCode.=$this->_startDefine . $this->_configs . $this->_startInit;
        $this->_defineCode.=$this->_initCode . $this->_endInit . $this->_functions .$this->_getLoadingMaskCode(). $this->_endDefine;
        if ($test)
            file_put_contents('protected/extensions/Exty/test/define.js', $this->_defineCode);
        return $this->_defineCode;
    }

    public function setReadyCode($code) {
//        $this->_readyCode.=$code;
        Exty::setReadyCode($code);
        return $this;
    }

    public function getReadyCode($test = false) {
        return $this->_readyCode;
    }

    /**
     * Return the full code for the view: Ext.define and onReady function
     * @param boolean $test True to write the output in a Exty/test/view.phtml file
     * @return string
     */
    public function renderExt($test = false) {

        $this->senchaCode = ExtY::startScript();
        $this->senchaCode.=$this->_defineCode;
        $this->senchaCode.=ExtY::startReadyFunc() . Exty::getReadyCode();
        if (count($this->_addedComponent) > 0) {
            foreach ($this->_addedComponent as $component) {
                $this->senchaCode.=Exty::containerAdd($component->getComponentId());
            }
        }
        $this->senchaCode.=ExtY::containerAdd($this->_idComponent);
        $this->senchaCode.=ExtY::endReadyFunc();
        $this->senchaCode.=ExtY::endScript();
        if ($test)
            file_put_contents('protected/extensions/Exty/test/view.phtml', $this->senchaCode);
        return $this->senchaCode;
    }

    /*
     * CONFIGURAZIONI COMUNI A TUTTI I COMPONENT
     */

    /**
     * Set the given name class to the component
     * @param string $cls   The class property to add to the component
     * @return \ExtyComponent
     */
    public function setCls($cls) {
        $this->_configs.=<<<SENCHA
                
    cls:'$cls',
SENCHA;
        return $this;
    }

    /**
     * Set the given width to the component
     * @param int $int  
     * @return \ExtyComponent
     */
    public function setWidth($int) {
        $this->_width = $int;
        $this->_configs.=<<<SENCHA

   width:$int,
SENCHA;
        return $this;
    }

    /**
     * Set the given height to the component
     * @param int $int  
     * @return \ExtyComponent
     */
    public function setHeight($int) {
        $this->_height = $int;
        $this->_configs.=<<<SENCHA

   height:$int,
SENCHA;
        return $this;
    }

    /**
     * Set the given width as maxWidth of the component
     * @param int $int  
     * @return \ExtyComponent
     */
    public function seMaxtWidth($int) {
        $this->_configs.=<<<SENCHA

   maxWidth:$int,
SENCHA;
        return $this;
    }

    /**
     * Set the given height as maxHeight of the component
     * @param int $int  
     * @return \ExtyComponent
     */
    public function setMaxHeight($int) {
        $this->_configs.=<<<SENCHA

   maxHeight:$int,
SENCHA;
        return $this;
    }

    /**
     * Set the given width as minWidth of the component
     * @param int $int  
     * @return \ExtyComponent
     */
    public function setMinWidth($int) {
        $this->_configs.=<<<SENCHA

   minWidth:$int,
SENCHA;
        return $this;
    }

    /**
     * Set the given height as minHeight of the component
     * @param int $int  
     * @return \ExtyComponent
     */
    public function setMinHeight($int) {
        $this->_configs.=<<<SENCHA

   minHeight:$int,
SENCHA;
        return $this;
    }

    /**
     * Set the given padding to the component
     * @param int $int  
     * @return \ExtyComponent
     */
    public function setPadding($int) {
        $this->_configs.=<<<SENCHA

   padding:$int,
SENCHA;
        return $this;
    }

    /**
     * Set the given margin to the component
     * @param int $int  
     * @return \ExtyComponent
     */
    public function setMargin($int) {
        $this->_configs.=<<<SENCHA

   margin:$int,
SENCHA;
    }

    /**
     * Set border property to the component
     * @param int $int 0 to set no border 
     * @return \ExtyComponent
     */
    public function setBorder($int) {
        $this->_configs.=<<<SENCHA

   border:$int,
SENCHA;
        return $this;
    }

    /**
     * Set bodyPadding property to the component
     * @param int $int  
     * @return \ExtyComponent
     */
    public function setBodyPadding($int) {
        $this->_configs.=<<<SENCHA

   bodyPadding:$int,
SENCHA;
        return $this;
    }

    /**
     * Set the container where the component will be rendered
     * @param string $container
     * @return \ExtyComponent
     */
    public function setRenderTo($container) {
        $this->_configs.=<<<SENCHA

   renderTo:'$container',
SENCHA;
        return $this;
    }

    /**
     * Set the layout property of the component
     * @param string $type  Choose from Exty::LAYOUT_
     * @return \ExtyComponent
     */
    public function setLayout($type="'auto'") {
        $code = <<<SENCHA
                layout: {
                    type:$type
                },
SENCHA;
        $this->setConfig($code);
        return $this;
    }

    /**
     * Add a component 
     * @param ExtyComponent $component
     * @return \ExtyComponent
     */
    public function addComponent(ExtyComponent $component) {
        $this->_addedComponent[] = $component;
//passo true come secondo parametro per aggiungere anche il codice per il create
        $this->_addDefineCode($component, true);
    }

    /**
     * Set hasParend property
     * @param type $boolean
     * @return \ExtyComponent
     */
    public function setParent(ExtyComponent $parent) {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Return the parent component
     * @return boolean
     */
    public function getParent() {
        return $this->parent;
    }

    public function getWidth() {
        return $this->_width;
    }

    public function getHeight() {
        return $this->_height;
    }

}
