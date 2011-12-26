<?php
// Make this part of the element class
function getNavigation($navigationItems, $trailPieces, $basePath = '', $currentLevel = 0) {
    $navigation = HtmlElement::ul();
    foreach($navigationItems as $navigationItem) {
        if($currentLevel == 0) {
            $class = 'firstLevel';
        }
        else if($currentLevel == 1) {
            $class = 'secondLevel';
        }
        else if($currentLevel == 2) {
            $class = 'thirdLevel';
        }
        else if($currentLevel == 3) {
            $class = 'fourthLevel';
        }

        $navigationLi = HtmlElement::li();
        $navigationDiv = HtmlElement::div(array('class' => 'link '.$class));
        
        if(!isset($navigationItem['subItems']) || !is_array($navigationItem['subItems'])) {
            $navigationA = HtmlElement::a(array('href' => $basePath.$navigationItem['path'], 'text' => $navigationItem['title'], 'class' => $class));
            $navigationDiv->attr('style', 'padding-left: .75em;');

            //echo $trailPieces[0]['path'].' vs. '.$navigationItem['path'].'<br />';
            if(isset($trailPieces[0]) && $navigationItem['path'] == $trailPieces[0]['path']) {
                //echo 'Highlighting '.$navigationItem['title'].'<br />';
                $navigationDiv->attr('class', ' active', true);
            }

            $navigationLi->append($navigationDiv->append($navigationA));
            $navigation->append($navigationLi);
        }
        else {
            $navigationArrow = HtmlElement::div(array('class' => 'sideNavigationArrow', 'onclick' => "$(this).toggleClass('expanded').parent().next('ul').toggle();"));
            $navigationA = HtmlElement::a(array('href' => $basePath.$navigationItem['path'], 'text' => $navigationItem['title'], 'style' => 'display: block;'));

            //echo $trailPieces[0]['path'].' vs. '.$navigationItem['path'].'<br />';
            if(isset($trailPieces[0]) && $navigationItem['path'] == $trailPieces[0]['path']) {
                //echo 'Expanding sub menu for '.$navigationItem['title'].'<br />';
                $navigationArrow->attr('class', ' expanded', true);
                $expandSubMenu = true;

                // Highlight the item if the sub item does not exist
                if(Arr::size($trailPieces) == 1) {
                    //echo 'Highlighting '.$navigationItem['title'].'<br />';
                    $navigationDiv->attr('class', ' active', true);
                }

                Arr::shift($trailPieces);
            }
            else {
                $expandSubMenu = false;
            }

            $subMenu = getNavigation($navigationItem['subItems'], $trailPieces, $basePath, $currentLevel + 1);

            // Hide the path if the path does not contain the link
            if(!$expandSubMenu) {
                $subMenu->attr('style', 'display: none;');
            }
            
            $navigation->append($navigationLi->append($navigationDiv->append($navigationArrow.$navigationA).$subMenu));
        }
    }
    return $navigation;
}
// Remove the first item
Arr::shift($trailPieces);

// Get the navigation
$sideNavigation = getNavigation($navigationItems, $trailPieces, Project::getInstanceAccessPath().'project/');
$sideNavigation->attr('id', 'sideNavigation');
?>