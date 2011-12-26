<?php

require_once($_SERVER['DOCUMENT_ROOT']."/php/api/Api.php");
require_once($_SERVER['DOCUMENT_ROOT']."/php/packages/geoip/geoipcity.php");

class LocatorApi extends Api {
    var $commands = array(
        'locate' => array('arguments' => array('remoteAddress'), 'requiredArguments' => array('remoteAddress')),
    );

    function locate($remoteAddress) {
        $baseDomain = Network::getBaseDomain($remoteAddress);
        $ipAddress = gethostbyname($remoteAddress);

        if(Network::isValidIpV4Address($ipAddress)) {
            // Pull in region variables
            include($_SERVER['DOCUMENT_ROOT']."/php/packages/geoip/geoipregionvars.php");

            $geoIp = geoip_open($_SERVER['DOCUMENT_ROOT']."/php/packages/geoip/GeoLiteCity.dat", GEOIP_STANDARD);
            $record = geoip_record_by_addr($geoIp, $ipAddress);
            if(!is_object($record)) {
                return array('status' => 'failure', 'response' => 'Could not find location data for "'.$ipAddress.'".');
            }
            $record->ip_address = $ipAddress;
            $record->remote_address = $remoteAddress;
            $record->base_domain = $baseDomain;
            $record->region_name = $GEOIP_REGION_NAME[$record->country_code][$record->region];
            geoip_close($geoIp);

            return array('status' => 'success', 'response' => $record);
        }
        else {
            return array('status' => 'failure', 'response' => '"'.$remoteAddress.'" is an invalid remote address.');
        }
    }
}

?>