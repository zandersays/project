<?php
$trail = HtmlElement::ul(array('id' => 'trail'));
$trailCount = 0;
Arr::pop($trailPieces);
foreach($trailPieces as $trailPiece) {
    $trailLi = HtmlElement::li(array('text' => HtmlElement::a(array('text' => $trailPiece['title'], 'href' => Project::getInstanceAccessPath().'project/'.$trailPiece['path'])).'&nbsp;&rarr;&nbsp;'));
    $trail->append($trailLi);
    $trailCount++;
}
$trail = $trail.'<h1 class="'.String::titleToCamelCase($pathTitle).'">'.$pathTitle.'</h1>';
?>