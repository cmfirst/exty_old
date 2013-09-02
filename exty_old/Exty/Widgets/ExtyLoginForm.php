<?php

class ExtyLoginForm extends CWidget {

    public $senchaCode;
    public $mode;
    public $error;

    public function init() {

        $module = Yii::app()->controller->module->id;
        $controller = Yii::app()->controller->id;
        $route = $module . '/' . $controller . '/login';
//        $url=Yii::app()->createUrl($route);
        $url = Yii::app()->createUrl('admin/login/login');
        $redirectUrl = Yii::app()->user->returnUrl;
        //la action che gestisce il login deve chiamarsi actionLogin
        //non importa se è nel siteController o se è in un modulo
//        $url = 'login';
        $mode = isset($this->mode) ? $this->mode : Exty::LOGIN_SUBMIT_AJAX;
        $error = isset($this->error) ? $this->error : '';
        $this->senchaCode = $this->_getSenchaCode($mode, $url, $error);
    }

    public function run() {
        echo $this->senchaCode;
        return;
    }

    private function _getSenchaCode($mode, $url, $error) {
        switch ($mode) {
            case Exty::LOGIN_SUBMIT_AJAX:
                $standardSubmit = "false";
                $manageRequest = <<<SENCHA
                        {
                            failure: function (form, action){
                                  Ext.MessageBox.alert('Attenzione',action.result.message);
//                                  form.reset();
                            },
                            success: function (form,action){
                                location.href=action.result.redirectUrl;
                            },
                            params: {
                                'password': md5(Ext.getCmp('password').getValue())
                            },
                        }
SENCHA;
                break;
            case Exty::LOGIN_SUBMIT_STANDARD:
                $standardSubmit = "true";
                $manageRequest = '';
                break;
        }
        $lbl_login = _t('login');
        $lbl_title_fieldset = _t('login information');
        $lbl_user=_t('user');
        $lbl_pass=_t('password');
        $code = <<<SENCHA
                  <script type="text/javascript">
   
                        Ext.define('LoginForm', {
                            extend: 'Ext.form.Panel',
                            bodyBorder: '10',
                            bodyPadding: '10 10',
                            renderTo:'content',
                            id: 'loginForm',
                            width: 400,
                            layout: {
                                type: 'auto'
                            },
                            title:'$lbl_login',
                            cls:'login-form',
                            url:'$url',
                            standardSubmit: $standardSubmit,    
                            initComponent: function() {
                                var me = this;
                                       Ext.applyIf(me, {
            items: [
                {
                    xtype: 'fieldset',
                    padding: '15',
                    layout: {
                        type: 'auto'
                    },
                    title: '$lbl_title_fieldset',
                    items: [
                        {
                            xtype: 'textfield',
                            padding: '0 0 5',
                            width: 306,
                            fieldLabel: '$lbl_user',
                            name: 'username',
                            allowBlank: false,
                            allowOnlyWhitespace: false,
                            listeners: {
                                specialkey: function(field, e){
                                    if (e.getKey() == e.ENTER) {
                                        me.onSubmit();
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'textfield',
                            width: 306,
                            fieldLabel: '$lbl_pass',
                            id: 'password',
                            name: 'password',
                            inputType: 'password',
                            allowBlank: false,
                            allowOnlyWhitespace: false,
                            submitValue: false,
                            listeners: {
                                specialkey: function(field, e){
                                    if (e.getKey() == e.ENTER) {
                                        me.onSubmit();
                                    }
                                }
                            }                
                        }
                    ]
                },
                {
                    xtype: 'button',
                    padding: 1,
                    scale: 'large',
                    textAlign: 'right',
                    formBind: true,
                    text: '$lbl_login',
                    iconCls: 'login-icon',
                    listeners: {
                        click: {
                            fn: me.onSubmit,
                            scope: me
                        }
                    }
                }
            ]
        });


                                me.callParent(arguments);
                            },
 
                            
                
                        onSubmit: function(){
                            this.showLoadingMask('esecuzione accesso');
                            this.getForm().submit($manageRequest);
                        },
showLoadingMask: function(loadText)
{
         if (Ext.isEmpty(loadText)) loadText = 'Loading... Please wait';
                   Ext.Ajax.on('beforerequest', function () {
                   Ext.get('content').mask(loadText, 'loading')
         }, Ext.get('content'));
         Ext.Ajax.on('requestcomplete', Ext.get('content').unmask, Ext.get('content'));
         Ext.Ajax.on('requestexception', Ext.get('content').unmask, Ext.get('content'));
}
                
                        });
                    Ext.onReady(function() {             
			Ext.create('LoginForm');
                  }); 
                 </script>
                                
SENCHA;
        return $code;
    }

}