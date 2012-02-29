<?php
class RoutePolicyStatement {

    // Effect is a required element that indicates whether you want the statement to result in an allow or an explicit deny
    // Must be either 'Allow' or 'Deny'
    public $effect;

    public $message;

    public $conditions;

}
?>