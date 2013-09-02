<?php

class ExtyTreeGrid extends ExtyGrid{
    protected $_component=Exty::COMPONENT_TREE_PANEL;
    protected $_dragDropPlugin;
    
    public function __construct($id, ExtyTreeStore &$store, $renderTo=''){
        parent::__construct($id, $store, false, $renderTo);
        parent::setConfig('rootVisible:false,');
    }
    
    public function addTreeColumn(ExtyGridColumnTree &$column){
        $this->_columns[]=$column->getSenchaCode();
        //controllo se la colonna ha un editor
        if ($column->hasEditor()){
            $editor=$column->getEditor();
            //aggiungo un row editing plugin
            $this->addEditorPlugin(false);
            //se l'editor è di tipo combobox, aggiungo il codice dello store ai define
            if($editor->getType()=='combobox'){
                $comboStore=$editor->getComboStore();
                parent::_addDefineCode($comboStore);
                $this->setReadyCode(Exty::extCreate($comboStore->getStoreId()));
                
            }
        }
        return $this;
    }
    /**
     * Enable Drag&Drop functionalities to the tree
     * @return \ExtyTreeGrid
     */
    public function enableDragDrop(){
        $this->_dragDropPlugin=<<<SENCHA
               viewConfig: {
                plugins: [
                    Ext.create('Ext.tree.plugin.TreeViewDragDrop', {

                    })
                ],
                listeners: {
                    drop: {
                        fn: me.onTreeViewDragDropDrop,
                        scope: me
                    }
                }
            }
SENCHA;
        parent::_addFunction($this->getDragDropFunction());
        return $this;
    }
    
    public function getDragDropFunction(){
        $code=<<<SENCHA
              ,onTreeViewDragDropDrop: function(node,data,overModel,dropPosition, eOpts){
                    var store=Ext.getStore('$this->_storeId');
                    var elementMoved=JSON.stringify(data.records[0].data);
                    var targetElement=JSON.stringify(overModel.data);
                    var proxy=store.getProxy();
                    var url=proxy.url;
                    Ext.Ajax.request({
                        method: 'POST',
                        url: url,
                        params:{
                            elementMoved: elementMoved,
                            targetElement: targetElement,
                            action: dropPosition
                        },
                        
                        failure: function (response,eOps){
                            Ext.MessageBox.alert('Connection error','Error');
                        },
                        success: function (response){
                            if(!response.responseText.success)
                            console.log('no');
                            else
                            console.log(response.responseText.msg);
                        }
                    });
                    store.load();
                }  
SENCHA;
        return $code;
        
    }
    
    /**
     * Set the starting code for init function. Overrides the ExtyGrid method
     * @return \ExtyGrid
     */
    protected function _setInitComponentCode(){
        $this->_setColumns();

        $this->_initComponentCode.=<<<SENCHA
                $this->_dragDropPlugin,
                $this->_columnsConfig
SENCHA;
        if($this->_dragDropPlugin)
            $this->_initComponentCode.=<<<SENCHA
                ,$this->_dragDropPlugin
SENCHA;
        return $this;
    }
}