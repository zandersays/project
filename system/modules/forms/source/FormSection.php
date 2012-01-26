<?php

/**
 * A FormSection object contains FormComponent objects and belongs to a FormPage object
 */
class FormSection {

    // General settings
    var $id;
    var $class = 'formSection';
    var $style = '';
    var $parentFormPage;
    var $formComponentArray = array();
    var $data;
    var $anonymous = false;

    // Title, description, submit instructions
    var $title = '';
    var $titleClass = 'formSectionTitle';
    var $description = '';
    var $descriptionClass = 'formSectionDescription';

    // Options
    var $instanceOptions = null;
    var $dependencyOptions = null;

    // Validation
    var $errorMessageArray = array();

    /*
     * Constructor
     */
    function __construct($id, $optionArray = array(), $formComponentArray = array()) {
        // Set the id
        $this->id = $id;
     
        // Use the options hash to update object variables
        if(is_array($optionArray)) {
            foreach($optionArray as $option => $value) {
                $this->{$option} = $value;
            }
        }

        // Make sure initial instances is not greater than the max allowed
        if($this->instanceOptions !== null && isset($this->instanceOptions['initialValues']) && sizeof($this->instanceOptions['initialValues']) > $this->instanceOptions['max'] && $this->instanceOptions['max'] != 0) {
            $this->instanceOptions['initialValues'] = array_slice($this->instanceOptions['initialValues'], 0, $this->instanceOptions['max']);
        }

        // Add the components from the constructor
        $this->addFormComponentArray($formComponentArray);

        return $this;
    }

    function createInstance($instanceIndex) {
        $formSectionInstance = new FormSection($this->id.'-section'.$instanceIndex);

        foreach($this as $key => $value) {
            if($key == 'id') {
                // Do nothing
            }
            else if($key == 'formComponentArray') {
                $formSectionInstance->{$key} = array();
                foreach($value as $formComponent) {
                    $formSectionInstance->{$key}[] = clone $formComponent;
                }
            }
            else if($key == 'title') {
                $formSectionInstance->{$key} = $value;
                if(String::contains('</', $formSectionInstance->title)) {
                    $formSectionInstance->title = String::replace('<\/', ' ('.$instanceIndex.')</', $formSectionInstance->title, 1);
                }
                else {
                    $formSectionInstance->title = $formSectionInstance->title.' ('.$instanceIndex.')';
                }
            }
            else {
                $formSectionInstance->{$key} = $value;
            }
        }

        return $formSectionInstance;
    }

    function add($formVariable) {
        // Handle arrays
        if(Arr::is($formVariable)) {
            $firstVariable = Arr::first($formVariable);
            $className = Object::className($firstVariable);
            if(String::startsWith('FormComponent', $className)) {
                $this->addFormComponentArray($formVariable);
            }
        }
        // Handle single items
        else {
            $className = Object::className($formVariable);
            if(String::startsWith('FormComponent', $className)) {
                $this->addFormComponent($formVariable);
            }
        }

        return $this;
    }

    function addFormComponent($formComponent) {
        $formComponent->parentFormSection = $this;
        $this->formComponentArray[$formComponent->id] = $formComponent;
        //print_r($formComponent);
        return $this;
    }

    function addFormComponentArray($formComponentArray) {
        foreach($formComponentArray as $formComponent) {
            $this->addFormComponent($formComponent);
        }
        return $this;
    }

    function getData() {
        $this->data = array();

        // Check to see if formComponent array contains instances
        if(array_key_exists(0, $this->formComponentArray) && is_array($this->formComponentArray[0])) {
            foreach($this->formComponentArray as $formComponentArrayInstanceIndex => $formComponentArrayInstance) {
                foreach($formComponentArrayInstance as $formComponentKey => $formComponent) {
                    if(get_class($formComponent) != 'FormComponentHtml') { // Don't include HTML components
                        $this->data[$formComponentArrayInstanceIndex][$formComponentKey] = $formComponent->getValue();
                    }
                }
            }
        }
        // If the section does not have instances
        else {
            foreach($this->formComponentArray as $formComponentKey => $formComponent) {
                if(get_class($formComponent) != 'FormComponentHtml') { // Don't include HTML components
                    $this->data[$formComponentKey] = $formComponent->getValue();
                }
            }
        }

        return $this->data;
    }

    function setInitialValues($formSectionData) {
        // Handle section instances
        if(Arr::is($formSectionData) && Arr::hasKey(0, $formSectionData)) {
            $this->instanceOptions['initialValues'] = $formSectionData;
        }
        // Handle normal instances
        else {
            foreach($formSectionData as $formComponentKey => $formComponentData) {
                $this->formComponentArray[$formComponentKey]->setInitialValues($formComponentData);
            }
        }
    }

    function setData($formSectionData) {
        // Handle multiple instances
        if(is_array($formSectionData)) {
            $newFormComponentArray = array();
            
            // Go through each section instance
            foreach($formSectionData as $formSectionIndex => $formSection) {
                // Create a clone of the formComponentArray
                $newFormComponentArray[$formSectionIndex] = unserialize(serialize($this->formComponentArray));

                // Go through each component in the instanced section
                foreach($formSection as $formComponentKey => $formComponentValue) {
                    // Set the value of the clone
                    $newFormComponentArray[$formSectionIndex][$formComponentKey]->setValue($formComponentValue);
                }
            }
            $this->formComponentArray = $newFormComponentArray;
        }
        // Single instance
        else {
            // Go through each component
            if(!empty($formSectionData)) {
                foreach($formSectionData as $formComponentKey => $formComponentValue) {
                    if(isset($this->formComponentArray[$formComponentKey])) {
                        $this->formComponentArray[$formComponentKey]->setValue($formComponentValue);
                    }
                }
            }
        }
    }

    function clearData() {
        // Check to see if formComponent array contains instances
        if(array_key_exists(0, $this->formComponentArray) && is_array($this->formComponentArray[0])) {
            foreach($this->formComponentArray as $formComponentArrayInstanceIndex => $formComponentArrayInstance) {
                foreach($formComponentArrayInstance as $formComponentKey => $formComponent) {
                    $formComponent->clearValue();
                }
            }
        }
        // If the section does not have instances
        else {
            foreach($this->formComponentArray as $formComponent) {
                $formComponent->clearValue();
            }
        }
        $this->data = null;
    }

    function validate() {
        // Clear the error message array
        $this->errorMessageArray = array();

        // If we have instances, return an array
        if(array_key_exists(0, $this->formComponentArray) && is_array($this->formComponentArray[0])) {
            foreach($this->formComponentArray as $formComponentArrayInstanceIndex => $formComponentArrayInstance) {
                foreach($formComponentArrayInstance as $formComponentKey => $formComponent) {
                    $this->errorMessageArray[$formComponentArrayInstanceIndex][$formComponent->id] = $formComponent->validate();
                }
            }
        }
        // If the section does not have instances, return an single dimension array
        else {
            foreach($this->formComponentArray as $formComponent) {
                $this->errorMessageArray[$formComponent->id] = $formComponent->validate();
            }
        }

        return $this->errorMessageArray;
    }

    function getOptions() {
        $options = array();
        $options['options'] = array();
        $options['formComponents'] = array();
        
        // Instances
        if(!empty($this->instanceOptions)) {
            $options['options']['instanceOptions'] = $this->instanceOptions;
            if(!isset($options['options']['instanceOptions']['addButtonText'])) {
                $options['options']['instanceOptions']['addButtonText'] = 'Add Another';
            }
            if(!isset($options['options']['instanceOptions']['removeButtonText'])) {
                $options['options']['instanceOptions']['removeButtonText'] = 'Remove';
            }
        }

        // Dependencies
        if(!empty($this->dependencyOptions)) {
            // Make sure the dependentOn key is tied to an array
            if(isset($this->dependencyOptions['dependentOn']) && !is_array($this->dependencyOptions['dependentOn'])) {
                $this->dependencyOptions['dependentOn'] = array($this->dependencyOptions['dependentOn']);
            }
            $options['options']['dependencyOptions'] = $this->dependencyOptions;
        }

        // Get options for each of the formComponents
        if(array_key_exists(0, $this->formComponentArray)){
            foreach ($this->formComponentArray as $instanceIndex => $formSectionInstance) {
                foreach ($formSectionInstance as $formComponent) {
                    // Don't get options for FormComponent1 objects
                    if (get_class($formComponent) != 'FormComponentHtml') {
                        $options['formComponents'][$formComponent->id] = $formComponent->getOptions();
                    }
                }
            }
        } else {
            foreach ($this->formComponentArray as $formComponent) {
                // Don't get options for FormComponent1 objects
                if (get_class($formComponent) != 'FormComponentHtml') {
                    $options['formComponents'][$formComponent->id] = $formComponent->getOptions();
                }
            }
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
        // Section fieldset
        $formSectionDiv = new HtmlElement('div', array(
            'id' => $this->id,
            'class' => $this->class
        ));
        
        if($this->anonymous) {
            $formSectionDiv->addClass('formSectionAnonymous');
        }

        // This causes issues with things that are dependent and should display by default
        // If the section has dependencies and the display type is hidden, hide by default
        //if($this->dependencyOptions !== null && isset($this->dependencyOptions['display']) && $this->dependencyOptions['display'] == 'hide') {
        //    $formSectionDiv->setAttribute('style', 'display: none;');
        //}

        // Set the style
        if(!empty($this->style)) {
            $formSectionDiv->attr('style', $this->style, true);
        }

        // Add a title to the page
        if(!empty($this->title)) {
            $title = new HtmlElement('div', array(
                'class' => $this->titleClass
            ));
            $title->html($this->title);
            $formSectionDiv->append($title);
        }

        // Add a description to the page
        if(!empty($this->description)) {
            $description = new HtmlElement('div', array(
                'class' => $this->descriptionClass
            ));
            $description->html($this->description);
            $formSectionDiv->append($description);
        }

        // Add the form sections to the page
        foreach($this->formComponentArray as $formComponent) {
            $formSectionDiv->append($formComponent);
        }
        
        return $formSectionDiv->__toString();
    }
}
?>