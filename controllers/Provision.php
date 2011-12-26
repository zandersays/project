<?php
class Provision extends Controller {

    function index($data) {
        // Create a document
        $this->view = new HtmlDocument();

        // Title
        $this->view->title = 'Provision a New Project Installation';

        // Styles
        $this->view->css[] = 'http://'.$_SERVER['HTTP_HOST'].'/styles/project.css';
        $this->view->css[] = 'http://'.$_SERVER['HTTP_HOST'].'/styles/forms.css';
        $this->view->css[] = 'http://'.$_SERVER['HTTP_HOST'].'/styles/provision.css';

        // Scripts
        $this->view->javaScript[] = 'http://'.$_SERVER['HTTP_HOST'].'/scripts/jQuery.js';
        $this->view->javaScript[] = 'http://'.$_SERVER['HTTP_HOST'].'/scripts/Class.js';
        $this->view->javaScript[] = 'http://'.$_SERVER['HTTP_HOST'].'/scripts/Json.js';
        $this->view->javaScript[] = 'http://'.$_SERVER['HTTP_HOST'].'/scripts/Form.php';
        $this->view->javaScript[] = 'http://'.$_SERVER['HTTP_HOST'].'/scripts/Project.js';


        $this->view->head->append($this->getView('Project:provision/head'));
        $this->view->body->append($this->getHtmlElement('Project:provision/provision'));

        return $this->view;
    }

}
?>