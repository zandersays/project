<?php

$php = '';

$php .= file_get_contents('Form.php');
$php .= file_get_contents('FormPage.php');
$php .= file_get_contents('FormSection.php');
$php .= file_get_contents('FormComponent.php');
$php .= file_get_contents('FormComponentAddress.php');
$php .= file_get_contents('FormComponentCreditCard.php');
$php .= file_get_contents('FormComponentDate.php');
$php .= file_get_contents('FormComponentDropDown.php');
$php .= file_get_contents('FormComponentFile.php');
$php .= file_get_contents('FormComponentHidden.php');
$php .= file_get_contents('FormComponentHtml.php');
$php .= file_get_contents('FormComponentLikert.php');
$php .= file_get_contents('FormComponentMultipleChoice.php');
$php .= file_get_contents('FormComponentName.php');
$php .= file_get_contents('FormComponentSingleLineText.php');
$php .= file_get_contents('FormComponentTextArea.php');

$php = str_ireplace('?>', '', $php);
$php = str_ireplace('<?php', '', $php);

echo '<?php'.$php.'?>';

?>