<?php
class View {
    var $type = 'html'; // html, json, xml, rss, file, image, null (empty)
    var $content; // HtmlElement, string, binary data

    function View() {
       
    }

    function __toString() {
        $string = '';
        if(is_string($this->content)) {
            $string = $this->content;
        }
        else if(is_object($this->content)) {
            $string = $this->content->__toString();
        }

        return $string;
    }
}
?>