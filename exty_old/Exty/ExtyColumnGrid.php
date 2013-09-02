<?php

abstract class ExtyColumnGrid {

    protected $_type;
    protected $_text;
    protected $_dataIndex;
    protected $_code;
    protected $_editor;
    protected $_editorCode;
    protected $_startRenderer;
    protected $_rendererFunction;

    const EDITOR_COMBO = 'combobox';
    const EDITOR_TEXT = 'textfield';
    const EDITOR_DATE = 'datefield';
    const EDITOR_NUMBER = 'numberfield';
    const COLUMN_BASE = 'gridcolumn';
    const COLUMN_DATE = 'datecolumn';
    const COLUMN_NUMBER = 'numbercolumn';
    const COLUMN_TREE = 'treecolumn';

    public function __construct($type, $text, $dataIndex) {
        $this->_type = $type;
        $this->_text = $text;
        $this->_dataIndex = $dataIndex;
        $this->_startRenderer = <<<SENCHA
                ,renderer: function(value, metaData, record, rowIndex, colIndex, store, view){
SENCHA;
        $this->_code = <<<SENCHA
                {
                    xtype: '$this->_type',
                    text: '$this->_text',
                    dataIndex: '$this->_dataIndex'
SENCHA;
    }

    //metodo che chiamano le sottoclassi per settare ulteriori configurazioni
    //deve ricevere 'attributo:valore'
    protected function configureColumn($attribute) {
        $this->_code.=",$attribute";
        return $this;
    }

    public function getSenchaCode() {
        //se la rendererFunction è valorizzata, aggiunge il codice del renderer
        if ($this->_rendererFunction)
            $this->_code.=$this->_startRenderer . $this->_rendererFunction . '}';
        if ($this->_editorCode)
            $this->_code.=$this->_editorCode;
        $this->_code.='}';
        return $this->_code;
    }

    /*
     * CONFIGURAZIONI COMUNI
     */

    public function setAlign($align) {
        if ($align == 'right' || $align == 'left' || $align == 'center') {
            $this->_code.=<<<SENCHA
                ,align: '$align'
SENCHA;
        }
        return $this;
    }

    public function setWidth($width) {
        $this->_code.=<<<SENCHA
                ,width: $width
SENCHA;
        return $this;
    }

    public function setFlex($flex) {
        $this->_code.=<<<SENCHA
                ,flex: $flex
SENCHA;
        return $this;
    }

    /*
     * RENDERER
     * setRenderer setta il codice da eseguire nella renderer function
     */

    public function setRenderer($code) {
        $this->_rendererFunction = $code;
        return $this;
    }

    /**
     * Add a textefield editor to the column
     * @param boolean [Optional] True to make the field required
     * @return \ExtyColumnGrid
     */
    public function addEdtText($required = false) {
        $this->_addEditor(self::EDITOR_TEXT, $required);
        return $this;
    }

    /**
     * Add a numberfield editor to the column
     * @param boolean [Optional] True to make the field required
     * @return \ExtyColumnGrid
     */
    public function addEdtNumber($required = false) {
        $this->_addEditor(self::EDITOR_NUMBER, $required);
        return $this;
    }

    /**
     * Add a datefield editor to the column
     * @param boolean [Optional] True to make the field required
     * @return \ExtyColumnGrid
     */
    public function addEdtDate($required = false) {
        $this->_addEditor(self::EDITOR_DATE, $required);
        return $this;
    }

    /**
     * Add a combobox editor to the column
     * @param ExtyStore $comboStore The store for the combo. 
     * The store will have 2 default fields: 'text' (displayed field) and 'value' (value field)
     * @param boolean [Optional] True to make the field required
     * @return \ExtyColumnGrid
     */
    public function addEdtCombo(ExtyStore $comboStore, $required = false) {
        $this->_addEditor(self::EDITOR_COMBO, $required, $comboStore);
        return $this;
    }

    /**
     * Return true if the column has an editor
     * @return boolean
     */
    public function hasEditor() {
        if ($this->_editor)
            return true;
        else
            return false;
    }

    /**
     * Return the Exty_GridColumnEditor od the column
     * @return /ExtyGridColumnEditor
     */
    public function getEditor() {
        return $this->_editor;
    }

    /**
     * Set the renderer function to show column data as link
     * Exty sets the url based on the given action, controller, module and params(field)
     * For every element in $params array, Exty add a GET param with the value of the current row
     * @param string $action    
     * @param string $controller    [Optional] Default to current controller
     * @param string $module    [Optional] Default to current module
     * @param array $paramsStore     [Optional] List of params (fields of the store)
     * @param array $extraParams    [Optional] Associative array paramName=>value  
                                
     * @return \ExtyColumnGrid
     */
    public function setLink($action, $controller = '', $module = '', array $paramsStore = null, $extraParams=null) {
        if ($controller == '')
            $controller = Yii::app()->controller->id;
        if ($module == '')
            $module = Yii::app()->controller->module->id;
        $href = Yii::app()->createUrl("$module/$controller/$action");
            $rendererCode="var paramString='';";
        if ($paramsStore) {
            foreach ($paramsStore as $param) {
                $rendererCode.=<<<SENCHA
                    var $param= record.data.$param;
                    if($param!=undefined)
                        paramString+="/$param/"+$param;
SENCHA;
            }
        }
        if($extraParams){
            foreach ($extraParams as $param=>$value) {
                $rendererCode.=<<<SENCHA
                    paramString+="/$param/$value";
SENCHA;
            }
        }
        $rendererCode.= "return '<a href=\"$href'+paramString+'\">'+value+'</a>';";
        $this->setRenderer($rendererCode);
        return $this;
    }
    
    /**
     * Set the renderer function to show column data as link to the given url
     * @param string $url   
     * @param boolean $newPage  [Optional] Default to true. If true open the link in a new page
     * @return \ExtyColumnGrid
     */
    public function setLinkByUrl($url, $newPage=true) {
        if($newPage)
            $target=' target="blank"';
        $rendererCode= "return '<a href=\"$url\"$target>'+value+'</a>';";
        $this->setRenderer($rendererCode);
        return $this;
    }

    /**
     * Add an aditor to the grid column
     * @param string $type   The type of the editor. Choose from ExtyColumnGrid::EDITOR_
     * @param boolean $required  True to set the field required
     * @param ExtyStore [optional]$comboId The id of the store to create in case of combobox editor
     * @return type
     */
    private function _addEditor($type, $required = false, ExtyStore $comboStore = null) {
        $this->_editor = new ExtyGridColumnEditor($type, $required, $comboStore);
        $this->_editorCode = $this->_editor->getCode();
        return;
    }

}