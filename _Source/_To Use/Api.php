<?php
require_once($_SERVER['DOCUMENT_ROOT']."/php/site.php");
require_once($_SERVER['DOCUMENT_ROOT']."/php/api/UserApi.php");
require_once($_SERVER['DOCUMENT_ROOT']."/php/api/ItemSpottingApi.php");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Disable IE caching
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-Type: application/json");

// Get the request variables
if(!empty($_GET)) {
    $request = $_GET;
}
if(!empty($_POST)) {
    // Get both POST and GET variables
    if(isset($request)) {
        $request = array_merge($request, $_POST);
    }
    else {
        $request = $_POST;
    }
}
if(empty($request)) {
    echo json_encode(array('status' => 'failure', 'response' => "No GET or POST variables were passed."));
    exit();
}

// Send the request to the appropriate API
$apiArray = array('user' => 'UserApi', 'itemSpotting' => 'itemSpottingApi');
if(!isset($request['api']) || !array_key_exists($request['api'], $apiArray)) {
    echo json_encode(array('status' => 'failure', 'response' => '\"api\" variable is required.'));
    exit();
}
else {
    $api = new $apiArray[$request['api']];
    if(isset($request['compressOutput']) && $request['compressOutput'] == 'true') {
        $api->compressOutput = true;
    }
    if(isset($request['decompressCommandArguments']) && $request['decompressCommandArguments'] == 'true') {
        $api->decompressCommandArguments = true;
    }

    $api->initialize($request);
}
?>