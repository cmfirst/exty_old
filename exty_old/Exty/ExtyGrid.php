<?php

/**
 * Create the code for Grid Panel
 * 
 * @param string $id   The id of the Grid
 * @param ExtyStore $store    Ext Store
 * @param boolean $autocolumn  [optional] False to add columns manually
 */
class ExtyGrid extends ExtyComponent {

    protected $_component = Exty::COMPONENT_GRID_PANEL;
    protected $_columns = array();
    protected $_columnsConfig;
    protected $_initComponentCode;
    protected $_gridStore;
    protected $_store;
    protected $_storeId;
    protected $_editForm;
    protected $_rowEditPlugin;

    /**
     * Create the code for Grid Panel
     * 
     * @param string $id   The id of the Grid
     * @param ExtyStore $store    The store of the grid
     * @param boolean $autocolumn  [optional] true to perform autocolumn
     * @param string $renderTo [Optional] The container (div) where the component has to be rendered
     */
    public function __construct($id, ExtyStore &$store, $autocolumn = false, $renderTo = '') {
        parent::__construct($id, $this->_component, $renderTo);
        $this->_addStore($store);
        if ($autocolumn)
            $this->_autocolumn($store->getFields());
        /* $this->_startDocked=<<<SENCHA
          dockedItems: [

          SENCHA; */
        parent::setConfig('forceFit:true,');
        //add column sorting listener
        parent::_addListener($this->_getColumnSortListener());
        parent::_addFunction($this->_getColumnSortFunction());
    }

    /**
     * Set the starting code for init function
     * @return \ExtyGrid
     */
    private function _setInitComponentCode() {
        $this->_setColumns();

        $this->_initComponentCode.=<<<SENCHA
                $this->_columnsConfig
SENCHA;
        if ($this->_rowEditPlugin)
            $this->_initComponentCode.=<<<SENCHA
                ,$this->_rowEditPlugin
SENCHA;
        return $this;
    }

    /**
     * Return the Ext.define code
     * @param boolean $test [optional] True to write code to file for test 
     * @return string
     */
    public function getDefineCode($test = false) {
        $this->_setInitComponentCode();
        $this->setInitCode($this->_initComponentCode);
        $this->setReadyCode(Exty::extCreate(parent::getComponentId()));
        return parent::getDefineCode($test);
    }

    /**
     * Return the whole code for the view 
     * Ext.define of Store and Ggrid + Ext.onReday code with Ext.create
     * @param boolean $test [optional] True to write code to file for test
     * @return string
     */
    public function renderExt($test = false) {
        $this->getDefineCode();
        return parent::renderExt($test);
    }

    /*
     * GRID CONFIGURATIONS
     */

    /**
     * Set the title of the grid
     * @param string $title
     * @return \ExtyGrid
     */
    public function setTitle($title) {
        $code = <<<SENCHA
                title:'$title',
SENCHA;
        $this->setConfig($code);
        return $this;
    }

    /**
     * Set scroll for the grid
     * @param string $scroll    Accepted values: 'vertical','horizontal','both','none'
     * @return \ExtyGrid
     */
    public function setScroll($scroll) {
        //vertical, horizontal, both, none
        if ($scroll == 'vertical' || $scroll == 'horizontal' || $scroll == 'both' || $scroll == 'none')
            $code = <<<SENCHA
                scroll:'$scroll',
SENCHA;
        $this->setConfig($code);
        return $this;
    }

    /**
     * Set fullscreen grid
     * @param boolean $boolean  True to make grid rendered full screen
     * @return \ExtyGrid
     */
    public function setFit($boolean) {
        $val = boolString($boolean);
        $code = <<<SENCHA
                forceFit:$val,
SENCHA;
        $this->setConfig($code);
        return $this;
    }

    /*
     * COLUMNS GRID
     */

    /**
     * Add a column to the grid
     * @param ExtyColumnGrid $column
     * @return \ExtyGrid
     */
    public function addColumn(ExtyColumnGrid &$column) {
        $this->_columns[] = $column->getSenchaCode();
        //controllo se la colonna ha un editor
        if ($column->hasEditor()) {
            $editor = $column->getEditor();
            //aggiungo un row editing plugin
            $this->addEditorPlugin(false);
            //se l'editor è di tipo combobox, aggiungo il codice dello store ai define
            if ($editor->getType() == 'combobox') {
                $comboStore = $editor->getComboStore();
                parent::_addDefineCode($comboStore, true);
                $this->setReadyCode(Exty::extCreate($comboStore->getStoreId()));
            }
        }
        return $this;
    }

    /**
     * Perform autocolumn to grid base on store fields. Called by constructor
     * @param Array $fields
     * @return \ExtyGrid
     */
    private function _autocolumn($fields) {
        foreach ($fields as $field) {
            $columnType = '';
            $columnFormat = '';
            $title = $field['title'];
            $dataIndex = $field['name'];
            switch ($field['type']) {
                case 'date':
                    $columnType = 'datecolumn';
                    $columnFormat = 'd/m/Y';
                    break;
                case 'float':
                    $columnType = 'numbercolumn';
                    break;
                case 'int':
                    $columnType = 'numbercolumn';
                    break;
                default:
                    $columnType = 'gridcolumn';
            }
            $col = <<<SENCHA
                   {
                        xtype:'$columnType',
                        text:'$title',
                        dataIndex:'$dataIndex'
SENCHA;
            if ($columnFormat)
                $col.=",format:'$columnFormat'";
            if ($columnType == 'numbercolumn')
                $col.=",align:'right'";
            $col.='}';
            $this->_columns[] = $col;
        }
        return $this;
    }

    /**
     * Create the code for columns
     * @return \ExtyGrid
     */
    protected function _setColumns() {
        $this->_columnsConfig = "columns:[";
        foreach ($this->_columns as $key => $value) {
            if ($key != 0)
                $this->_columnsConfig.=',';
            $this->_columnsConfig.=$value;
        }
        $this->_columnsConfig.=']';
        return $this;
    }

    /**
     * Add a cell or row editing plugin to grid
     * Call
     * @param boolean $cell [optional] False to add a RowEditingPlugin. Default to true (CellEditingPlugin)
     * @return \ExtyGrid
     */
    public function addEditorPlugin($cell = true) {
        //se $this->_plugin non è ancora stato valorizzato=>se non è già stato chiamato
        if (!$this->_rowEditPlugin) {
            if ($cell)
                $type = 'CellEditing';
            else
                $type = 'RowEditing';
            $this->_rowEditPlugin = <<<SENCHA
                    plugins: [
                        Ext.create('Ext.grid.plugin.$type', {
                            saveBtnText: 'Aggiorna',
                            cancelBtnText: 'Annulla'
                        })
                    ]
SENCHA;
        }
        return $this;
    }

    /*
     * GRID STORE
     */

    /**
     * Add the store to the grid. Called by constructor
     * @param ExtyStore $store
     * @return \ExtyGrid
     */
    private function _addStore(ExtyStore &$store) {
        $this->_store = $store;
        parent::_addDefineCode($store, true);
        $this->_storeId = $store->getStoreId();
        parent::setConfig("store: '$this->_storeId',");
//        $this->_gridStore="store: '$this->_storeId'";
        $this->setReadyCode(Exty::extCreate($this->_storeId));
        return $this;
    }

    /**
     * Add a paginator to the grid
     * @param   string  $dockedPosition [Optional] Default to top. Choose from Exty::DOCKED_POSITION_
     * @return \ExtyGrid
     */
    public function addPaginator($dockedPosition = Exty::DOCKED_POSITION_TOP) {
        $paginator = new ExtyPaginator('pagingToolbar', $this->_store, $dockedPosition);
        parent::addDockedToolbar($paginator);
        parent::_addDefineCode($paginator, true);
        return $this;
    }

    /**
     * Add a toolbar component to the grid
     * @param ExtyToolbar $toolbar
     * @return \ExtyGrid
     */
    public function addToolbar(ExtyToolbar $toolbar) {
        parent::addDockedToolbar($toolbar);
        parent::_addDefineCode($toolbar, true);
        return $this;
    }

    /**
     * Add a filter panel before the grid
     * @param ExtyForm $form    The form with filter fields. 
     * The Form must have a button BtnFilterSubmit. 
     * The button will load the store of the grid, passing the encoded values as parameter in 'filter' param
     * @return \ExtyGrid
     */
    public function addFilterForm(ExtyFilterForm $form) {
        parent::addComponent($form);
        return $this;
    }

    public function addEditFormOnDblClick(ExtyForm $form) {
        $this->_editForm = $form;
        $id = $this->_idComponent;
        $listener = <<<SENCHA
                celldblclick: {
                    fn: me.onCellDblClick$id,
                    scope: me
                }
SENCHA;
        parent::_addListener($listener);
        $function = $this->_getOpenEditFormFunction();
        parent::_addFunction($function);
        return $this;
    }

    /*
     * COLUMN SORTING LISTENER
     */

    /**
     * Returns the code of the function for column sorting 
     * @return string
     */
    protected function _getColumnSortListener() {
        $code = <<<SENCHA
                    sortchange: {
                        fn: me.onSortChange,
                        scope: me
                    }
SENCHA;
        return $code;
    }

    protected function _getColumnSortFunction() {
        $code = <<<SENCHA
                ,onSortChange: function(ct, column, direction, eOpts) {
                    var store=Ext.getStore('$this->_storeId');
                    var params={};
                    params.property=column.dataIndex;
                    params.direction=direction;
                    var json='['+JSON.stringify(params)+']';
                    store.load({
                        params:{
                            sort:json

                        }
                    });
                }
SENCHA;
        return $code;
    }

    private function _getOpenEditFormFunction() {
        $winComponent = Exty::COMPONENT_WINDOW;
        $form = $this->_editForm;
        $formId = $form->getComponentId();
        $formWidth = $form->getWidth() ? $form->getWidth() : 350;
        $formHeight = $form->getHeight() ? $form->getHeight() : 400;
        $formTitle = $form->getTitle() == '' ? 'Modifica' : $form->getTitle();
        $this->_editForm->setTitle('');
        $function = <<<SENCHA
                ,onCellDblClick$this->_idComponent: function(tableview, td, cellIndex, record, tr, rowIndex){
                    var $formId=Ext.create('$formId');
                    var win$formId=Ext.create('Ext.$winComponent',{
                    width: $formWidth,
                    height:$formHeight,
                    title: '$formTitle',
                    id: 'win$formId',
                    autoShow:true
                    });
                    win$formId.add($formId);
                    $formId.loadRecord(record);
                }
SENCHA;
        return $function;
    }

}