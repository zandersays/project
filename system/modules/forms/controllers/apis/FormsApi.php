<?php

class FormsApi extends Api {

    public $commands = array(
        'processForm' => array(
            'arguments' => array(
                array('name' => 'view', 'required' => true),
                array('name' => 'formData', 'required' => true),
                array('name' => 'viewData', 'required' => false),
            ),
        ),
        'processFormComponentFile' => array(
            'arguments' => array(
                array('name' => 'view', 'required' => true),
                array('name' => 'formComponentId', 'required' => true),
                array('name' => 'fileName', 'required' => true),
                array('name' => 'controller', 'required' => true),
                array('name' => 'function', 'required' => true),
                array('name' => 'viewData', 'required' => false),
            ),
        ),
    );
    
    function processFormComponentFile($view, $formComponentId, $fileName, $controller, $function, $viewData = array()) {
        //return array('failureNoticeHtml' => Json::encode(array('view' => $view, 'formComponentId' => $formComponentId)));
        
        // Decode the viewData
        if(!Arr::is($viewData)) {
            $viewData = Object::arr(Json::decode(Url::decode($viewData)));
        }

        // Force the viewData to be an array
        if(Object::is($viewData)) {
            $viewData = Object::arr($viewData);
        }
        
        // Force the output type to JSON
        $this->outputType = 'json';

        // Include the form from the view
        if(String::startsWith('system', $view)) {
            $form = $this->getVariable('Module:'.$view, $viewData);
        }
        else {
            $form = $this->getVariable($view, $viewData);
        }
        
        // Validate the form and return the forms controller function response
        $processFormComponent = $form->processFormComponentFile($formComponentId, $fileName, $controller, $function);
        
        return $processFormComponent;
    }

    function processForm($view, $formData, $viewData = array()) {
        // Decode the viewData
        if(!Arr::is($viewData)) {
            $viewData = Object::arr(Json::decode(Url::decode($viewData)));
        }

        // Force the viewData to be an array
        if(Object::is($viewData)) {
            $viewData = Object::arr($viewData);
        }
        
        // Force the output type to string
        $this->outputType = 'string';

        // Include the form from the view
        if(String::startsWith('system', $view)) {
            $form = $this->getVariable('Module:'.$view, $viewData);
        }
        else {
            $form = $this->getVariable($view, $viewData);
        }

        // Validate the form and return the forms controller function response
        return $form->process($formData);
    }

}

?>