<?php

class FormComponentHidden extends FormComponent {
    /*
     * Constructor
     */
    function __construct($id, $value = '', $optionArray = array()) {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'formComponentHidden';

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);

        // Prevent the value from being overwritten
        $this->value = $value;
    }

    /**
     *
     * @return string
     */
    function __toString() {
        // Generate the component div without a label
        $div = $this->generateComponentDiv(false);
        $div->attr('style', 'display: none;', true);

        // Input tag
        $input = new HtmlElement('input', array(
            'type' => 'hidden',
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
        ));
        $div->append($input);

        return $div->__toString();
    }
}

?>
