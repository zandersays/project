<?php

class FormComponentMultipleChoice extends FormComponent {
    var $multipleChoiceType = 'checkbox'; // radio, checkbox
    var $multipleChoiceClass = 'choice';
    var $multipleChoiceLabelClass = 'choiceLabel';
    var $multipleChoiceArray = array();
    var $forceLabel = false;
    var $showMultipleChoiceTipIcons = true;
    var $initialValue;

    /**
     * Constructor
     */
    function __construct($id, $label, $multipleChoiceArray, $optionArray = array()) {
        // General settings
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'formComponentMultipleChoice';
        $this->label = $label;
        $this->multipleChoiceArray = $multipleChoiceArray;

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    function hasInstanceValues() {
        if($this->multipleChoiceType == 'radio' ){
            return is_array($this->value);
        }
        else {
            if(!empty($this->value)){
                return is_array($this->value[0]);
            }
        }
        return false;
    }

     /**
     * MultipleChoice Specific Instance Handling for validation
     *
     */
     function validateComponent() {
        $this->passedValidation = true;
        $this->errorMessageArray = array();

        if(is_array($this->value[0])){
            foreach($this->value as $value){
                $this->errorMessageArray[] = $this->validate($value);
            }
        }
        else {
            $this->errorMessageArray = $this->validate($this->value);
        }
    }

    function getOptions() {
        $options = parent::getOptions();

        // Make sure you have an options array to manipulate
        if(!isset($options['options'])) {
            $options['options']  = array();
        }

        // Set the multiple choice type
        $options['options']['multipleChoiceType'] = $this->multipleChoiceType;

        return $options;
    }

    /**
     *
     * @return string
     */
    function __toString() {
        // Generate the component div
        if(sizeof($this->multipleChoiceArray) > 1 || $this->forceLabel) {
            $div = parent::generateComponentDiv();
        }
        else {
            $div = parent::generateComponentDiv(false);
        }
        
        // Case
        // array(array('value' => 'option1', 'label' => 'Option 1', 'checked' => 'checked', 'tip' => 'This is a tip'))
        $multipleChoiceCount = 0;
        foreach($this->multipleChoiceArray as $multipleChoice) {
            
            $multipleChoiceValue = isset($multipleChoice['value']) ? $multipleChoice['value'] : '';
            $multipleChoiceLabel = isset($multipleChoice['label']) ? $multipleChoice['label'] : '';
            $multipleChoiceLabel = isset($multipleChoice['label']) ? $multipleChoice['label'] : '';
            $multipleChoiceLabelClass = isset($multipleChoice['labelClass']) ? $multipleChoice['labelClass'] : '';
            $multipleChoiceLabelOnMouseOver = isset($multipleChoice['labelOnMouseOver']) ? $multipleChoice['labelOnMouseOver'] : '';
            $multipleChoiceChecked = isset($multipleChoice['checked']) ? $multipleChoice['checked'] : false;
            $multipleChoiceTip = isset($multipleChoice['tip']) ? $multipleChoice['tip'] : '';
            $multipleChoiceDisabled = isset($multipleChoice['disabled']) ? $multipleChoice['disabled'] : '';
            $multipleChoiceInputHidden = isset($multipleChoice['inputHidden']) ? $multipleChoice['inputHidden'] : '';

            // Check for initial values
            if($this->initialValue !== null) {
                if(Arr::is($this->initialValue)) {
                    if(in_array($multipleChoice['value'], $this->initialValue)) {
                        $multipleChoiceChecked = true;
                    }
                }
                else if($this->initialValue == $multipleChoice['value']) {
                    $multipleChoiceChecked = true;
                }
            }

            $multipleChoiceCount++;

            $div->append($this->getMultipleChoiceWrapper($multipleChoiceValue, $multipleChoiceLabel, $multipleChoiceLabelClass, $multipleChoiceLabelOnMouseOver, $multipleChoiceChecked, $multipleChoiceTip, $multipleChoiceDisabled, $multipleChoiceInputHidden, $multipleChoiceCount));
        }

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div, $this->id.'-div');

        return $div->__toString();
    }
    
    //function to insert tips onto the wrappers

    function getMultipleChoiceWrapper($multipleChoiceValue, $multipleChoiceLabel, $multipleChoiceLabelClass, $multipleChoiceLabelOnMouseOver, $multipleChoiceChecked, $multipleChoiceTip, $multipleChoiceDisabled, $multipleChoiceInputHidden, $multipleChoiceCount) {
        // Make a wrapper div for the input and label
        $multipleChoiceWrapperDiv = new HtmlElement('div', array(
            'id' => $this->id.'-choice'.$multipleChoiceCount.'-wrapper',
            'class' => $this->multipleChoiceClass.'Wrapper',
        ));

        // Input tag
        $input = new HtmlElement('input', array(
            'type' => $this->multipleChoiceType,
            'id' => $this->id.'-choice'.$multipleChoiceCount,
            'name' => $this->name,
            'value' => $multipleChoiceValue,
            'class' => $this->multipleChoiceClass,
            'style' => 'display: inline;',
            //'checked' => ($multipleChoiceChecked) ? 'checked' : null
        ));
        if($multipleChoiceChecked) {
            $input->attr('checked', 'checked');
        }
        if($multipleChoiceDisabled) {
            $input->attr('disabled', 'disabled');
        }
        if($multipleChoiceInputHidden) {
            $input->attr('style', 'display: none;');
        }
        $multipleChoiceWrapperDiv->append($input);

        // Multiple choice label
        $multipleChoiceLabelHtmlElement = new HtmlElement('label', array(
            'for' => $this->id.'-choice'.$multipleChoiceCount,
            'class' => $this->multipleChoiceLabelClass,
            'style' => 'display: inline;',
        ));
        if(!empty($multipleChoiceLabelClass)) {
            $multipleChoiceLabelHtmlElement->attr('class', $multipleChoiceLabelClass);
        }
        if(!empty($multipleChoiceLabelOnMouseOver)) {
            $multipleChoiceLabelHtmlElement->attr('onmouseover', $multipleChoiceLabelOnMouseOver);
        }
        // Add an image to the label if there is a tip
        if(!empty($multipleChoiceTip) && $this->showMultipleChoiceTipIcons) {
            $multipleChoiceLabelHtmlElement->html($multipleChoiceLabel.' <span class="formComponentMultipleChoiceTipIcon">&nbsp;</span>');
        }
        else {
            $multipleChoiceLabelHtmlElement->html($multipleChoiceLabel);
        }
        // Add a required star if there is only one multiple choice option and it is required
        if(sizeof($this->multipleChoiceArray) == 1) {
            // Add the required star to the label
            if(in_array('required', $this->validationOptions)) {
                $labelRequiredStarSpan = new HtmlElement('span', array(
                    'class' => $this->labelRequiredStarClass
                ));
                $labelRequiredStarSpan->html(' *');
                $multipleChoiceLabelHtmlElement->append($labelRequiredStarSpan);
            }
        }
        $multipleChoiceWrapperDiv->append($multipleChoiceLabelHtmlElement);

        // Multiple choice tip
        if(!empty($multipleChoiceTip)) {
            $multipleChoiceTipDiv = new HtmlElement('div', array(
                'id' => $this->id.'-'.$multipleChoiceValue.'-tip',
                'style' => 'display: none;',
                'class' => 'formComponentMultipleChoiceTip'
            ));
            $multipleChoiceTipDiv->html($multipleChoiceTip);
            $multipleChoiceWrapperDiv->append($multipleChoiceTipDiv);
        }

        return $multipleChoiceWrapperDiv;
    }


    // Validations
    public function required($options) {
        $errorMessageArray = array('Required.');
        return  sizeof($options['value']) > 0 ? 'success' : $errorMessageArray;
    }
    public function minOptions($options) {
        $errorMessageArray = array('You must select more than '. $options['minOptions'] .' options');
        return sizeof($options['value']) == 0 || sizeof($options['value']) > $options['minOptions'] ? 'success' : $errorMessageArray;
    }
    public function maxOptions($options) {
        $errorMessageArray = array('You may select up to '. $options['maxOptions'] .' options. You have selected '. sizeof($options['value']) . '.');
        return sizeof($options['value']) == 0 || sizeof($options['value']) <= $options['maxOptions'] ? 'success' : $errorMessageArray;
    }
}

?>
