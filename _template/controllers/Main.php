<?php
class Main extends Controller {

    public function index($data) {

        // Create a document
        $this->view = new HtmlDocument();

        // Set the title
        $this->view->title = Project::getSiteTitle();
        
        // Styles
        $this->view->css[] = 'project/styles/project.css';
        $this->view->css[] = 'project/styles/forms.css';
        $this->view->css[] = 'site.css';

        // Scripts
        $this->view->javaScript[] = 'project/scripts/jQuery.js';
        $this->view->javaScript[] = 'project/scripts/Class.js';
        $this->view->javaScript[] = 'project/scripts/Json.js';
        $this->view->javaScript[] = 'project/scripts/Form.php';

        // Set the document head
        $this->view->head->append($this->head());

        // Set the document body
        $this->view->body->append($this->header());
        $this->view->body->append($this->home());
        $this->view->body->append($this->footer());

        return $this->view;
    }

    public function head($data = array()) {
        return $this->getView('head', $data);
    }

    public function header($data = array()) {
        return $this->getView('header', $data);
    }

    public function home($data = array()) {
        return $this->getView('home');
    }

    public function footer($data = array()) {
        return $this->getView('footer', array('siteTitle' => Project::getSiteTitle()));
    }

}
?>