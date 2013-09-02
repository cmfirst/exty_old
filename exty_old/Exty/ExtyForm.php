<?php

class ExtyForm extends ExtyFormpanel {

    /**
     * Add a button to perform form submit 
     * @param string $text  The text to be displayed on the button
     * @param string $url   [Optional] The url for POST submit. Default value: 'actual_actionRequest' es:editRequest
     * @return \ExtyForm
     */
    public function addBtnSubmit($text, $action='' ,$url = '') {
        if (!$url) {
            $action = Yii::app()->controller->action->id;
            $url = $action . Exty::AJAX_ACTION_SUFFIX;
        }
        
        if(!$action) $action='save';
        $this->_url = $url;
        parent::setConfig("url: '$url',");

        parent::_addBtn(ExtyButton::BUTTON_FORM_SUBMIT, $text, $action);
        return $this;
    }

}
