<?php

class ExtyFilterForm extends ExtyFormpanel{
    
    /* Add a button to submit the values to filter grid panel
     * @param string $text The text to be displayed on the button 
     * @return \ExtyFilterForm
     */
    public function addBtnSubmit($text){
        parent::_addBtn(ExtyButton::BUTTON_FILTER_SUBMIT, $text);
        return $this;
    }
}