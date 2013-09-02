<?php

/**
 * Create the code for the button, ready to be added to items
 * @param string $type The type of the button. Choose from costant ExtyButton_BUTTON_
 * @param string $text The text to be displayed on the button
 */
class ExtyButton {

    protected $_type;
    protected $_funcName;
    protected $_iconCls;
    protected $_code;
    protected $_function;
    protected $_form;
    protected $_formWidth = 400; //default width
    protected $_formHeight = 400;    //default height
    protected $_formTitle;
    protected $_formId;
    protected $_custFunc;
    private $_xtype = Exty::XTYPE_BUTTON;

    const BUTTON_ADD = 'add';
    const BUTTON_EDIT = 'edit';
    const BUTTON_DELETE = 'delete';
    const BUTTON_EXPORT_XLS = 'exportXls';
    const BUTTON_EXPORT_PDF = 'exportPdf';
    const BUTTON_SAVE = 'save';
    const BUTTON_FORM_SUBMIT = 'submit';
    const BUTTON_FILTER_SUBMIT = 'filter';
    const BUTTON_CUSTOM = 'custom';

    /**
     * Create the code for the button, ready to be added to items
     * @param string $type The type of the button. Choose from costant ExtyButton_BUTTON_
     * @param string $text The text to be displayed on the button
     * @return \ExtyButton
     */
    public function __construct($type, $text, ExtyForm $form = null, $custFunc = '') {
        $this->_type = $type;
        $this->_funcName = str_replace(' ', '', $text) . 'Click';
        $iconCls = "icon-$type";
        if ($custFunc != '') {
            $iconCls = "icon-$custFunc";
            $this->_custFunc = $custFunc;
            $this->_funcName = str_replace(' ', '', $custFunc) . 'Click';
        }
        if ($form) {
            $this->_formId = $form->getComponentId();
            $this->_formWidth = $form->getWidth() ? $form->getWidth() : 350;
            $this->_formHeight = $form->getHeight() ? $form->getHeight() : 400;
            $this->_formTitle = $form->getTitle() == '' ? '' : $form->getTitle();
            $form->setTitle('');
            $this->_form = $form;
        }

        $this->_code = <<<SENCHA
                {
                    xtype: '$this->_xtype',
                    text: '$text',
                    iconCls: '$iconCls',
                    handler: function(button, e, eOpts){
                        me.on$this->_funcName(button, e, eOpts);
                    }
SENCHA;
        $options = '';



        $this->_code.=$options . '}';
        return $this;
    }

    /**
     * Return the button code
     * @return string
     */
    public function getCode() {
        return $this->_code;
    }

    /**
     * Return the standard function called on click button, based on the type of the button
     * @return type
     */
    //TO DO: finire di implementare tutte le funzioni per i diversi bottoni
    public function getBtnFunction() {
        if ($this->_form) {
            $function = '';
            $comboStores = $this->_form->getComboStores();
            //in caso di bottone 'edit' recupero i dati della riga per popolare la form
            if ($this->_type == self::BUTTON_EDIT) {
                $function.=<<<SENCHA
                        var grid=button.up('grid');
                        var store=grid.getStore();
                        var selected=grid.getSelectionModel().getSelection();
                        if(selected.length ==0)
                            Ext.MessageBox.alert('Attenzione', 'Selezionare il record da modificare');
                        else{
                            var record=store.getAt(selected[0].index);
                            if(record){
SENCHA;
            }
            $winComponent = Exty::COMPONENT_WINDOW;
            if ($comboStores) {
                foreach ($comboStores as $store) {
                    $storeId = $store->getStoreId();
                    $function.="var $storeId=Ext.create('$storeId');";
                }
            }
            $function .= <<<SENCHA
                 var $this->_formId=Ext.create('$this->_formId');
                 var win$this->_formId=Ext.create('Ext.$winComponent',{
//                    width: $this->_formWidth,
//                    height:$this->_formHeight,
                    title: '$this->_formTitle',
                    id: 'win$this->_formId',
                    layout: {type: 'fit'},
//                    y: 100, x:100,
                    autoShow:true
                    });
                 win$this->_formId.add($this->_formId);
SENCHA;
            if ($this->_type == self::BUTTON_EDIT) {
                $function .=<<<SENCHA
                            $this->_formId.loadRecord(record);
                        }
                        }
SENCHA;
            }
        } else {
            switch ($this->_type) {
                case self::BUTTON_ADD:
                    $module = Yii::app()->controller->module->id;
                    $controller = Yii::app()->controller->id;
                    $url = Yii::app()->createUrl($module . '/' . $controller . '/edit');
                    $function = 'location.href="' . $url . '"';
                    break;
                case self::BUTTON_DELETE:
                    $function = <<<SENCHA
                        var grid=button.up('grid');
                        var store=grid.getStore();
                        var selected=grid.getSelectionModel().getSelection();
                        if(selected.length ==0)
                            Ext.MessageBox.alert('Attenzione', 'Selezionare il record da cancellare');
                        else{
                            Ext.MessageBox.confirm('Attenzione', 'Cancellare il record selezionato?', function(option){
                                    if(option=='yes'){
                                    var record=selected[0].data;
                                    if(record){
                                        var record=Ext.JSON.encode(record);
                                        var url=store.getProxy().url;
                                        Ext.Ajax.request({
                                            url: url,
                                            params: {
                                                delete: record
                                            },
                                            success: function(response){
                                                var text = response.responseText;
                                                text=Ext.JSON.decode(text);
                                                if(text.success===true){
                                                    if(!text.hasOwnProperty('title'))
                                                        text.title='Ok';
                                                    if(!text.hasOwnProperty('text'))
                                                        text.text='Record cancellato';
                                                    Ext.MessageBox.alert(text.title, text.text, function(){
                                                        store.load();
                                                    });
                                                }else
                                                    Ext.MessageBox.alert('Errore', 'Non è stato possibile cancellare il record');
                                            },
                                            failure: function (response){
                                                    Ext.MessageBox.alert('Errore', 'Non è stato possibile cancellare il record');
                                                
                                            }
                                        });

                                        }
                                    }
                                 });
                        }
SENCHA;
                    break;
                case self::BUTTON_EXPORT_XLS:
                case self::BUTTON_EXPORT_PDF:
                    $type = substr(strtolower($this->_type), -3);
                    $function = <<<SENCHA
                            var extraParams='';
                            var container=Ext.getCmp('mainContainer');
                            var filterpanel=container.child('form');
                            if(filterpanel){
                                var filter=false;
                                var filterValues=filterpanel.getValues();
                                for(value in filterValues){
                                    if(filterValues[value]!='')
                                       filter=true;
                                }
                                if(filter){
                                    filterValues=Ext.JSON.encode(filterValues);
                                    extraParams+='/filter/'+filterValues;
                                }
                            }else{
                                var toolbar=button.up('toolbar');
                                var searchfield=toolbar.child('textfield');
                                if(searchfield){
                                    if(searchfield.name=='search' || searchfield.value!=''){
                                        extraParams='/search/'+searchfield.value;
                                    }
                                }
                            }
                            var storeId=button.up('grid').getStore().storeId;
                            storeId=storeId.slice(0, -5)+'request';
                           window.location.href='export/actionrequest/'+storeId+'/export/true/type/$type'+extraParams;
SENCHA;
                    break;
                case self::BUTTON_SAVE:
                    $function = "console.log('save da implementare')";
                    break;
                case self::BUTTON_FORM_SUBMIT:
                    $function = <<<SENCHA
                        this.showLoadingMask('esecuzione accesso');
                        var form=button.up("form");
                        var winId='win'+form.getId();
                        form.submit({
                            success: function (form, action){
                                if(!action.result.hasOwnProperty('title'))
                                    action.result.title='Ok';
                                if(!action.result.hasOwnProperty('text'))
                                    action.result.text='Record aggiornato';
                                Ext.MessageBox.alert(action.result.title, action.result.text, function(){
                                    if(action.result.success==true){
                                        form.reset();
                                        if(Ext.getCmp(winId)){
                                            var win=Ext.getCmp(winId);
                                            win.close();
                                            win.destroy();
                                            form.destroy();
                                        }else if(action.result.redirectUrl)
                                                location.href=action.result.redirectUrl;
                                        else
                                                window.history.back(-1);
                                        }
                                });
                            },
                            failure: function (form, action){
                                if(!action.result.hasOwnProperty('title'))
                                    action.result.title='Errore';
                                if(!action.result.hasOwnProperty('text'))
                                    action.result.text='Record non aggiornato';
                                Ext.MessageBox.alert(action.result.title, action.result.text);
                            }
                        });
SENCHA;
                    break;
                case self::BUTTON_FILTER_SUBMIT:
                    $function = <<<SENCHA
                    var form=button.up("form");

                    var values=form.getValues();
                    var jsonParam=Ext.JSON.encode(values);

                    var container=form.up('container');
                    var grid=container.down('grid');
                    var store=grid.store;
                    store.load({
                        params:{filter: jsonParam}

                    });
SENCHA;
                    break;
                case self::BUTTON_CUSTOM:
                    $function = <<<SENCHA
                        $this->_custFunc(button, e);
SENCHA;
                    break;
            }
        }
        $this->_function = <<<SENCHA
                ,on$this->_funcName:function(button, e){
                    $function
            }
SENCHA;
        return $this->_function;
    }

}