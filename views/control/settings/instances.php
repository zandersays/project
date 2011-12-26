<p><a class="buttonLink plusSquareGrey" href="add-an-instance/">Add an Instance</a></p>

<?php
$projectInstancesTBody = new HtmlElement('tbody');
foreach($instances as $instanceIndex => $instanceOptions) {
    // Identify the active instance
    if($instanceOptions['host'] == $_SERVER['HTTP_HOST'] && Dir::exists($instanceOptions['path'])) {
        $instanceStatus = '<a class="statusDotBlue project" title="Current Instance"></a>';
    }
    else {
        $instanceStatus = '';
    }

    $projectInstancesTBody->append('
        <tr>
            <td class="statusColumn">'.$instanceStatus.'</td>
            <!--<td>'.$instanceIndex.'</td>-->
            <td><a href="edit-instance/instanceId:'.$instanceOptions['id'].'/">'.$instanceOptions['id'].'</a></td>
            <td>'.$instanceOptions['type'].'</td>
            <td>'.$instanceOptions['host'].'</td>
            <td>'.$instanceOptions['accessPath'].'</td>
            <!--<td>'.$instanceOptions['path'].'</td>
            <td>'.$instanceOptions['projectPath'].'</td>
            <td>'.date('F j, Y, g:i a', $instanceOptions['setupTime']).'</td>-->
            <td></td>
        </tr>
    ');
}
?>

<h2 style="margin-top: 1em;">Instances</h2>
<table>
    <thead>
        <tr>
            <td class="statusColumn"></td>
            <!--<td>Index</td>-->
            <td>ID</td>
            <td>Type</td>
            <td>Host</td>
            <td>Access Path</td>
            <!--<td>Path</td>
            <td>Project Path</td>
            <td>Setup Time</td>-->
            <td>Databases</td>
        </tr>
    </thead>
    <?php echo $projectInstancesTBody; ?>
    <tfoot>
        <tr>
            <td class="statusColumn"></td>
            <!--<td>#</td>-->
            <td>ID</td>
            <td>Type</td>
            <td>Host</td>
            <td>Access Path</td>
            <!--<td>Path</td>
            <td>Project Path</td>
            <td>Setup Time</td>-->
            <td>Databases</td>
        </tr>
    </tfoot>
</table>