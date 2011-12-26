<?php
echo '<div>';

echo '<h2>Essential</h2>';
$essentialModulesUl = HtmlElement::ul(array('class' => 'moduleList'));
foreach($essentialModules as $module) {
    $essentialModulesUl->append('
        <li>'.$module['moduleName'].'
        <ul>
            <li><a class="buttonLink refreshVertical" href="reinstall-module/moduleKey:'.$module['moduleKey'].'/">Reinstall</a></li>
            <!--<li><a class="buttonLink cog" href="unit-test-module/moduleKey:'.$module['moduleKey'].'/">Unit Test</a></li>-->
        </ul>
        </li>'
    );
}
echo $essentialModulesUl;

echo '<h2>Active</h2>';
$activeModulesUl = HtmlElement::ul(array('class' => 'moduleList'));
foreach($activeModules as $module) {
    $activeModulesUl->append('
        <li>'.$module['moduleName'].'
        <ul>
            <!--<li><a class="buttonLink cog" href="unit-test-module/moduleKey:'.$module['moduleKey'].'/">Unit Test</a></li>-->
            <li><a class="buttonLink refreshVertical" href="reinstall-module/moduleKey:'.$module['moduleKey'].'/">Reinstall</a></li>
            <li><a class="buttonLink minusDotRed" href="deactivate-module/moduleKey:'.$module['moduleKey'].'/">Deactivate</a></li>
            <li><a class="buttonLink minusSquareGrey" href="uninstall-module/moduleKey:'.$module['moduleKey'].'/">Uninstall</a></li>
            <!--<li><a class="buttonLink minusSquareGrey" href="delete-module/moduleKey:'.$module['moduleKey'].'/">Delete</a></li>-->
        </ul>
        </li>'
    );
}
echo $activeModulesUl;

echo '<h2>Inactive</h2>';
$inactiveModulesUl = HtmlElement::ul(array('class' => 'moduleList'));
foreach($inactiveModules as $module) {
    $inactiveModulesUl->append('
        <li>'.$module['moduleName'].'
        <ul>
            <li><a class="buttonLink plusDotGreen" href="activate-module/moduleKey:'.$module['moduleKey'].'/">Activate</a></li>
            <li><a class="buttonLink refreshVertical" href="reinstall-module/moduleKey:'.$module['moduleKey'].'/">Reinstall</a></li>
            <li><a class="buttonLink minusSquareGrey" href="uninstall-module/moduleKey:'.$module['moduleKey'].'/">Uninstall</a></li>
            <!--<li><a class="buttonLink minusSquareGrey" href="delete-module/moduleKey:'.$module['moduleKey'].'/">Delete</a></li>-->
        </ul>
        </li>'
    );
}
echo $inactiveModulesUl;

echo '<h2>Not Installed</h2>';
$notInstalledModulesUl = HtmlElement::ul(array('class' => 'moduleList'));
foreach($notInstalledModules as $module) {
    $notInstalledModulesUl->append('
        <li>'.$module['moduleName'].'
        <ul>
            <li><a class="buttonLink plusSquareGrey" href="install-module/moduleKey:'.$module['moduleKey'].'/">Install</a></li>
            <!--<li><a class="buttonLink minusSquareGrey" href="delete-module/moduleKey:'.$module['moduleKey'].'/">Delete</a></li>-->
        </ul>
        </li>'
    );
}
echo $notInstalledModulesUl;
echo '</div>';
?>