<?php

class FormComponentName extends FormComponent {
    var $middleInitialHidden = false;
    var $emptyValues = null;
    var $showSublabels = true;
    var $initialValue = array('firstName' => '', 'middleInitial' => '', 'lastName' => '');

    /*
     * Constructor
     */
    function __construct($id, $label, $optionArray = array()) {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->label = $label;
        $this->class = 'formComponentName';

        if($this->emptyValues === true) {
            $this->emptyValues = array('firstName' => 'First Name', 'middleInitial' => 'M' ,'lastName' => 'Last Name');
        }
        //$this->mask = '';

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    function hasInstanceValues() {
        return is_array($this->value);
    }

    function getOptions() {
        $options = parent::getOptions();

        if(!empty($this->emptyValues)) {
            $options['options']['emptyValue'] = $this->emptyValues;
        }

        if(empty($options['options'])) {
            unset($options['options']);
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

        $firstNameDiv = new HtmlElement('div', array(
            'class' => 'firstNameDiv',
        ));
        // Add the first name input tag
        $firstName = new HtmlElement('input', array(
            'type' => 'text',
            'id' => $this->id.'-firstName',
            'name' => $this->name.'-firstName',
            'class' => 'firstName singleLineText',
            'value' => $this->initialValue['firstName'],
        ));
        $firstNameDiv->append($firstName);

        // Add the middle initial input tag
        $middleInitialDiv = new HtmlElement('div', array(
            'class' => 'middleInitialDiv',
        ));
        $middleInitial = new HtmlElement('input', array(
            'type' => 'text',
            'id' => $this->id.'-middleInitial',
            'name' => $this->name.'-middleInitial',
            'class' => 'middleInitial singleLineText',
            'maxlength' => '1',
            'value' => $this->initialValue['middleInitial'],
        ));
        if($this->middleInitialHidden) {
            $middleInitial->attr('style', 'display: none;');
            $middleInitialDiv->attr('style', 'display: none;');
        }
        $middleInitialDiv->append($middleInitial);
        

        // Add the last name input tag
        $lastNameDiv = new HtmlElement('div', array(
            'class' => 'lastNameDiv',
        ));
        $lastName = new HtmlElement('input', array(
            'type' => 'text',
            'id' => $this->id.'-lastName',
            'name' => $this->name.'-lastName',
            'class' => 'lastName singleLineText',
            'value' => $this->initialValue['lastName'],
        ));
        $lastNameDiv->append($lastName);

        if(!empty($this->emptyValues)){
            $this->emptyValues = array('firstName' => 'First Name', 'middleInitial' => 'M' ,'lastName' => 'Last Name');
            foreach($this->emptyValues as $key => $value) {
            if($key == 'firstName') {
                $firstName->setAttribute('value', $value);
                $firstName->addClassName('defaultValue');
            }
            if($key == 'middleInitial') {
                $middleInitial->setAttribute('value', $value);
                $middleInitial->addClassName('defaultValue');
            }
            if($key == 'lastName') {
                $lastName->setAttribute('value', $value);
                $lastName->addClassName('defaultValue');
            }
        }
            
        }

        if($this->showSublabels) {
            $firstNameDiv->append('<div class="formComponentSublabel"><p>First Name</p></div>');
            $middleInitialDiv->append('<div class="formComponentSublabel"><p>MI</p></div>');
            $lastNameDiv->append('<div class="formComponentSublabel"><p>Last Name</p></div>');
        }
        
        $div->append($firstNameDiv);
        $div->append($middleInitialDiv);
        $div->append($lastNameDiv);

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div);

        return $div->__toString();
    }

    public function required($options) {
        $errorMessageArray = array();
        if($options['value']->firstName == '') {
            array_push($errorMessageArray, array('First name is required.'));
        }
        if($options['value']->lastName == '') {
            array_push($errorMessageArray, array('Last name is required.'));
        }
        return sizeof($errorMessageArray) == 0 ? 'success' : $errorMessageArray;
    }
}

?>
