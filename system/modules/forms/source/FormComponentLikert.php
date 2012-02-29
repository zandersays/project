<?php

class FormComponentLikert extends FormComponent {
    var $choiceArray = array();
    var $statementArray = array();
    var $showTableHeading = true;
    var $collapseLabelIntoTableHeading = false;

    /**
     * Constructor
     */
    function __construct($id, $label, $choiceArray, $statementArray, $optionsArray) {
        // General settings
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'formComponentLikert';
        $this->label = $label;

        $this->choiceArray = $choiceArray;
        $this->statementArray = $statementArray;

        // Initialize the abstract FormComponent object
        $this->initialize($optionsArray);
    }

    function getOptions() {
        $options = parent::getOptions();

        $statementArray = array();
        foreach($this->statementArray as $statement) {
            $statementArray[$statement['name']] = array();

            if(!empty($statement['validationOptions'])) {
                $statementArray[$statement['name']]['validationOptions'] = $statement['validationOptions'];
            }

            if(!empty($statement['triggerFunction'])) {
                $statementArray[$statement['name']]['triggerFunction'] = $statement['triggerFunction'];
            }
        }

        $options['options']['statementArray'] = $statementArray;

        // Make sure you have an options array to manipulate
        if(!isset($options['options'])) {
            $options['options']  = array();
        }

        return $options;
    }

    /**
     *
     * @return string
     */
    function __toString() {
        // Generate the component div
        $componentDiv = parent::generateComponentDiv(!$this->collapseLabelIntoTableHeading);

        // Create the table
        $table = new HtmlElement('table', array('class' => 'formComponentLikertTable'));

        // Generate the first row
        if($this->showTableHeading) {
            $tableHeadingRow = new HtmlElement('tr', array('class' => 'formComponentLikertTableHeading'));

            $tableHeading = new HtmlElement('th', array(
                'class' => 'formComponentLikertStatementColumn',
            ));
            // Collapse the label into the heading if the option is set
            if($this->collapseLabelIntoTableHeading) {
                $tableHeadingLabel = new HtmlElement('label', array(
                    'class' => 'formComponentLikertStatementLabel',
                ));
                $tableHeadingLabel->html($this->label);
                // Add the required star to the label
                if(in_array('required', $this->validationOptions)) {
                    $labelRequiredStarSpan = new HtmlElement('span', array(
                        'class' => $this->labelRequiredStarClass
                    ));
                    $labelRequiredStarSpan->html(' *');
                    $tableHeadingLabel->append($labelRequiredStarSpan);
                }
                $tableHeading->append($tableHeadingLabel);
            }
            $tableHeadingRow->append($tableHeading);

            foreach($this->choiceArray as $choice) {
                $tableHeadingRow->append('<th>'.$choice['label'].'</th>');
            }
            $table->append($tableHeadingRow);
        }
        
        // Insert each of the statements
        $statementCount = 0;
        foreach($this->statementArray as $statement) {
            // Set the row style
            if($statementCount % 2 == 0) {
                $statementRowClass = 'formComponentLikertTableRowEven';
            }
            else {
                $statementRowClass = 'formComponentLikertTableRowOdd';
            }

            // Set the statement
            $statementRow = new HtmlElement('tr', array('class' => $statementRowClass));
            $statementColumn = new HtmlElement('td', array('class' => 'formComponentLikertStatementColumn'));
            $statementLabel = new HtmlElement('label', array(
                'class' => 'formComponentLikertStatementLabel',
                'for' => $statement['name'].'-choice1',
            ));
            $statementColumn->append($statementLabel->append($statement['statement']));

            // Set the statement description (optional)
            if(!empty($statement['description'])) {
                $statementDescription = new HtmlElement('div', array(
                    'class' => 'formComponentLikertStatementDescription',
                ));
                $statementColumn->append($statementDescription->html($statement['description']));
            }

            // Insert a tip (optional)
            if(!empty($statement['tip'])) {
                $statementTip = new HtmlElement('div', array(
                    'class' => 'formComponentLikertStatementTip',
                    'style' => 'display: none;',
                ));
                $statementColumn->append($statementTip->html($statement['tip']));
            }

            $statementRow->append($statementColumn);

            $choiceCount = 1;
            foreach($this->choiceArray as $choice) {
                $choiceColumn = new HtmlElement('td');

                $choiceInput = new HtmlElement('input', array(
                    'id' => $statement['name'].'-choice'.$choiceCount,
                    'type' => 'radio',
                    'value' => $choice['value'],
                    'name' => $statement['name'],
                ));
                // Set a selected value if defined
                if(!empty($statement['selected'])) {
                    if($statement['selected'] == $choice['value']) {
                        $choiceInput->attr('checked', 'checked');
                    }
                }
                $choiceColumn->append($choiceInput);

                // Choice sub labels
                if(!empty($choice['sublabel'])) {
                    $choiceSublabel = new HtmlElement('label', array(
                        'class' => 'formComponentLikertSublabel',
                        'for' => $statement['name'].'-choice'.$choiceCount,
                    ));
                    $choiceSublabel->html($choice['sublabel']);
                    $choiceColumn->append($choiceSublabel);
                }

                $statementRow->append($choiceColumn);
                $choiceCount++;
            }
            $statementCount++;

            $table->append($statementRow);
        }

        $componentDiv->append($table);

        // Add any description (optional)
        $componentDiv = $this->insertComponentDescription($componentDiv);

        // Add a tip (optional)
        $componentDiv = $this->insertComponentTip($componentDiv, $this->id.'-div');

        return $componentDiv->__toString();
    }

    // Validation
    public function required($options) {
        $errorMessageArray = array();
        foreach($options['value'] as $key => $statement) {
            if(empty($statement)) {
                //print_r($key);
                //print_r($statement);
                array_push($errorMessageArray, array($key => 'Required.'));
            }
        }

        return sizeof($errorMessageArray) == 0 ? 'success' : $errorMessageArray;
    }
}

class FormComponentLikertStatement extends FormComponent {
    /**
     * Constructor
     */
    function __construct($id, $label, $choiceArray, $statementArray, $optionsArray) {
        // General settings
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'formComponentLikertStatement';
        $this->label = $label;
        // Initialize the abstract FormComponent object
        $this->initialize($optionsArray);
    }

    function  __toString() {
        return;
    }
}

?>
