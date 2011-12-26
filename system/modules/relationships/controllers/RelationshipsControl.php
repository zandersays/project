<?php
class RelationshipsControl extends Controller {

    function index($data) {
        
        if(empty($data['function'])) {
            $data['function'] = 'dashboard';
        }
        
        return $this->{$data['function']}($data);
    }
    
    function dashboard($data) {
        return $this->getHtmlElement('Module:relationships/relationships/relationships', $data);
    }
    
}
?>