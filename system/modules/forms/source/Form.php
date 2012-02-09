<?php
class Form {

    // General settings
    public $id;
    public $class = 'form';
    public $action;
    public $style;
    public $onMouseOver;
    public $onMouseOut;
    public $formPageArray = array();
    public $formId;
    public $onSubmitFunctionServerSide = 'onSubmit';
    public $disableAnalytics = false;
    public $setupPageScroller = true;
    public $data;

    // Forms API
    public $view;
    public $viewData = array();
    public $controller = 'Main';
    public $function;

    // Title, description, and submission button
    public $title = '';
    public $titleClass = 'formTitle';
    public $description = '';
    public $descriptionClass = 'formDescription';
    public $submitButtonText = 'Submit';
    public $previousButtonText = 'Previous';
    public $submitProcessingButtonText = 'Processing...';
    public $afterControl = '';
    public $cancelButton = false;
    public $cancelButtonOnClick = '';
    public $cancelButtonText = 'Cancel';
    public $cancelButtonClass = 'cancelButton';
    public $cancelButtonLiBeforeNextButtonLi = true;
    public $formControlLiButtonClass = '';

    // Form options
    public $alertsEnabled = true;
    public $clientSideValidation = true;
    public $validationTips = true;

    // Page navigator
    public $pageNavigatorEnabled = false;
    public $pageNavigator = array();
    
    // Allow the form to start at a specific page
    public $startingPageId = false;

    // Progress bar
    public $progressBar = false;

    // Splash page
    public $splashPageEnabled = false;
    public $splashPage = array();

    // Animations
    public $animationOptions = null;

    // Custom script execution before form submission
    public $onSubmitStartClientSide = '';
    public $onSubmitFinishClientSide = '';

    // Security options
    public $requireSsl = false; // Not implemented yet

    // Essential class variables
    public $status = array('status' => 'processing', 'response' => 'Form initialized.');

    // Validation
    public $validationResponse = array();
    public $validationPassed = null;
    
    

    /**
     * Constructor
     */
    function __construct($id, $optionArray = array(), $formPageArray = array()) {
        // Set the id
        $this->id = $id;

        // Set the action dynamically
        $this->action = Project::getInstanceAccessPath().'api/forms/processForm/';

        // Set the view dynamically
        $this->view = String::replace(Project::getProjectPath(), '', $this->view);
        $this->view = String::replaceLast('.php', '', $this->view);

        // Use the options array to update the form variables
        if(is_array($optionArray)) {
            foreach($optionArray as $option => $value) {
                $this->{$option} = $value;
            }
        }
        
        // Set defaults for the page navigator        
        if(!empty($this->pageNavigator)) {
            $this->pageNavigatorEnabled = true;
        }
        else if($this->pageNavigator == true){
            $this->pageNavigator = array(
                'position' => 'top'
            );
        }

        // Set defaults for the splash page
        if(!empty($this->splashPage)) {
            $this->splashPageEnabled = true;
        }

        // Add the pages from the constructor
        foreach($formPageArray as $formPage) {
            $this->addFormPage($formPage);
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
            if($className == 'FormPage') {
                $this->addFormPageArray($formVariable);
            }
            else if($className == 'FormSection') {
                $this->addFormSectionArray($formVariable);
            }
            else if(String::startsWith('FormComponent', $className)) {
                $this->addFormComponentArray($formVariable);
            }
        }
        // Handle single items
        else {
            $className = Object::className($formVariable);
            if($className == 'FormPage') {
                $this->addFormPage($formVariable);
            }
            else if($className == 'FormSection') {
                $this->addFormSection($formVariable);
            }
            else if(String::startsWith('FormComponent', $className)) {
                $this->addFormComponent($formVariable);
            }
        }

        return $this;
    }

    function addFormPageArray($formPageArray) {
        foreach($formPageArray as $formPage) {
            $this->addFormPage($formPage);
        }

        return $this;
    }

    function addFormPage($formPage) {
        $formPage->form = $this;
        $this->formPageArray[$formPage->id] = $formPage;
        return $this;
    }

    // Convenience method, no need to create a page or section to get components on the form
    function addFormComponent($formComponent) {
        // Create an anonymous page if necessary
        if(empty($this->formPageArray)) {
            $this->addFormPage(new FormPage($this->id.'-page1', array('anonymous' => true)));
        }

        // Get the first page in the formPageArray
        $currentFormPage = Arr::first($this->formPageArray);

        // Get the last section in the page
        $lastFormSection = end($currentFormPage->formSectionArray);

        // If the last section exists and is anonymous, add the component to it
        if(!empty($lastFormSection) && $lastFormSection->anonymous) {
            $lastFormSection->addFormComponent($formComponent);
        }
        // If the last section in the page does not exist or is not anonymous, add a new anonymous section and add the component to it
        else {
            // Create an anonymous section
            $anonymousSection = new FormSection($currentFormPage->id.'-section'.(sizeof($currentFormPage->formSectionArray) + 1), array('anonymous' => true));

            // Add the anonymous section to the page
            $currentFormPage->addFormSection($anonymousSection->addFormComponent($formComponent));
        }

        return $this;
    }

    function addFormComponentArray($formComponentArray) {
        foreach($formComponentArray as $formComponent) {
            $this->addFormComponent($formComponent);
        }
        return $this;
    }

    function addFormSectionArray($formSectionArray) {
        foreach($formSectionArray as $formSection) {
            $this->addFormSection($formSection);
        }

        return $this;
    }

    // Convenience method, no need to create a to get a section on the form
    function addFormSection($formSection) {
        // Create an anonymous page if necessary
        if(empty($this->formPageArray)) {
            $this->addFormPage(new FormPage($this->id.'-page1', array('anonymous' => true)));
        }

        // Get the first page in the formPageArray
        $currentFormPage = current($this->formPageArray);

        // Add the section to the first page
        $currentFormPage->addFormSection($formSection);

        return $this;
    }

    public static function updateProcessingText($formId, $processingText) {
        Router::output('
            <script type="text/javascript" language="javascript">
                parent.'.$formId.'Object.updateProcessingText(\''.String::addSlashes($processingText).'\');
            </script>
        ');
    }

    function setStatus($status, $response) {
        $this->status = array('status' => $status, 'response' => $response);
        return $this->status;
    }

    function resetStatus() {
        $this->status = array('status' => 'processing', 'response' => 'Form status reset.');
        return $this->status;
    }

    function getStatus() {
        return $this->status;
    }
   
    function validate() {
        // Update the form status
        $this->setStatus('processing', 'Validating component values.');

        // Clear the validation response
        $this->validationResponse = array();

        // Validate each page
        foreach($this->formPageArray as $formPage) {
            $this->validationResponse[$formPage->id] = $formPage->validate();
        }
        // Walk through all of the pages to see if there are any errors
        $this->validationPassed = true;

        foreach($this->validationResponse as $formPageKey => $formPage) {
            foreach($formPage as $formSectionKey => $formSection) {
                // If there are section instances
                if($formSection != null && array_key_exists(0, $formSection) && is_array($formSection[0])) {
                    foreach($formSection as $formSectionInstanceIndex => $formSectionInstance) {
                        foreach($formSectionInstance as $formComponentKey => $formComponentErrorMessageArray) {
                            // If there are component instances
                            if($formComponentErrorMessageArray != null && array_key_exists(0, $formComponentErrorMessageArray) && is_array($formComponentErrorMessageArray[0])) {
                                foreach($formComponentErrorMessageArray as $formComponentInstanceErrorMessageArray)  {
                                    // If the first value is not empty, the component did not pass validation
                                    if(!empty($formComponentInstanceErrorMessageArray[0]) || sizeof($formComponentInstanceErrorMessageArray) > 1) {
                                        $this->validationPassed = false;
                                    }
                                }
                            }
                            else {
                                if(!empty($formComponentErrorMessageArray)) {
                                    $this->validationPassed = false;
                                }
                            }
                        }
                    }
                }
                // No section instances
                else {
                    foreach($formSection as $formComponentErrorMessageArray) {
                        // Component instances
                        if($formComponentErrorMessageArray != null && array_key_exists(0, $formComponentErrorMessageArray) && is_array($formComponentErrorMessageArray[0])) {
                            foreach($formComponentErrorMessageArray as $formComponentInstanceErrorMessageArray)  {
                                // If the first value is not empty, the component did not pass validation
                                if(!empty($formComponentInstanceErrorMessageArray[0]) || sizeof($formComponentInstanceErrorMessageArray) > 1) {
                                    $this->validationPassed = false;
                                }
                            }
                        }
                        else {
                            if(!empty($formComponentErrorMessageArray)) {
                                $this->validationPassed = false;
                            }
                        }
                    }
                }
            }
        }

        // Update the form status
        $this->setStatus('processing', 'Validation complete.');

        return $this->validationResponse;
    }


    function getData() {
        $this->data = array();

        foreach($this->formPageArray as $formPageKey => $formPage) {
            if(!$formPage->anonymous) {
                $this->data[$formPageKey] = $formPage->getData();
            }
            else {
                foreach($formPage->formSectionArray as $formSectionKey => $formSection) {
                    if(!$formSection->anonymous) {
                        $this->data[$formSectionKey] = $formSection->getData();
                    }
                    else {
                        foreach($formSection->formComponentArray as $formComponentKey => $formComponent) {
                            if(get_class($formComponent) != 'FormComponentHtml') { // Don't include HTML components
                                $this->data[$formComponentKey] = $formComponent->getValue();
                            }
                        }
                    }
                }
            }
        }
        return json_decode(json_encode($this->data));
    }

    function setInitialValues($formValues) {
        // Make sure we are always working with an object
        if(!Object::is($formValues)) {
            $formValues = json_decode(urldecode($formValues));
            if(!Object::is($formValues)) {
                $formValues = json_decode(urldecode(stripslashes($data)));
            }
        }

        // Walk through the form object and apply initial values
        foreach($formValues as $formPageKey => $formPageData) {
            $this->formPageArray[$formPageKey]->setInitialValues($formPageData);
        }
    }
    
    public static function getFormComponentLocation($formComponentId) {
        preg_match('/(-section([0-9])+)?(-instance([0-9])+)?:([A-Za-z0-9_-]+):([A-Za-z0-9_-]+)/', $formComponentId, $fileIdInfo);
        //print_r($fileIdInfo);

        $formComponentId = str_replace($fileIdInfo[0], '', $formComponentId);
        $formPageId = $fileIdInfo[5];
        $formSectionId = $fileIdInfo[6];
        
        return array(
            'formComponentId' => $formComponentId,
            'formPageId' => $formPageId,
            'formSectionId' => $formSectionId,
        );
    }

    function setData($data, $fileArray = array()) {
        //print_r($data);

        // Get the form data as an object, handle apache auto-add slashes on post requests
        $formData = json_decode(urldecode($data));
        if(!is_object($formData)) {
            $formData = json_decode(urldecode(stripslashes($data)));
        }

        //print_r($formData);

        // Clear all of the component values
        $this->clearData();

        //print_r($formData);
        //print_r($fileArray);

        // Update the form status
        $this->setStatus('processing', 'Setting component values.');

        // Assign all of the received JSON values to the form
        foreach($formData as $formPageKey => $formPageData) {
            $this->formPageArray[$formPageKey]->setData($formPageData);
        }
       
        // Handle files
        if(!empty($fileArray)) {
            print_r($_FILES);
            
            foreach($fileArray as $formComponentId => $fileDataArray) {
                $formComponentLocation = self::getFormComponentLocation($formComponentId);
                $formComponentId = $formComponentLocation['formComponentId'];
                $formPageId = $formComponentLocation['formPageId'];
                $formSectionId = $formComponentLocation['formSectionId'];

                //echo 'Form component ID: '.$formComponentId."<br />\n";
                //echo 'Form page ID: '.$formPageId."<br />\n";
                //echo 'Form section ID: '.$formSectionId."<br />\n";

                // Inside section instances
                if($fileIdInfo[1] != null || ($fileIdInfo[1] == null && !empty($this->formPageArray[$formPageId]->formSectionArray[$formSectionId]->formComponentArray) && array_key_exists(0, $this->formPageArray[$formPageId]->formSectionArray[$formSectionId]->formComponentArray))) {
                    //echo 'Section instances';

                    // section instance
                    // set the instance index
                    if($fileIdInfo[1] != null) {
                        $formSectionInstanceIndex = $fileIdInfo[2] - 1;
                    }
                    else {
                        // prime instance
                        $formSectionInstanceIndex = 0;
                    }
                    // check to see if there is a component instance
                    if($fileIdInfo[3] != null || ($fileIdInfo[3] == null && is_array($this->formPageArray[$formPageId]->formSectionArray[$formSectionId]->formComponentArray[$formSectionInstanceIndex][$formComponentId]->value))) {
                        //echo 'Section instances and component instances';

                        // set the component instance index inside of a  section instance
                        if($fileIdInfo[3] == null) {
                            $formComponentInstanceIndex = 0;
                        }
                        else {
                            $formComponentInstanceIndex = $fileIdInfo[4] - 1;
                        }
                        // set the value with a section and a component instance
                        $this->formPageArray[$formPageId]->formSectionArray[$formSectionId]->formComponentArray[$formSectionInstanceIndex][$formComponentId]->value[$formComponentInstanceIndex] = $fileDataArray;
                    }
                    else {
                        //echo 'Section instances and no component instances';

                        // set the value with a section instance
                        $this->formPageArray[$formPageId]->formSectionArray[$formSectionId]->formComponentArray[$formSectionInstanceIndex][$formComponentId]->value = $fileDataArray;
                    }
                }

                // Not section instances
                else {
                    // has component instances
                    if ($fileIdInfo[3] != null || ($fileIdInfo[3]== null && is_array($this->formPageArray[$formPageId]->formSectionArray[$formSectionId]->formComponentArray[$formComponentId]->value))) {
                        //echo 'No section instances, has component instances';

                        // set component  instance index
                        if($fileIdInfo[3] == null) {
                            $formComponentInstanceIndex = 0;
                        }
                        else {
                            $formComponentInstanceIndex = $fileIdInfo[4] - 1;
                        }
                        $this->formPageArray[$formPageId]->formSectionArray[$formSectionId]->formComponentArray[$formComponentId]->value[$formComponentInstanceIndex] = $fileDataArray;
                    }
                    else {
                        // no instances
                        //echo 'No section instances, no component instances';
                        $this->formPageArray[$formPageId]->formSectionArray[$formSectionId]->formComponentArray[$formComponentId]->value = $fileDataArray;
                    }
                }
            }
        }

        return $this;
    }

    function clearData() {
        foreach($this->formPageArray as $formPage) {
            $formPage->clearData();
        }
        $this->data = null;
    }

    function clearAllComponentValues() {
        // Clear all of the components in the form
        foreach($this->formPageArray as $formPage) {
            foreach($formPage->formSectionArray as $formSection) {
                foreach($formSection->formComponentArray as $formComponent) {
                    $formComponent->value = null;
                }
            }
        }
    }

    function select($id) {
        foreach($this->formPageArray as $formPageId => &$formPage) {
            if($id === $formPageId) {
                return $formPage;
            }
            foreach($formPage->formSectionArray as $formSectionId => &$formSection) {
                if($id === $formSectionId) {
                    return $formSection;
                }
                foreach($formSection->formComponentArray as $formComponentId => &$formComponent) {
                    if (is_array($formComponent)) {
                        foreach ($formComponent as $sectionInstanceComponentId => &$sectionInstanceComponent) {
                            if ($id === $sectionInstanceComponentId) {
                                return $sectionInstanceComponent;
                            }
                        }
                    }
                    if($id === $formComponentId) {
                        return $formComponent;
                    }
                }
            }
        }
        return false;
    }

    function remove($id) {
        foreach($this->formPageArray as $formPageId => &$formPage) {
            if($id == $formPageId) {
                $this->formPageArray[$formPageId] = null;
                Arr::filter($this->formPageArray);
                return true;
            }
            foreach($formPage->formSectionArray as $formSectionId => &$formSection) {
                if($id == $formSectionId) {
                    $this->formPageArray[$formPageId]->formSectionArray[$formSectionId] = null;
                    Arr::filter($this->formPageArray[$formPageId]->formSectionArray);
                    return true;
                }
                foreach($formSection->formComponentArray as $formComponentId => &$formComponent) {
                    if($id == $formComponentId) {
                        $this->formPageArray[$formPageId]->formSectionArray[$formSectionId]->formComponentArray[$formComponentId] = null;
                        Arr::filter($this->formPageArray[$formPageId]->formSectionArray[$formSectionId]->formComponentArray);
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    function processFormComponentFile($formComponentId, $fileName, $controller, $function) {
        // Handle instances (just get the base component ID)
        if(String::contains('-section', $formComponentId)) {
            $formComponentId = String::sub($formComponentId, 0, String::indexOf('-section', $formComponentId));    
        }
        
        $formComponentFile = $this->select($formComponentId);
        if(sizeof($_FILES) == 0){
            $phpInput = fopen('php://input', 'r');
            $temporaryFileName = tempnam(sys_get_temp_dir(), 'xhr-');
            $temporaryFile = fopen($temporaryFileName, 'w');
            $fileSize = stream_copy_to_stream($phpInput, $temporaryFile);
            fclose($phpInput);
            fclose($temporaryFile);

            $formValue = array(
                'name' => $fileName,
                'type' => File::mimeType($temporaryFileName),
                'tmp_name' => $temporaryFileName,
                'size' => File::size($temporaryFileName),
            );
        } else {
            $formValue = $_FILES[$formComponentId];
        }
        
        
        $formComponentFile->setValue($formValue);
        
        $validateFormComponentFile = $formComponentFile->validate();
        
        $response = array();
        
        // If validation fails
        if(!empty($validateFormComponentFile)) {
            $response['status'] = 'failure';
            $response['response'] = 'Validation failed.';
            $response['errorMessageArray'] = $validateFormComponentFile;
        }
        // Validation passed
        else {
            /*
             * Possible response key value pairs
             * 
             * fileUrl
             * functionJs
             * 
             */
            
            // Run logic to handle AJAX file, use passed controller and function
            $response = Controller::getControllerView($controller, $formComponentFile->getValue(), $function);
        }
        
        return $response;
    }

    function process($data) {
        $onSubmitErrorMessageArray = array();

        // Set the form components
        $this->setData($data, $_FILES);

        //print_r($this->getData());

        // Run validation
        $this->validate();
        if(!$this->validationPassed) {
            $this->setStatus('failure', array('validationFailed' => $this->validationResponse));
        }
        else {
            try {
                // Require the controller if necessary
                if(!class_exists($this->controller)) {
                    if(String::startsWith('Project:', $this->controller)) {
                        if(File::exists(Project::getProjectPath().'controllers/forms/'.String::replace('Project:', '', $this->controller).'.php')) {
                            include(Project::getProjectPath().'controllers/forms/'.String::replace('Project:', '', $this->controller).'.php');
                        }
                    }
                    else if(String::startsWith('Module:', $this->controller)) {
                        if(File::exists(Project::getProjectPath().'system/modules/'.String::replaceOccurences('\/', '/controllers/', String::replace('Module:', '', $this->controller), 1).'.php')) {
                            include(Project::getProjectPath().'system/modules/'.String::replaceOccurences('\/', '/controllers/', String::replace('Module:', '', $this->controller), 1).'.php');
                        }
                        else if(File::exists(Project::getProjectPath().'system/modules/'.String::replaceOccurences('\/', '/controllers/forms/', String::replace('Module:', '', $this->controller), 1).'.php')) {
                            include(Project::getProjectPath().'system/modules/'.String::replaceOccurences('\/', '/controllers/forms/', String::replace('Module:', '', $this->controller), 1).'.php');
                        }
                    }
                    else {
                        if(File::exists(Project::getInstancePath().'controllers/forms/'.$this->controller.'.php')) {
                            include(Project::getInstancePath().'controllers/forms/'.$this->controller.'.php');
                        }
                    }
                }

                $controllerName = String::explode('/', $this->controller);
                if(String::contains(':', Arr::last($controllerName))) {
                    $controllerName = String::explode(':', Arr::last($controllerName));
                }
                $controllerName = Arr::last($controllerName);

                //echo $controllerName;

                $controller = new $controllerName();
                $onSubmitResponse = call_user_func(array($controller, $this->function), $this->getData());
            }
            catch(Exception $exception) {
                $onSubmitErrorMessageArray[] = $exception->getTraceAsString();
            }

            // Make sure you actually get a callback response
            if(empty($onSubmitResponse)) {
                $onSubmitErrorMessageArray[] = '<p>The function <b>'.$this->onSubmitFunctionServerSide.'</b> did not return a valid response.</p>';
            }

            // If there are no errors, it is a successful response
            if(empty($onSubmitErrorMessageArray)) {
                $this->setStatus('success', $onSubmitResponse);
            }
            else {
                $this->setStatus('failure', array('failureHtml' => $onSubmitErrorMessageArray));
            }
        }

        return '
            <script type="text/javascript" language="javascript">
                parent.'.$this->id.'Object.handleFormSubmissionResponse('.json_encode($this->getStatus()).');
            </script>
        ';
    }

    function processRequest($silent = false) {
        // Are they trying to post a file that is too large?
        if(isset($_SERVER['CONTENT_LENGTH']) && empty($_POST)) {
            $this->setStatus('success', array('failureNoticeHtml' => 'Your request ('.round($_SERVER['CONTENT_LENGTH']/1024/1024, 1).'M) was too large for the server to handle. '.ini_get('post_max_size').' is the maximum request size.'));
            echo '
                <script type="text/javascript" language="javascript">
                    parent.'.$this->id.'Object.handleFormSubmissionResponse('.json_encode($this->getStatus()).');
                </script>
            ';
            exit();
        }

        // Are they trying to post something to the form?
        if(isset($_POST['form']) && $this->id == $_POST['formId'] || isset($_POST['formTask'])) {
            // Process the form, get the form state, or display the form
            if(isset($_POST['form'])) {
                //echo json_encode($_POST);
                $onSubmitErrorMessageArray = array();

                // Set the form components and validate the form
                $this->setData($_POST['form'], $_FILES);

                //print_r($this->getData());

                // Run validation
                $this->validate();
                if(!$this->validationPassed) {
                    $this->setStatus('failure', array('validationFailed' => $this->validationResponse));
                }
                else {
                    try {
                        $onSubmitResponse = call_user_func($this->onSubmitFunctionServerSide, $this->getData());
                    }
                    catch(Exception $exception) {
                        $onSubmitErrorMessageArray[] = $exception->getTraceAsString();
                    }

                    // Make sure you actually get a callback response
                    if(empty($onSubmitResponse)) {
                        $onSubmitErrorMessageArray[] = '<p>The function <b>'.$this->onSubmitFunctionServerSide.'</b> did not return a valid response.</p>';
                    }

                    // If there are no errors, it is a successful response
                    if(empty($onSubmitErrorMessageArray)) {
                        $this->setStatus('success', $onSubmitResponse);
                    }
                    else {
                        $this->setStatus('failure', array('failureHtml' => $onSubmitErrorMessageArray));
                    }
                }

                echo '
                    <script type="text/javascript" language="javascript">
                        parent.'.$this->id.'Object.handleFormSubmissionResponse('.json_encode($this->getStatus()).');
                    </script>
                ';

                //echo json_encode($this->getValues());

                exit();
            }
            // Get the form's status
            else if(isset($_POST['formTask']) && $_POST['formTask'] == 'getFormStatus') {
                $onSubmitResponse = $this->getStatus();
                echo json_encode($onSubmitResponse);
                $this->resetStatus();
                exit();
            }

        }
        // If they aren't trying to post something to the form
        else if(!$silent) {
            $this->outputHtml();
        }
    }

    function getOptions() {
        $options = array();
        $options['options'] = array();
        $options['formPages'] = array();

        // Get all of the pages
        foreach($this->formPageArray as $formPage) {
            $options['formPages'][$formPage->id] = $formPage->getOptions();
        }

        // Set form options
        if(!$this->clientSideValidation) {
            $options['options']['clientSideValidation'] = $this->clientSideValidation;
        }
        if(!$this->validationTips) {
            $options['options']['validationTips'] = $this->validationTips;
        }
        if($this->disableAnalytics) {
            $options['options']['disableAnalytics'] = $this->disableAnalytics;
        }
        if(!$this->setupPageScroller) {
            $options['options']['setupPageScroller'] = $this->setupPageScroller;
        }
        if($this->animationOptions !== null) {
            $options['options']['animationOptions'] = $this->animationOptions;
        }
        if($this->pageNavigatorEnabled) {
            $options['options']['pageNavigator'] = $this->pageNavigator;
        }
        if($this->startingPageId) {
            $options['options']['startingPageId'] = $this->startingPageId;
        }
        if($this->splashPageEnabled) {
            $options['options']['splashPage'] = $this->splashPage;
            //print_r($options['options']['splashPage']); exit();
            unset($options['options']['splashPage']['content']);
        }
        if(!empty($this->onSubmitStartClientSide)) {
            $options['options']['onSubmitStart'] = $this->onSubmitStartClientSide;
        }
        if(!empty($this->onSubmitFinishClientSide)) {
            $options['options']['onSubmitFinish'] = $this->onSubmitFinishClientSide;
        }
        if(!$this->alertsEnabled) {
            $options['options']['alertsEnabled'] = false;
        }
        if($this->submitButtonText != 'Submit') {
            $options['options']['submitButtonText'] = $this->submitButtonText;
        }
        if($this->previousButtonText != 'Previous') {
            $options['options']['previousButtonText'] = $this->previousButtonText;
        }
        if($this->submitProcessingButtonText != 'Processing...') {
            $options['options']['submitProcessingButtonText'] = $this->submitProcessingButtonText;
        }
        if($this->progressBar) {
            $options['options']['progressBar'] = $this->progressBar;
        }
        
        if(empty($options['options'])) {
            unset($options['options']);
        }

        return $options;
    }

    function outputHtml() {
        echo $this->getHtml();
    }

    function __toString() {
        return $this->getHtml()->__toString();
    }

    function toString() {
        return $this->__toString();
    }

    function getHtml() {
        // Create the form
        $formHtmlElement = new HtmlElement('form', array(
            'id' => $this->id,
            'target' => $this->id.'-iframe',
            'enctype' => 'multipart/form-data',
            'method' => 'post',
            'class' => $this->class,
            'action' => $this->action,
        ));
        
        if(!empty($this->onMouseOver)) {
            $formHtmlElement->attr('onmouseover', $this->onMouseOver);
        }
        
        if(!empty($this->onMouseOut)) {
            $formHtmlElement->attr('onmouseout', $this->onMouseOut);
        }
        
        // Set the style
        if(!empty($this->style)) {
            $formHtmlElement->attr('style', $this->style, true);
        }

        // Global messages
        if($this->alertsEnabled) {
            $formAlertWrapperDiv = new HtmlElement('div', array(
                'class' => 'formAlertWrapper',
                'style' => 'display: none;',
            ));
            $alertDiv = new HtmlElement('div', array(
                'class' => 'formAlert',
            ));
            $formAlertWrapperDiv->append($alertDiv);
            $formHtmlElement->append($formAlertWrapperDiv);
        }

        // If a splash is enabled
        if($this->splashPageEnabled) {
            // Create a splash page div
            $splashPageDiv = new HtmlElement('div', array(
                'id' => $this->id.'-splash-page',
                'class' => 'formSplashPage formPage',
            ));

            // Set defaults if they aren't set
            if(!isset($this->splashPage['content'])) {
                $this->splashPage['content'] = '';
            }
            if(!isset($this->splashPage['splashButtonText'])) {
                $this->splashPage['splashButtonText'] = 'Begin';
            }

            $splashPageDiv->append('<div class="formSplashPageContent">'.$this->splashPage['content'].'</div>');
            
            // Create a splash button if there is no custom button ID
            if(!isset($this->splashPage['customButtonId'])) {
                $splashLi = new HtmlElement('li', array('class' => 'splashLi'));
                $splashButton = new HtmlElement('button', array('class' => 'splashButton'));
                $splashButton->html($this->splashPage['splashButtonText']);
                $splashLi->append($splashButton);
            }
        }

        // Add a title to the form
        if(!empty($this->title)) {
            $title = new HtmlElement('div', array(
                'class' => $this->titleClass
            ));
            $title->html($this->title);
            $formHtmlElement->append($title);
        }

        // Add a description to the form
        if(!empty($this->description)) {
            $description = new HtmlElement('div', array(
                'class' => $this->descriptionClass
            ));
            $description->html($this->description);
            $formHtmlElement->append($description);
        }

        // Add the page navigator if enabled
        if($this->pageNavigatorEnabled) {
            $pageNavigatorDiv = new HtmlElement('div', array(
                'class' => 'formPageNavigator',
            ));
            if(isset($this->pageNavigator['position']) && $this->pageNavigator['position'] == 'right') {
                $pageNavigatorDiv->attr('class', ' formPageNavigatorRight', true);
            }
            else {
                $pageNavigatorDiv->attr('class', ' formPageNavigatorTop', true);
            }

            $pageNavigatorUl = new HtmlElement('ul', array(
            ));

            $formPageArrayCount = 0;
            foreach($this->formPageArray as $formPageKey => $formPage) {
                $formPageArrayCount++;
                
                $pageNavigatorLabel = new HtmlElement('li', array(
                    'id' => 'navigatePage'.$formPageArrayCount,
                    'class' => 'formPageNavigatorLink',
                ));
                
                // If the page navigator item for this page should be hidden
                if(isset($formPage->pageNavigator['hide']) && $formPage->pageNavigator['hide'] == true) {
                    $pageNavigatorLabel->addClass('formPageNavigatorHide');
                }

                // If the label is numeric
                if(isset($this->pageNavigator['label']) && $this->pageNavigator['label'] == 'numeric') {
                    $pageNavigatorLabelText = 'Page '.$formPageArrayCount;
                }
                else {
                    // Add a link prefix if there is a title
                    if(!empty($formPage->title)) {
                        $pageNavigatorLabelText = '<span class="formNavigatorLinkPrefix">'.$formPageArrayCount.'</span> '.strip_tags($formPage->title);
                    }
                    else {
                        $pageNavigatorLabelText = 'Page '.$formPageArrayCount;
                    }
                }
                $pageNavigatorLabel->html($pageNavigatorLabelText);

                // Make the starting page the active one
                if($this->startingPageId) {
                    if($formPageKey == $this->startingPageId) {
                        $pageNavigatorLabel->attr('class', ' formPageNavigatorLinkUnlocked formPageNavigatorLinkActive', true);
                    }
                    else {
                        $pageNavigatorLabel->attr('class', ' formPageNavigatorLinkLocked', true);
                    }
                }
                // Make the first page active
                else {
                    if($formPageArrayCount != 1) {
                        $pageNavigatorLabel->attr('class', ' formPageNavigatorLinkLocked', true);
                    }
                    else {
                        $pageNavigatorLabel->attr('class', ' formPageNavigatorLinkUnlocked formPageNavigatorLinkActive', true);
                    }    
                }
                

                $pageNavigatorUl->append($pageNavigatorLabel);
            }

            // Add the page navigator ul to the div
            $pageNavigatorDiv->append($pageNavigatorUl);

            // Add the progress bar if it is enabled
            if($this->progressBar) {
                $pageNavigatorDiv->append('<div class="formProgress"><div class="formProgressBar"></div></div>');
            }

            // Hide the progress bar if there is a splash page
            if($this->splashPageEnabled) {
                $pageNavigatorDiv->attr('style', 'display: none;', true);
            }

            $formHtmlElement->append($pageNavigatorDiv);
        }

        // Add the formControl UL
        $formControlUl = new HtmlElement('ul', array(
            'class' => 'formControl',
        ));

        // Create the cancel button
        if($this->cancelButton) {
            $cancelButtonLi = new HtmlElement('li', array('class' => 'cancelLi'));
            $cancelButton = new HtmlElement('button', array('class' => $this->cancelButtonClass));
            $cancelButton->html($this->cancelButtonText);

            if(!empty($this->cancelButtonOnClick)) {
                $cancelButton->attr('onclick', $this->cancelButtonOnClick);
            }

            $cancelButtonLi->append($cancelButton);
        }

        // Create the previous button
        $previousButtonLi = new HtmlElement('li', array('class' => 'previousLi', 'style' => 'display: none;'));
        $previousButtonClass = 'previousButton';
        if(!empty($this->formControlLiButtonClass)) {
            $previousButtonClass = $this->formControlLiButtonClass.' '.$previousButtonClass;
        }
        $previousButton = new HtmlElement('button', array('class' => $previousButtonClass));
        $previousButton->html($this->previousButtonText);
        $previousButtonLi->append($previousButton);

        // Create the next button
        $nextButtonLi = new HtmlElement('li', array('class' => 'nextLi'));
        $nextButtonClass = 'nextButton';
        if(!empty($this->formControlLiButtonClass)) {
            $nextButtonClass = $this->formControlLiButtonClass.' nextButton';
        }
        $nextButton = new HtmlElement('button', array('class' => $nextButtonClass));
        $nextButton->html($this->submitButtonText);
        // Don't show the next button
        if($this->splashPageEnabled) {
            $nextButtonLi->attr('style', 'display: none;');
        }
        $nextButtonLi->append($nextButton);

        // Add a splash page button if it exists
        if(isset($splashLi)) {
            $formControlUl->append($splashLi);
        }

        $formControlUl->append($previousButtonLi);
        
        if($this->cancelButton && $this->cancelButtonLiBeforeNextButtonLi) {
            $formControlUl->append($cancelButtonLi);
            $formControlUl->append($nextButtonLi);
        }
        else if($this->cancelButton) {
            $formControlUl->append($nextButtonLi);
            $formControlUl->append($cancelButtonLi);
        }
        else {
            $formControlUl->append($nextButtonLi);
        }

        // Create the page wrapper and scrollers
        $formPageWrapper = new HtmlElement('div', array('class' => 'formPageWrapper'));
        $formPageScroller = new HtmlElement('div', array('class' => 'formPageScroller'));
        
        // Add a splash page if it exists
        if(isset($splashPageDiv)) {
            $formPageScroller->append($splashPageDiv);
        }

        // Add the form view component
        $this->addFormComponent(new FormComponentHidden($this->id.'-view', $this->view));
        $this->addFormComponent(new FormComponentHidden($this->id.'-viewData', Url::encode(Json::encode($this->viewData))));

        // Add the form pages to the form
        $formPageCount = 0;
        foreach($this->formPageArray as $formPage) {
            // If there is a starting page, hide all pages but the starting page
            if($this->startingPageId) {
                if($formPage->id !== $this->startingPageId) {
                    $formPage->style .= 'display: none;';    
                }
            }
            // Hide everything but the first page   
            else {
                if($formPageCount != 0 || ($formPageCount == 0 && ($this->splashPageEnabled))) {
                    $formPage->style .= 'display: none;';
                }
            }

            $formPageScroller->append($formPage);
            $formPageCount++;
        }

        // Page wrapper wrapper
        $pageWrapperContainer = new HtmlElement('div', array('class' => 'formWrapperContainer'));

        // Insert the page wrapper and the formControl UL to the form
        $formHtmlElement->append($pageWrapperContainer->append($formPageWrapper->append($formPageScroller).$formControlUl));

        // Create a script tag to initialize form JavaScript
        $script = new HtmlElement('script', array(
            'type' => 'text/javascript',
            'language' => 'javascript'
        ));

        // Update the script tag
        $script->html('$(document).ready(function () { '.$this->id.'Object = new Form(\''.$this->id.'\', '.json_encode($this->getOptions()).'); });');
        $formHtmlElement->append($script);

        // Add a hidden iframe to handle the form posts
        $iframe = new HtmlElement('iframe', array(
            'id' => $this->id.'-iframe',
            'name' => $this->id.'-iframe',
            'class' => 'formIFrame',
            'frameborder' => 0,
            'src' => Project::getInstanceAccessPath().'null/',
            //'src' => str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__).'?iframe=true',
        ));
        $formHtmlElement->append($iframe);

        // After control
        if(!empty($this->afterControl)) {
            $subSubmitInstructions = new HtmlElement('div', array('class' => 'formAfterControl'));
            $subSubmitInstructions->html($this->afterControl);
            $formHtmlElement->append($subSubmitInstructions);
        }

        return $formHtmlElement;
    }

    static function formValuesToHtml($formValues) {
        $pagesUl = new HtmlElement('ul', array(
            'class' => 'pages',
        ));

        foreach($formValues as $pageKey => $section) {
            $pageLi = new HtmlElement('li', array(
                'class' => 'page',
                'text' => '<h2 class="pageTitle">'.String::title(String::camelCaseToSpaces($pageKey)).'</h2>',
            ));

            foreach($section as $sectionKey => $sectionValue) {
                $sectionsUl = new HtmlElement('ul', array(
                    'class' => 'sections',
                ));
                $sectionLi = new HtmlElement('li', array(
                    'class' => 'section',
                ));

                // If the sectionValue is an array (instances)
                if(is_array($sectionValue) && array_key_exists(0, $sectionValue)) {
                    $sectionLi->addClass('instance');
                    $sectionLi->append('<h3 class="sectionTitle">'.String::title(String::camelCaseToSpaces($sectionKey)).' instances ('.sizeof($sectionValue).' total)</h3>');
                    foreach($sectionValue as $sectionInstanceIndex => $section) {
                        $sectionLi->append('<h3>('.($sectionInstanceIndex + 1).') '.String::title(String::camelCaseToSpaces($sectionKey)).'</h3>');
                        $sectionLi->append(Form::sectionFormValuesToHtml($section));
                    }
                }
                // Not an array (no instances)
                else {
                    if(!String::startsWith($pageKey.'-section', $sectionKey)) {
                        $sectionLi->append('<h3 class="sectionTitle">'.String::title(String::camelCaseToSpaces($sectionKey)).'</h3>');
                    }
                    $sectionLi->append(Form::sectionFormValuesToHtml($sectionValue));
                }

                $pageLi->append($sectionsUl->append($sectionLi));
            }

            $pagesUl->append($pageLi);
        }

        return $pagesUl;
    }

    static function sectionFormValuesToHtml($sectionFormValues) {
        $componentsUl = new HtmlElement('ul', array(
            'class' => 'components',
        ));

        foreach($sectionFormValues as $componentKey => $componentValue) {
            $componentLi = new HtmlElement('li', array(
                'class' => 'component',
            ));

            // If the component is complex (address, etc) or if it is instanced
            if(is_object($componentValue) || is_array($componentValue)) {
                // If the component value is an array (instances)
                if(is_array($componentValue) && array_key_exists(0, $componentValue)) {
                    $componentLi->append('<h4>'.String::title(String::camelCaseToSpaces($componentKey)).' ('.sizeof($componentValue).' total)</h4>');
                }
                else {
                    $componentLi->append('<h4>'.String::title(String::camelCaseToSpaces($componentKey)).'</h4>');
                }

                $componentValuesUl = new HtmlElement('ul', array(
                    'class' => 'componentValues',
                ));

                foreach($componentValue as $componentValueKey => $componentValueValue) {
                    $componentValueLi = new HtmlElement('li', array(
                        'class' => 'componentValue',
                    ));

                    if(is_int($componentValueKey)) {
                        if(is_object($componentValueValue)) {
                            foreach($componentValueValue as $instanceKey => $instanceValue) {
                                $componentValueLi->append('('.($componentValueKey + 1).') '.String::title(String::camelCaseToSpaces($instanceKey)).': <b>'.$instanceValue.'</b>');
                            }
                        }
                        else {
                            $componentValueLi->append('<b>'.$componentValueValue.'</b>');
                        }
                    }
                    else {
                        $componentValueLi->append(String::title(String::camelCaseToSpaces($componentValueKey)).': <b>'.$componentValueValue.'</b>');
                    }

                    $componentValuesUl->append($componentValueLi);
                }

                $componentLi->append($componentValuesUl);
            }
            else {
                $componentLi->append(String::title(String::camelCaseToSpaces($componentKey)).': <b>'.$componentValue.'</b>');
            }

            $componentsUl->append($componentLi);
        }
        
        return $componentsUl;
    }
}

// Handle any requests that come to this file
if(isset($_GET['iframe'])) {
    echo '';
}
?>