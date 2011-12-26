<?php
/**
 * A FormPage object contains FormSection objects and belongs to a Form object
 */
class FormPage {

    // General settings
    var $id;
    var $class = 'formPage';
    var $style = '';
    var $form;
    var $formSectionArray = array();
    var $onBeforeScrollTo; // array('function', 'notificationHtml')
    var $onBeforeScrollAway;
    var $data;
    var $anonymous = false;

    // Title, description, submit instructions
    var $title = '';
    var $titleClass = 'formPageTitle';
    var $description = '';
    var $descriptionClass = 'formPageDescription';
    var $submitInstructions = '';
    var $submitInstructionsClass = 'formPageSubmitInstructions';

    // Validation
    var $errorMessageArray = array();

    // Options
    var $dependencyOptions = null;
    var $pageNavigator = null;

    /*
     * Constructor
     */
    function __construct($id, $optionArray = array(), $formSectionArray = array()) {
        // Set the id
        $this->id = $id;

        // Use the options hash to update object variables
        if(is_array($optionArray)) {
            foreach($optionArray as $option => $value) {
                $this->{$option} = $value;
            }
        }

        // Add the sections from the constructor
        foreach($formSectionArray as $formSection) {
            $this->addFormSection($formSection);
        }

        return $this;
    }

    function set($key, $value) {
        $this->{$key} = $value;

        return $this;
    }

    function add($formVariable) {
        // Handle arrays
        if(Arr::is($formVariable)) {
            $firstVariable = Arr::first($formVariable);
            $className = Object::className($firstVariable);
            if($className == 'FormSection') {
                $this->addFormSectionArray($formVariable);
            }
            else if(String::startsWith('FormComponent', $className)) {
                $this->addFormComponentArray($formVariable);
            }
        }
        // Handle single items
        else {
            $className = Object::className($formVariable);
            if($className == 'FormSection') {
                $this->addFormSection($formVariable);
            }
            else if(String::startsWith('FormComponent', $className)) {
                $this->addFormComponent($formVariable);
            }
        }

        return $this;
    }

    function addFormSectionArray($formSectionArray) {
        foreach($formSectionArray as $formSection) {
            $this->addFormSection($formSection);
        }

        return $this;
    }

    function addFormSection($formSection) {
        $formSection->parentFormPage = $this;
        $this->formSectionArray[$formSection->id] = $formSection;

        return $this;
    }

    // Convenience method, no need to create a section to get components on the page
    function addFormComponent($formComponent) {
        // Create an anonymous section if necessary
        if(empty($this->formSectionArray)) {
            $this->addFormSection(new FormSection($this->id.'-section1', array('anonymous' => true)));
        }

        // Get the last section in the page
        $lastFormSection = end($this->formSectionArray);

        // If the last section exists and is anonymous, add the component to it
        if(!empty($lastFormSection) && $lastFormSection->anonymous) {
            $lastFormSection->addFormComponent($formComponent);
        }
        // If the last section in the page does not exist or is not anonymous, add a new anonymous section and add the component to it
        else {
            // Create an anonymous section
            $anonymousSection = new FormSection($this->id.'-section'.(sizeof($this->formSectionArray) + 1), array('anonymous' => true));

            // Add the anonymous section to the page
            $this->addFormSection($anonymousSection->addFormComponent($formComponent));
        }

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
        foreach($this->formSectionArray as $formSectionKey => $formSection) {
            $this->data[$formSectionKey] = $formSection->getData();
        }
        return $this->data;
    }

    function setInitialValues($formPageData) {
        foreach($formPageData as $formSectionKey => $formSectionData) {
            $this->formSectionArray[$formSectionKey]->setInitialValues($formSectionData);
        }
    }

    function setData($formPageData) {
        if(!empty($formPageData)) {
            foreach($formPageData as $formSectionKey => $formSectionData) {
                if(isset($this->formSectionArray[$formSectionKey]) && Object::is($this->formSectionArray[$formSectionKey])) {
                    $this->formSectionArray[$formSectionKey]->setData($formSectionData);
                }
            }
        }
    }

    function clearData() {
        foreach($this->formSectionArray as $formSection) {
            $formSection->clearData();
        }
        $this->data = null;
    }

    function validate() {
        // Clear the error message array
        $this->errorMessageArray = array();

        // Validate each section
        foreach($this->formSectionArray as $formSection) {
            $this->errorMessageArray[$formSection->id] = $formSection->validate();
        }

        return $this->errorMessageArray;
    }

    function getOptions() {
        $options = array();
        $options['options'] = array();
        $options['formSections'] = array();

        foreach($this->formSectionArray as $formSection) {
            $options['formSections'][$formSection->id] = $formSection->getOptions();
        }

        if(!empty($this->onScrollTo)) {
            $options['options']['onScrollTo'] = $this->onScrollTo;
        }
        
        if(!empty($this->onScrollAway)) {
            $options['options']['onScrollAway'] = $this->onScrollAway;
        }
        
        if(!empty($this->pageNavigator)) {
            $options['options']['pageNavigator'] = $this->pageNavigator;
        }

        // Dependencies
        if(!empty($this->dependencyOptions)) {
            // Make sure the dependentOn key is tied to an array
            if(isset($this->dependencyOptions['dependentOn']) && !is_array($this->dependencyOptions['dependentOn'])) {
                $this->dependencyOptions['dependentOn'] = array($this->dependencyOptions['dependentOn']);
            }
            $options['options']['dependencyOptions'] = $this->dependencyOptions;
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
        // Page div
        $formPageDiv = new HtmlElement('div', array(
            'id' => $this->id,
            'class' => $this->class
        ));

        // Set the styile
        if(!empty($this->style)) {
            $formPageDiv->attr('style', $this->style, true);
        }

        // Add a title to the page
        if(!empty($this->title)) {
            $title = new HtmlElement('div', array(
                'class' => $this->titleClass
            ));
            $title->html($this->title);
            $formPageDiv->append($title);
        }

        // Add a description to the page
        if(!empty($this->description)) {
            $description = new HtmlElement('div', array(
                'class' => $this->descriptionClass
            ));
            $description->html($this->description);
            $formPageDiv->append($description);
        }

        // Add the form sections to the page
        foreach($this->formSectionArray as $formSection) {

            // Handle initial values on section instances
            if($formSection->instanceOptions !== null && isset($formSection->instanceOptions['initialValues']) && !empty($formSection->instanceOptions['initialValues'])) {
                // Make sure we are always working with an array
                if(Object::is($formSection->instanceOptions['initialValues']) || !isset($formSection->instanceOptions['initialValues'][0])) {
                    $formSection->instanceOptions['initialValues'] = array($formSection->instanceOptions['initialValues']);
                }

                foreach($formSection->instanceOptions['initialValues'] as $sectionInstanceIndex => $sectionInstance) {
                    // Set the current values
                    if($sectionInstanceIndex == 0) {
                        foreach($formSection->formComponentArray as $formComponent) {
                            foreach($sectionInstance as $formComponentId => $formComponentData) {
                                if($formComponent->id == $formComponentId) {
                                    $formComponent->setInitialValues($formComponentData);
                                }
                            }
                        }
                        $formPageDiv->append($formSection);
                    }
                    // Create additional instances as required by the initial values
                    else {
                        $instancedFormSection = $formSection->createInstance($sectionInstanceIndex + 1);
                        foreach($instancedFormSection->formComponentArray as $formComponent) {
                            foreach($sectionInstance as $formComponentId => $formComponentData) {
                                if($formComponent->id == $formComponentId) {
                                    $formComponent->setInitialValues($formComponentData);
                                }
                            }
                            $formComponent->id = $formComponent->id.'-section'.($sectionInstanceIndex + 1);
                            // Handle check boxes which depend on the name attribute
                            if(isset($formComponent->name)) {
                                $formComponent->name = $formComponent->id;
                            }
                        }
                        $formPageDiv->append($instancedFormSection);
                    }
                }

            }
            // Handle sections without instances
            else {
                $formPageDiv->append($formSection);
            }
        }

        // Submit instructions
        if(!empty($this->submitInstructions)) {
            $submitInstruction = new HtmlElement('div', array(
                'class' => $this->submitInstructionsClass
            ));
            $submitInstruction->html($this->submitInstructions);
            $formPageDiv->append($submitInstruction);
        }

        return $formPageDiv->__toString();
    }
}
?>