<?php
class Setup extends Controller {

    function index($data) {

        // Create a document
        $this->view = new HtmlDocument();

        // Title
        $this->view->title = 'Setup Your New Project Installation';

        // Styles
        $this->view->css[] = 'project/styles/project.css';
        $this->view->css[] = 'project/styles/forms.css';
        $this->view->css[] = 'project/styles/setup.css';

        // Scripts
        $this->view->javaScript[] = 'project/scripts/jQuery.js';
        $this->view->javaScript[] = 'project/scripts/Class.js';
        $this->view->javaScript[] = 'project/scripts/Json.js';
        $this->view->javaScript[] = 'project/scripts/Form.php';
        $this->view->javaScript[] = 'project/scripts/Project.js';

        $this->view->head->append($this->getView('Project:setup/head'));
        $this->view->body->append($this->getHtmlElement('Project:setup/setup'));

        return $this->view;
    }

}
?>