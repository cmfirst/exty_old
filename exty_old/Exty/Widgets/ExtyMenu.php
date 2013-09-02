<?php

class ExtyMenu extends CWidget {

    public $senchaCode;
    public $baseUrl;

    public function init() {

        $this->baseUrl = (strlen(yii::app()->baseUrl) > 0 ? yii::app()->baseUrl : '/');
        $logoutUrl = $this->baseUrl . Yii::app()->params['logoutUrl'];
        $username = Yii::app()->user->name;
        $this->senchaCode = <<<SENCHA
                  <script type="text/javascript">
                    var visible;
                    function onMenuClick(module,controller,action){
                        location.href='$this->baseUrl'+module+'/'+controller+'/'+action;
                    }
                    
                    Ext.onReady(function() {
			Ext.create('Ext.toolbar.Toolbar',{
				renderTo: Ext.get('mainmenu'),
                                cls: 'exty-menu-toolbar',
                                items: [
SENCHA;
        echo $this->senchaCode;
        $this->getSenchaCode(Yii::app()->session['menuTree']);
        $lbl_logout=_t('logout');
        $this->senchaCode = <<<SENCHA
                                    
				    {
					xtype: 'tbfill'
				    },
                                    {
                                        text: '$lbl_logout ($username)',
                                        cls: 'exty-menu-button',
                                        href: '$logoutUrl',
                                        ui:'menu',
                                        width:150,
                                        hrefTarget:'_self'
                                    }
				]
                        
                        });
                  }); 
                 </script>
SENCHA;
        echo $this->senchaCode;
    }

    public function getSenchaCode($root) {
//        echo "<pre>";
//        print_r($root);

        foreach ($root as $element) {

            $text = $element['actdesc'];
            $module = $element['actmodule'];
            $controller = $element['actcontroller'];
            $action = $element['actaction'];
            if ($element['leaf'] && $element['actvisible'] || (!$element['leaf'] && @$element['children']) && $element['actvisible']) {
                echo '{';
                echo "text: '" . $element['actdesc'] . "',";
                echo "cls: 'exty-menu-button',";
                echo "width: 120,";
                echo "ui: 'menu',";
//		echo 'iconCls: "icon-menu_' . $element['icon'] . '",';
//		$this->_cssCache[] = $element['icon'];

                if (isset($element['children'])) {
                    echo 'menu: {';
                    echo "xtype: 'menu',";
                    echo 'items: [';
                    $this->getSenchaCode($element['children']);
                    echo ']';
                    echo '},';
                } else {
                    if ($element['leaf'] && !isset($element['children'])) {
//			echo "url: '$module/$controller/$action',";
                        echo "href: '" . Yii::app()->createUrl("$module/$controller/$action") . "',";
                        echo "hrefTarget: '_self',";
//		    }else{
                    }
                }
                //se ho sotto elementi, simulo il click al passaggio del mouse
                if (!$element['leaf'] && @$element['children'] && $element['actvisible']) {
                    echo "listeners: { ";
                    echo "mouseover: function (menu,item){";
                    echo "visible=true;";
                    echo "menu.showMenu();}";
                    echo "},";
                }
                echo 'hidden: false'; //For IE compatibility
                echo '},';
            }
        }
//	echo '{hidden:true}'; //For IE compatibility
    }

}
