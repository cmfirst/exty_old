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
                                  form.reset();
                            },
                            success: function (form,action){
                                location.href=action.result.redirectUrl;
                            }
                        }
SENCHA;
                break;
            case Exty::LOGIN_SUBMIT_STANDARD:
                $standardSubmit = "true";
                $manageRequest = '';
                break;
        }
        $code = <<<SENCHA
                  <script type="text/javascript">
                    
                        Ext.define('LoginForm', {
                            extend: 'Ext.form.Panel',
                            bodyBorder: '10',
                            renderTo:'content',
                            padding: '0 0 10',
                            height: 200,
                            id: 'loginForm',
                            width: 400,
                            layout: {
                                type: 'auto'
                            },
                            title: 'Login',
                            cls:'login-form',
                            url:'$url',
                            standardSubmit: $standardSubmit,    
                            initComponent: function() {
                                var me = this;

                                Ext.applyIf(me, {
                                    items: [
                                        {
                                            xtype: 'displayfield',
                                            anchor: '100%',
                                            height: 5,
                                            fieldLabel: '',
                                            value: '$error',
                                            fieldStyle: 'color:red; font-weight:bold'
                                        },
                                        {
                                            xtype: 'textfield',
                                            id: 'username',
                                            name:'username',
                                            fieldLabel: 'Username',
                                            msgTarget: 'under',
                                            allowBlank: false,
                                            size: 30,
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
                                            id: 'password',
                                            name:'password',
                                            fieldLabel: 'Password',
                                            msgTarget: 'under',
                                            inputType: 'password',
                                            allowBlank: false,
                                            size: 30,
                                            listeners: {
                                                specialkey: function(field, e){
                                                    if (e.getKey() == e.ENTER) {
                                                        me.onSubmit();
                                                    }
                                                }
                                            }
                                        }
                                    ],
                                    dockedItems: [
                                        {
                                            xtype: 'toolbar',
                                            dock: 'bottom',
                                            layout: {
                                                pack: 'end',
                                                type: 'hbox'
                                            },
                                            items: [
                                                {
                                                    xtype: 'button',
                                                    formBind: true,
                                                    iconCls: 'login-icon',
                                                    text: 'Login',
                                                    listeners: {
                                                        click: {
                                                            fn: me.onSubmit,
                                                            scope: me
                                                        }
                                                    }
                                                }
                                            ]
                                        }
                                    ]
                                });

                                me.callParent(arguments);
                            },
                        onSubmit: function(){
                                var form=Ext.getCmp('loginForm');
                                form.submit($manageRequest);
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