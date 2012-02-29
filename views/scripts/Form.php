<?php
header('Content-Type: application/x-javascript');
$fileModificationTime = gmdate('D, d M Y H:i:s', 0).' GMT';
if(!class_exists('Project')) {
    include('../../system/core/Project.php');
}
$headers = Project::getHeaders();
if(isset($headers['If-Modified-Since']) && $headers['If-Modified-Since'] == $fileModificationTime) {
    header('HTTP/1.1 304 Not Modified');
    exit();
}
header('Last-Modified: '.$fileModificationTime);
include('FormDatePicker.js');
include('FormMask.js');
include('FormScroller.js');
include('FormTip.js');
include('Form.js');
include('FormPage.js');
include('FormSection.js');
include('FormComponent.js');
include('FormComponentAddress.js');
include('FormComponentCreditCard.js');
include('FormComponentDate.js');
include('FormComponentDropDown.js');
include('FormComponentFile.js');
include('FormComponentHidden.js');
include('FormComponentLikert.js');
include('FormComponentLikertStatement.js');
include('FormComponentMultipleChoice.js');
include('FormComponentName.js');
include('FormComponentSingleLineText.js');
include('FormComponentTextArea.js');
?>