<?php
Arr::printFormatted($externalApis);
?>

<p><a class="buttonLink plusSquareGrey" href="add-an-external-api/">Add an External API</a></p>

<?php
$databaseTable = '
<table>
    <thead>
        <tr>
            <td class="statusColumn"></td>
            <td>Name</td>
            <td>Type</td>
            <td>Host</td>
            <td>Port</td>
            <td>Username</td>
            <td>Model Path</td>
            <td>Global Context</td>
        </tr>
    </thead>
';

$tbody = new HtmlElement('tbody');
if(!empty($databases['databases'])) {
    foreach($databases['databases'] as $databaseIndex => $databaseOptions) {
        $tbody->append('
            <tr>
                <td class="statusColumn"><a class="statusGood" title="Online"></a></td>
                <td><a href="edit-database/databaseIndex:'.$databaseIndex.'/">'.$databaseOptions['name'].'</a></td>
                <td>'.$databaseOptions['type'].'</td>    
                <td>'.$databaseOptions['host'].'</td>
                <td>'.$databaseOptions['port'].'</td>
                <td>'.$databaseOptions['username'].'</td>
                <td>'.$databaseOptions['modelPath'].'</td>
                <td>'.($databaseOptions['globalContext'] ? '<span class="statusDotCheckGreen" style="width: 16px; height: 16px; display: block;"></span>' : '').'</td>
            </tr>
        ');
        //<td><span class="hiddenPassword">*****</span><span class="actualPassword" style="display: none;">'.$databaseOptions->password.'</span></a> <a onclick="$(this).parent().find(\'.actualPassword\').toggle(); $(this).parent().find(\'.hiddenPassword\').toggle(); if($(this).text() == \'(show)\') { $(this).text(\'(hide)\'); } else if($(this).text() == \'(hide)\') { $(this).text(\'(show)\'); }">(show)</a></td>
    }
}
$databaseTable .= $tbody;

$databaseTable .= '
    <tfoot>
        <tr>
            <td class="statusColumn"></td>
            <td>Name</td>
            <td>Type</td>
            <td>Host</td>
            <td>Port</td>
            <td>Username</td>
            <td>Model Path</td>
            <td>Global Context</td>
        </tr>
    </tfoot>
</table>
';

if(empty($databases['databases'])) {
    echo '<h2>No external APIs found.</h2>';
}
else {
    echo $databaseTable;
}
?>