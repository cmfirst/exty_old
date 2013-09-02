<?php
 /**
     * Create the code radio buttons, ready to be added to items
     * @param type The type of the field
     * @param type $label   The label of the field
     * @param type $name    The name value of the field
     * @param type $required    True to set the field required
     * @param array $radio  The associative array of radio button. It must be an associative array 'label'=>'value'
     */
class ExtyFieldRadioGroup extends ExtyField{
    
   /**
     * Create the code radio buttons, ready to be added to items
     * @param type The type of the field
     * @param type $label   The label of the field
     * @param type $name    The name value of the field
     * @param type $required    True to set the field required
     * @param array $radio  The associative array of radio button. It must be an associative array 'label'=>'value'
     */
    public function __construct($label,$name,$required,array $radio){
        $this->_radio=$radio;
        parent::__construct(ExtyField::FIELD_RADIOGROUP, $label, $name, $required, null);
    }
   
}