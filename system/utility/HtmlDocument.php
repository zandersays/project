<?php
class HtmlDocument extends HtmlElement {
    
    public $docType;
    public $title;
    public $head;
    public $body;
    public $css = array();
    public $javaScript = array();
    public $javaScriptOnReady = array();

    function HtmlDocument($attributes = array(), $docType = null) {
        parent::HtmlElement('html');
        $this->docType = $docType;

        $this->head = new HtmlElement('head');
        $this->append($this->head);

        $this->body = new HtmlElement('body');
        $this->append($this->body);
    }

    function getJavaScript() {
        $this->javaScript = array_unique($this->javaScript);
        $javaScriptString = '';
        foreach($this->javaScript as $javaScript) {
            if(String::startsWith('http://', $javaScript) || String::startsWith('https://', $javaScript)) {
                $javaScriptString .= HtmlElement::javaScript($javaScript);
            }
            else if(String::contains('project/', $javaScript)) {
                $javaScriptString .= HtmlElement::javaScript(Project::getInstanceAccessPath().$javaScript);
            }
            else {
                $javaScriptString .= HtmlElement::javaScript(Project::getInstanceAccessPath().'scripts/'.$javaScript);
            }

            // Handle some items for the Project User script
            if($javaScript == 'project/scripts/User.js') {
                //echo 'Using the Project user class!'; exit();
                $this->javaScriptOnReady[] = 'User.loggedIn = '.(UserApi::$user ? 'true' : 'false').';';
                $this->javaScriptOnReady[] = 'User.authenticationMethod = \''.UserApi::$user['authenticationMethod'].'\';';
            }
        }

        return $javaScriptString;
    }

    function getCss() {
        $this->css = array_unique($this->css);
        $cssString = '';
        foreach($this->css as $css) {
            if(String::startsWith('http://', $css) || String::startsWith('https://', $css)) {
                $cssString .= HtmlElement::css($css);
            }
            else if(String::contains('project/', $css)) {
                $cssString .= HtmlElement::css(Project::getInstanceAccessPath().$css);
            }
            else {
                $cssString .= HtmlElement::css(Project::getInstanceAccessPath().'styles/'.$css);
            }
        }
        
        return $cssString;
    }

    function getJavaScriptOnReady() {
        if(empty($this->javaScriptOnReady)) {
            $javaScriptOnReadyString = '';
        }
        else {
            $this->javaScriptOnReady = array_unique($this->javaScriptOnReady);

            $javaScriptOnReadyString = "$(document).ready(function() {";
            foreach($this->javaScriptOnReady as $javaScriptOnReady) {
                $javaScriptOnReadyString .= $javaScriptOnReady;
            }
            $javaScriptOnReadyString .= '});';
            $javaScriptOnReadyString = HtmlElement::javaScript(array('text' => $javaScriptOnReadyString));
        }
        return $javaScriptOnReadyString;
    }
    
    function __toString() {
        // Set the document type
        if($this->docType === null) {
            $this->docType = '<!DOCTYPE html>';
        }

        // Set the title
        if($this->title !== null) {
            $this->head->append(HtmlElement::title($this->title));
        }

        // Add CSS
        $this->head->append($this->getCss());

        // Add JavaScript
        $this->head->append($this->getJavaScript());

        // Add JavaScript on ready
        $this->head->append($this->getJavaScriptOnReady());

        // Build the HTML string
        $string = $this->docType;
        $string .= parent::__toString();

        return $string;
    }

}
?>