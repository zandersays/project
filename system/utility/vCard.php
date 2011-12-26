<?php
class vCard {

    public $properties;
    public $fileName;
    
    // Taken from PHP documentation comments
    function quotedPrintableEncode($input, $line_max = 76) {
        $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
        $lines = preg_split("/(?:\r\n|\r|\n)/", $input);
        $eol = "\r\n";
        $linebreak = "=0D=0A";
        $escape = "=";
        $output = "";

        for($j = 0; $j < count($lines); $j++) {
            $line = $lines[$j];
            $linlen = strlen($line);
            $newline = "";
            for($i = 0; $i < $linlen; $i++) {
                $c = substr($line, $i, 1);
                $dec = ord($c);
                if(($dec == 32) && ($i == ($linlen - 1))) { // convert space at eol only
                    $c = "=20";
                }
                elseif(($dec == 61) || ($dec < 32 ) || ($dec > 126)) { // always encode "\t", which is *not* required
                    $h2 = floor($dec / 16);
                    $h1 = floor($dec % 16);
                    $c = $escape.$hex["$h2"].$hex["$h1"];
                }
                if((strlen($newline) + strlen($c)) >= $line_max) { // CRLF is not counted
                    $output .= $newline.$escape.$eol; // soft line break; " =\r\n" is okay
                    $newline = "    ";
                }
                $newline .= $c;
            } // end of for
            $output .= $newline;
            if($j < count($lines) - 1)
                $output .= $linebreak;
        }
        
        return trim($output);
    }

    function setPhoneNumber($number, $type="") {
        // type may be PREF | WORK | HOME | VOICE | FAX | MSG | CELL | PAGER | BBS | CAR | MODEM | ISDN | VIDEO or any senseful combination, e.g. "PREF;WORK;VOICE"
        $key = "TEL";
        if($type != "")
            $key .= ";".$type;
        $key.= ";ENCODING=QUOTED-PRINTABLE";
        $this->properties[$key] = $this->quotedPrintableEncode($number);
    }

    // UNTESTED !!!
    function setPhoto($type, $base64Data) { // $type = "GIF" | "JPEG"
        //PHOTO;ENCODING=BASE64;TYPE=GIF:
        //R0lGODdhfgA4AOYAAAAAAK+vr62trVIxa6WlpZ+fnzEpCEpzlAha/0Kc74+PjyGM
        //SuecKRhrtX9/fzExORBSjCEYCGtra2NjYyF7nDGE50JrhAg51qWtOTl7vee1MWu1
        //50o5e3PO/3sxcwAx/4R7GBgQOcDAwFoAQt61hJyMGHuUSpRKIf8A/wAY54yMjHtz
        //$this->properties['PHOTO'] = 'VALUE=URL;TYPE='.$type.':'.$url;
        $this->properties['PHOTO'] = 'TYPE='.$type.';ENCODING=BASE64:'."\r\n ";
        $this->properties['PHOTO'] .= chunk_split($base64Data, 72, "\r\n ");
    }

    function setFormattedName($name) {
        $this->properties["FN"] = $this->quotedPrintableEncode($name);
    }

    function setName($family="", $first="", $additional="", $prefix="", $suffix="") {
        $this->properties["N"] = preg_replace('/;+/', ';', "$family;$first;$additional;$prefix;$suffix");
        if(String::endsWith(';', $this->properties['N'])) {
            $this->properties['N'] = String::stripTrailingCharacters(1, $this->properties['N']);
        }
        $this->fileName = $first.' '.$family.'.vcf';
        if(!isset($this->properties["FN"]) || $this->properties["FN"] == "")
            $this->setFormattedName(preg_replace('/\s+/', ' ', trim("$prefix $first $additional $family $suffix")));
    }
    
    function getName() {
        return $this->properties['FN'];
    }

    function setBirthday($date) { // $date format is YYYY-MM-DD
        $this->properties["BDAY"] = $date;
    }

    function setAddress($postoffice="", $extended="", $street="", $city="", $region="", $zip="", $country="", $type="HOME;POSTAL") {
        // $type may be DOM | INTL | POSTAL | PARCEL | HOME | WORK or any combination of these: e.g. "WORK;PARCEL;POSTAL"
        $key = "ADR";
        if($type != "")
            $key.= ";$type";
        $key.= ";ENCODING=QUOTED-PRINTABLE";
        $this->properties[$key] = encode($name).";".encode($extended).";".encode($street).";".encode($city).";".encode($region).";".encode($zip).";".encode($country);

        if($this->properties["LABEL;$type;ENCODING=QUOTED-PRINTABLE"] == "") {
            //$this->setLabel($postoffice, $extended, $street, $city, $region, $zip, $country, $type);
        }
    }

    function setLabel($postoffice="", $extended="", $street="", $city="", $region="", $zip="", $country="", $type="HOME;POSTAL") {
        $label = "";
        if($postoffice != "")
            $label.= "$postoffice\r\n";
        if($extended != "")
            $label.= "$extended\r\n";
        if($street != "")
            $label.= "$street\r\n";
        if($zip != "")
            $label.= "$zip ";
        if($city != "")
            $label.= "$city\r\n";
        if($region != "")
            $label.= "$region\r\n";
        if($country != "")
            $country.= "$country\r\n";

        $this->properties["LABEL;$type;ENCODING=QUOTED-PRINTABLE"] = $this->quotedPrintableEncode($label);
    }

    function setEmail($address) {
        $this->properties["EMAIL;INTERNET"] = $address;
    }

    function setNote($note) {
        $this->properties["NOTE;ENCODING=QUOTED-PRINTABLE"] = $this->quotedPrintableEncode($note);
    }

    function setUrl($url, $type="") {
        // $type may be WORK | HOME
        $key = "URL";
        if($type != "")
            $key.= ";$type";
        $this->properties[$key] = $url;
    }

    function getVCard() {
        $text = "BEGIN:VCARD\r\n";
        $text.= "VERSION:2.1\r\n";
        foreach($this->properties as $key => $value) {
            if($key == 'PHOTO') {
                $text.= $key.';'.$value."\r\n";    
            }
            else {
                $text.= $key.':'.$value."\r\n";
            }            
        }
        $text.= "REV:".date("Y-m-d")."T".date("H:i:s")."Z\r\n";
        $text.= "END:VCARD\r\n";

        return $text;
    }
    
    function download() {
        $output = $this->getVCard();
        header('Content-Disposition: attachment; filename='.$this->getFileName());
        header('Content-Length: '.strlen($output));
        header('Connection: close');
        header('Content-Type: text/x-vCard; name='.$this->getFileName());
        echo $output;
        exit();
    }

    function getFileName() {
        return $this->fileName;
    }
    
    function encode($string) {
        return $this->escapeString($this->quotedPrintableEncode($string));
    }

    function escapeString($string) {
        return str_replace(";", "\;", $string);
    }

}
?>