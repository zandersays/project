<?php

class FormComponentAddress extends FormComponent {
    var $selectedCountry = null;
    var $selectedState = null;
    var $stateDropDown = false;
    var $emptyValues = null;
    var $showSublabels = true;
    var $unitedStatesOnly = false;
    var $addressLine2Hidden = false;
    var $continentalOnly = false;
    // default input options
    var $inputOptions = array();
    var $initialValue = array();
    var $filter = array();

    /*
     * Constructor
     */
    function __construct($id, $label, $optionArray = array()) {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->label = $label;
        $this->class = 'formComponentAddress';
        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);

        // Set the empty values with a boolean
        if($this->emptyValues === true) {
            $this->emptyValues = array('addressLine1' => 'Street Address', 'addressLine2' => 'Address Line 2', 'city' => 'City', 'state' => 'State / Province / Region', 'zip' => 'Postal / Zip Code');
        }

        // United States only switch
        if($this->unitedStatesOnly) {
            $this->stateDropDown = true;
            $this->selectedCountry = 'US';
        }
    }

    function getOption($optionValue, $optionLabel, $optionSelected, $optionDisabled) {
        $option = new HtmlElement('option', array('value' => $optionValue));
        $option->html($optionLabel);

        if($optionSelected) {
            $option->attr('selected', 'selected');
        }

        if($optionDisabled) {
            $option->attr('disabled', 'disabled');
        }

        return $option;
    }

    function getOptions() {
        $options = parent::getOptions();

        if(!empty($this->emptyValues)) {
            $options['options']['emptyValue'] = $this->emptyValues;
        }

        if($this->stateDropDown){
            $options['options']['stateDropDown'] = true;
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
        $componentDiv = $this->generateComponentDiv();

        $defaultInputOptions = array(
            'addressLine1' => array('maxLength' => ''),
            'addressLine2' => array('maxLength' => ''),
            'city' => array(),
            'state' => array(),
            'county' => array(),
        );
        
        
        // some defaults that allow for input
        $this->inputOptions = array_merge($defaultInputOptions, $this->inputOptions);
        
        $defaultValues = array(
            'addressLine1' => '', 
            'addressLine2' => '', 
            'city' => '', 
            'state' => '', 
            'zip' => '', 
            'country' => '');
        
        if(Arr::is($this->initialValue)) {
            $this->initialValue = array_merge($defaultValues, $this->initialValue);
        } else {
            $this->initialValue = $defaultValues;
        }

        // Add the Address Line 1 input tag
        $addressLine1Div = new HtmlElement('div', array(
            'class' => 'addressLine1Div',
        ));
        $addressLine1 = new HtmlElement('input', array_merge(array(
            'type' => 'text',
            'id' => $this->id.'-addressLine1',
            'name' => $this->name.'-addressLine1',
            'class' => 'addressLine1',
            'value' => $this->initialValue['addressLine1'],
            ), $this->inputOptions['addressLine1']
        ));
        $addressLine1Div->append($addressLine1);

        // Add the Address Line 2 input tag
        $addressLine2Div = new HtmlElement('div', array(
            'class' => 'addressLine2Div',
        ));
        $addressLine2 = new HtmlElement('input', array(
            'type' => 'text',
            'id' => $this->id.'-addressLine2',
            'name' => $this->name.'-addressLine2',
            'class' => 'addressLine2',
            'value' => $this->initialValue['addressLine2'],
            'maxLength' => $this->inputOptions['addressLine2']['maxLength'],
        ));
        $addressLine2Div->append($addressLine2);

        // Add the city input tag
        $cityDiv = new HtmlElement('div', array(
            'class' => 'cityDiv',
        ));
        $city = new HtmlElement('input', array(
            'type' => 'text',
            'id' => $this->id.'-city',
            'name' => $this->name.'-city',
            'class' => 'city',
            'maxlength' => '15',
            'value' => $this->initialValue['city'],
        ));
        $cityDiv->append($city);

        // Add the State input tag
        $stateDiv = new HtmlElement('div', array(
            'class' => 'stateDiv',
        ));
        if($this->stateDropDown){
            $state = new HtmlElement('select', array(
                'id' => $this->id.'-state',
                'name' => $this->name.'-state',
                'class' => 'state',
            ));

            if(isset($this->filter['state']) && !is_array($this->filter['state'])){
                $this->filter['state'] = array($this->filter['state']);
            }

            // Add any options that are not in an opt group to the select
            foreach(FormComponentDropDown::getStateArray($this->selectedState, $this->continentalOnly) as $dropDownOption) {
                $optionValue = isset($dropDownOption['value']) ? $dropDownOption['value'] : '';
                $optionLabel = isset($dropDownOption['label']) ? $dropDownOption['label'] : '';
                $optionSelected = isset($dropDownOption['selected']) ? $dropDownOption['selected'] : false;
                $optionDisabled = isset($dropDownOption['disabled']) ? $dropDownOption['disabled'] : false;
                $optionOptGroup = isset($dropDownOption['optGroup']) ? $dropDownOption['optGroup'] : '';
                $option = $this->getOption($optionValue, $optionLabel, $optionSelected, $optionDisabled);
                if($this->initialValue['state'] == $dropDownOption['value']) {
                    $option->attr('selected' , true);
                }
                if(!isset($this->filter['state']) || (isset($this->filter['state']) && !in_array($dropDownOption['value'], $this->filter['state']))){
                    $state->append($option);
                }
                
            }
        }
        else {
            $state = new HtmlElement('input', array(
                'type' => 'text',
                'id' => $this->id.'-state',
                'name' => $this->name.'-state',
                'class' => 'state',
            ));

            if(!empty($this->initialValue['state'])) {
                $state->attr('value' , $this->initialValue['state']);
            }
        }
        $stateDiv->append($state);

        // Add the Zip input tag
        $zipDiv = new HtmlElement('div', array(
            'class' => 'zipDiv',
        ));
        $zip = new HtmlElement('input', array(
            'type' => 'text',
            'id' => $this->id.'-zip',
            'name' => $this->name.'-zip',
            'class' => 'zip',
            //'maxlength' => '5',
            'value' => $this->initialValue['zip'],
        ));
        $zipDiv->append($zip);

        // Add the country input tag
        $countryDiv = new HtmlElement('div', array(
            'class' => 'countryDiv',
        ));
        // Don't built a select list if you are United States only
        if($this->unitedStatesOnly) {
            $country = new HtmlElement('input', array(
                'type' => 'hidden',
                'id' => $this->id.'-country',
                'name' => $this->name.'-country',
                'class' => 'country',
                'value' => 'US',
                'style' => 'display: none;',
            ));
        }
        else {
            $country = new HtmlElement('select', array(
                'id' => $this->id.'-country',
                'name' => $this->name.'-country',
                'class' => 'country',
            ));
            // Add any options that are not in an opt group to the select
            foreach(FormComponentDropDown::getCountryArray($this->selectedCountry) as $dropDownOption) {
                $optionValue = isset($dropDownOption['value']) ? $dropDownOption['value'] : '';
                $optionLabel =  isset($dropDownOption['label']) ? $dropDownOption['label'] : '';
                $optionSelected = isset($dropDownOption['selected']) ? $dropDownOption['selected'] : false;
                $optionDisabled = isset($dropDownOption['disabled']) ? $dropDownOption['disabled'] : false;
                $optionOptGroup = isset($dropDownOption['optGroup']) ? $dropDownOption['optGroup'] : '';
                $option = $this->getOption($optionValue, $optionLabel, $optionSelected, $optionDisabled);
                if($this->initialValue['country'] == $dropDownOption['value']) {
                    $option->attr('selected' , true);
                }

                $country->append($option);
            }
        }
        $countryDiv->append($country);

        // Set the empty values if they are enabled
        if(!empty($this->emptyValues)) {
            foreach($this->emptyValues as $empyValueKey => $emptyValue) {
                if($empyValueKey == 'addressLine1') {
                    $addressLine1->setAttribute('value', $emptyValue);
                    $addressLine1->addClassName('defaultValue');
                }
                if($empyValueKey == 'addressLine2') {
                    $addressLine2->setAttribute('value', $emptyValue);
                    $addressLine2->addClassName('defaultValue');
                }
                if($empyValueKey == 'city') {
                    $city->setAttribute('value', $emptyValue);
                    $city->addClassName('defaultValue');
                }
                if($empyValueKey == 'state' && !$this->stateDropDown) {
                    $state->setAttribute('value', $emptyValue);
                    $state->addClassName('defaultValue');
                }
                if($empyValueKey == 'zip') {
                    $zip->setAttribute('value', $emptyValue);
                    $zip->addClassName('defaultValue');
                }
            }
        }


        // Put the sublabels in if the option allows for it
        if($this->showSublabels) {
            $addressLine1Div->append('<div class="formComponentSublabel"><p>Street Address</p></div>');
            $addressLine2Div->append('<div class="formComponentSublabel"><p>Address Line 2</p></div>');
            $cityDiv->append('<div class="formComponentSublabel"><p>City</p></div>');

            if($this->unitedStatesOnly) {
                $stateDiv->append('<div class="formComponentSublabel"><p>State</p></div>');
            }
            else {
                $stateDiv->append('<div class="formComponentSublabel"><p>State / Province / Region</p></div>');
            }

            if($this->unitedStatesOnly) {
                $zipDiv->append('<div class="formComponentSublabel"><p>Zip Code</p></div>');
            }
            else {
                $zipDiv->append('<div class="formComponentSublabel"><p>Postal / Zip Code</p></div>');
            }

            $countryDiv->append('<div class="formComponentSublabel"><p>Country</p></div>');
        }

        // United States only switch
        if($this->unitedStatesOnly) {
            $countryDiv->attr('style', 'display: none;');
        }

        // Hide address line 2
        if($this->addressLine2Hidden) {
            $addressLine2Div->attr('style', 'display: none;');
        }

        // Insert the address components
        $componentDiv->append($addressLine1Div);
        $componentDiv->append($addressLine2Div);
        $componentDiv->append($cityDiv);
        $componentDiv->append($stateDiv);
        $componentDiv->append($zipDiv);
        $componentDiv->append($countryDiv);

        // Add any description (optional)
        $componentDiv = $this->insertComponentDescription($componentDiv);

        // Add a tip (optional)
        $componentDiv = $this->insertComponentTip($componentDiv);

        return $componentDiv->__toString();
    }

    // Address validations
    public function required($options) {
        $errorMessageArray = array();
        if($options['value']->addressLine1 == '') {
            array_push($errorMessageArray, array('Street Address is required.'));
        }
        if($options['value']->city == '') {
            array_push($errorMessageArray, array('City is required.'));
        }
        if($options['value']->state == '') {
            array_push($errorMessageArray, array('State is required.'));
        }
        if($options['value']->zip == '') {
            array_push($errorMessageArray, array('Zip is required.'));
        }
        if($options['value']->country == '') {
            array_push($errorMessageArray, array('Country is required.'));
        }
        return sizeof($errorMessageArray) < 1 ? 'success' : $errorMessageArray;
    }
}

?>
