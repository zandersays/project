<!--<p><a class="buttonLink add" href="add-a-model/">Add a Model</a></p>-->

<?php
foreach($models as $databaseName => $modelArray) {
    $table = HtmlElement::table(array(
        'style' => 'margin-bottom: 1em;',
    ));
    $thead = new HtmlElement('thead');
    $tbody = new HtmlElement('tbody');
    $tfoot = new HtmlElement('tfoot');
    $headAndFootColumns = '
        <tr>
            <td></td>
            <td>Model</td>
            <td>Last Updated</td>
        </tr>
    ';
    $thead->append($headAndFootColumns);
    $tfoot->append($headAndFootColumns);

    foreach($modelArray as $modelName => $modelMetaArray) {
        $statusColumn = new HtmlElement('td');
        $statusColumn->attr('class', 'statusColumn');
        $statusIcon = HtmlElement::a();
        $projectModelText = '';

        // Check to see if the model is out of date
        if($modelMetaArray['status'] == 'Model File Does Not Exist') {
            $statusIcon->attr('class', 'statusDotRed');
            $statusIcon->attr('title', 'Model File Does Not Exist');
            $projectModelText .= ' <i>(Model File Does Not Exist)</i>';
        }
        else if($modelMetaArray['status'] == 'Model Fields Do Not Match Database') {
            $statusIcon->attr('class', 'statusDotRed');
            $statusIcon->attr('title', 'Model Fields Do Not Match Database');
            $projectModelText .= ' <i>(Model Fields Do Not Match Database)</i>';
        }
        else if($modelMetaArray['status'] == 'Database Table Does Not Exist') {
            $statusIcon->attr('class', 'statusDotRed');
            $statusIcon->attr('title', 'Database Table Does Not Exist');
            $projectModelText .= ' <i>(Database Table Does Not Exist)</i>';
        }
        else if($modelMetaArray['status'] == 'Model Up to Date') {
            $statusIcon->attr('class', 'statusDotGreen');
            $statusIcon->attr('title', 'Model is Up to Date');
        }

        if($modelMetaArray['modificationTime'] == 'Never') {
            $generateModelText = 'Generate Model';
        }
        else if($modelMetaArray['status'] == 'Database Table Does Not Exist') {
            $generateModelText = 'Generate Table';
        }
        else {
            $generateModelText = 'Regenerate Model';
        }

        $statusColumn->append($statusIcon);

        
        // Standard models
        $link = '<a href="view-model/databaseName:'.$databaseName.'/modelName:'.$modelName.'/" title="Table name: '.$modelMetaArray['tableName'].'">'.$modelName.'</a>';

        $tbody->append('
            <tr>
                '.$statusColumn.'
                <td>'.$link.$projectModelText.'</td>
                <td>'.$modelMetaArray['modificationTime'].'</td>
            </tr>
        ');
        //<td><a href="regenerate-models/modelName:'.$modelName.'/">'.$generateModelText.'</a></td>
    }
    
    $table->append($thead.$tbody.$tfoot);

    echo '<h2>Database: '.$databaseName.'</h2>';
    echo '<p><a class="buttonLink plusDotGreen" href="generate-model-files/databaseName:'.$databaseName.'/">Generate All Model Files for '.$databaseName.'</a></p>';
    echo $table;
}
?>