<?php
$viewModel = HtmlElement::div();

$title = HtmlElement::h2($modelName.' on '.$databaseName.' (<i>'.$status.'</i>)');
$viewModel->append($title);

$actionsTitle = HtmlElement::h3('Actions');
$viewModel->append($actionsTitle);

$actionsList = HtmlElement::ul(array(
    'class' => 'buttonList',
));
$actionsList->append('<li><a class="buttonLink database" href="/project/modules/models/settings/models/browse-data/databaseName:'.$databaseName.'/fileName:'.$modelName.'/">Browse Data</a></li>');
if($status == 'Model File Does Not Exist') {
    $actionsList->append('<li><a class="buttonLink plusDotGreen" href="/project/modules/models/settings/models/generate-model-file/databaseName:'.$databaseName.'/modelName:'.$modelName.'/">Generate Model File</a></li>');
}
else {
    $actionsList->append('<li><a class="buttonLink minusSquareGrey" href="/project/modules/models/settings/models/delete-model-file/databaseName:'.$databaseName.'/modelName:'.$modelName.'/">Delete Model File</a></li>');
}
if($status == 'Model Does Not Match Database') {
    $actionsList->append('<li><a class="buttonLink plusDotGreen" href="add-a-model/">Regenerate Class File</a></li>');
}
if($status == 'Model Up to Date') {

}

$viewModel->append($actionsList);

// Outward model relations
$actionsTitle = HtmlElement::h3('Models '.$modelName.' Relates To');
$viewModel->append($actionsTitle);
if(!empty($outwardRelatedModels)) {
    $relatedModelsList = HtmlElement::ul(array(
        'style' => 'margin-left: 1em;',
    ));
    foreach($outwardRelatedModels as $relatedModel) {
        if($relatedModel == $modelName) {
            $relatedModelText = ' (Self Referential)';
        }
        else {
            $relatedModelText = '';
        }
        $relatedModelsList->append('<li><a href="/project/modules/models/settings/models/view-model/databaseName:'.$databaseName.'/modelName:'.$relatedModel.'/">'.$relatedModel.'</a>'.$relatedModelText.'</li>');
    }
    $viewModel->append($relatedModelsList);
}
else {
    $viewModel->append('<p>'.$modelName.' does not relate to any other models.</p>');
}

// Inward model relations
$actionsTitle = HtmlElement::h3('Models Relating to '.$modelName);
$viewModel->append($actionsTitle);
if(!empty($inwardRelatedModels)) {
    $relatedModelsList = HtmlElement::ul(array(
        'style' => 'margin-left: 1em;',
    ));
    foreach($inwardRelatedModels as $relatedModel) {
        if($relatedModel == $modelName) {
            $relatedModelText = ' (Self Referential)';
        }
        else {
            $relatedModelText = '';
        }
        $relatedModelsList->append('<li><a href="/project/modules/models/settings/models/view-model/databaseName:'.$databaseName.'/modelName:'.$relatedModel.'/">'.$relatedModel.'</a>'.$relatedModelText.'</li>');
    }
    $viewModel->append($relatedModelsList);
}
else {
    $viewModel->append('<p>No models have '.$modelName.' as a foreign key.</p>');
}

$actionsTitle = HtmlElement::h3($modelName.' Meta');
$viewModel->append($actionsTitle);

$table = HtmlElement::tableFromArray($modelSchema);
$viewModel->append($table);

?>