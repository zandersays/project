<?php

class FormComponentTextArea extends FormComponent {
    /*
     * Constructor
     */
    function __construct($id, $label, $optionArray = array()) {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->label = $label;
        $this->class = 'formComponentTextArea';
        $this->inputClass = 'textArea';
        $this->widthArray = array('shortest' => '5em', 'short' => '10em', 'medium' => '20em', 'long' => '30em', 'longest' => '40em');
        $this->heightArray = array('short' => '6em', 'medium' => '12em', 'tall' => '18em');

        // Input options
        $this->initialValue = '';
        $this->disabled = false;
        $this->readOnly = false;
        $this->wrap = ''; // hard, off
        $this->width = '';
        $this->height = '';
        $this->style = '';
        $this->maxLength = null;
        $this->allowTabbing = false;
        $this->emptyValue = '';
        $this->autoGrow = false;
        $this->onKeyUp = '';
        $this->onKeyDown = '';
        $this->onScroll = '';

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    function hasInstanceValues() {
        return is_array($this->value);
    }

    function getOptions() {
        $options = parent::getOptions();

        // Tabbing
        if($this->allowTabbing) {
            $options['options']['allowTabbing'] = true;
        }

        // Empty value
        if(!empty($this->emptyValue)) {
            $options['options']['emptyValue'] = $this->emptyValue;
        }

        // Auto grow
        if($this->autoGrow) {
            $options['options']['autoGrow'] = $this->autoGrow;
        }

        return $options;
    }

    /**
     *
     * @return string
     */
    function __toString() {
        // Generate the component div
        $div = $this->generateComponentDiv();

        // Add the input tag
        $textArea = new HtmlElement('textarea', array(
            'id' => $this->id,
            'name' => $this->name,
            'class' => $this->inputClass,
        ));
        if(!empty($this->width)) {
            if(array_key_exists($this->width, $this->widthArray)) {
                $textArea->attr('style', 'width: '.$this->widthArray[$this->width].';');
            }
            else {
                $textArea->attr('style', 'width: '.$this->width.';');
            }
        }
        if(!empty($this->height)) {
            if(array_key_exists($this->height, $this->heightArray)) {
                $textArea->attr('style', 'height: '.$this->heightArray[$this->height].';', true);
            }
            else {
                $textArea->attr('style', 'height: '.$this->height.';', true);
            }
        }
        if(!empty($this->style)) {
            $textArea->attr('style', $this->style, true);
        }
        if($this->disabled) {
            $textArea->attr('disabled', 'disabled');
        }
        if($this->readOnly) {
            $textArea->attr('readonly', 'readonly');
        }
        if($this->wrap) {
            $textArea->attr('wrap', $this->wrap);
        }
        if(!empty($this->initialValue)) {
            $textArea->html($this->initialValue);
        }
        if(!empty($this->onKeyUp)) {
            $textArea->attr('onkeyup', $this->onKeyUp);
        }
        if(!empty($this->onKeyDown)) {
            $textArea->attr('onkeydown', $this->onKeyDown);
        }
        if(!empty($this->onScroll)) {
            $textArea->attr('onscroll', $this->onScroll);
        }
        if(isset($this->maxLength)) {
            $textArea->attr('maxlength', $this->maxLength);
        }
        else if(isset($this->validationOptions['maxLength'])) {
            $textArea->attr('maxlength', $this->validationOptions['maxLength']);
        }
        
        $div->append($textArea);

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div);

        return $div->__toString();
    }
    
    public function maxLength($options) {
        $messageArray = array('Must be less than ' . $options['maxLength'] . ' characters long. Current value is '.strlen($options['value']).' characters.');
        return strlen($options['value']) <= $options['maxLength'] || $options['value'] == '' ? 'success' : $messageArray;
    }
}

?>
