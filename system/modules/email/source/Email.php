<?php
class Email {

    // Static defaults
    public static $defaultMailType = 'text'; // text/html  Defines email formatting
    public static $defaultCharacterSet = 'utf-8'; // Default char set: iso-8859-1 or us-ascii
    public static $defaultUserAgent = 'Project';
    public static $defaultSendMailPath = '/usr/sbin/sendmail'; // Sendmail path
    public static $defaultProtocol = 'mail'; // mail/sendmail/smtp
    public static $defaultSmtpHost = '';  // SMTP Server.  Example: mail.earthlink.net
    public static $defaultSmtpUsername = '';  // SMTP Username
    public static $defaultSmtpPassword = '';  // SMTP Password
    public static $defaultSmtpPort = '25';  // SMTP Port
    public static $defaultSmtpTimeout = 5;  // SMTP Timeout in seconds
    public static $defaultWordWrap = true;  // TRUE/FALSE  Turns word-wrap on/off
    public static $defaultWordWrapCharacters = 76;  // Number of characters to wrap at.

    private $mailType;
    private $characterSet;
    private $userAgent;
    private $sendMailPath;
    private $protocol;
    private $smtpHost;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpPort;
    private $smtpTimeout;
    private $wordWrap;
    private $wordWrapCharacters;

    private $multipart = 'mixed'; // "mixed" (in the body) or "related" (separate)
    private $alternativeMessage = '';  // Alternative message for HTML emails
    private $validate = false; // TRUE/FALSE.  Enables email validation
    private $priority = '3';  // Default priority (1 - 5)
    private $newLine = "\r\n";  // (NEEDED FOR GOOGLE APPS) Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)
    private $crlf = "\r\n";  // (NEEDED FOR GOOGLE APPS) The RFC 2045 compliant CRLF for quoted-printable is "\r\n".  Apparently some servers,
    // even on the receiving end think they need to muck with CRLFs, so using "\n", while
    // distasteful, is the only thing that seems to work for all environments.
    private $sendMultipart = true;  // TRUE/FALSE - Yahoo does not like multipart alternative, so this is an override.  Set to FALSE for Yahoo.
    private $bccBatchMode = false; // TRUE/FALSE  Turns on/off Bcc batch feature
    private $bccBatchSize = 200;  // If bcc_batch_mode = TRUE, sets max number of Bccs in each batch
    public $safeMode = false;
    private $subject = '';
    private $body = '';
    private $finalBody = '';
    private $alternativeBoundary = '';
    private $atcBoundary = '';
    private $headerString = '';
    private $smtpConnect = false;
    private $encoding = '8bit';
    private $ipAddress = false;
    private $smtpAuthorization = false;
    private $replyToFlag = false;
    private $debugMessage = array();
    private $recipients = array();
    private $ccArray = array();
    private $bccArray = array();
    private $headers = array();
    private $attachFile = array();
    private $attachName = array();
    private $attachType = array();
    private $attachDisposition = array();
    private $protocols = array('mail', 'sendmail', 'smtp');
    private $baseCharacterSets = array('us-ascii', 'iso-2022-'); // 7-bit charsets (excluding language suffix)
    private $bitDepths = array('7bit', '8bit');
    private $priorities = array('1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)');
    public $log = array();

    /**
     * Constructor - Sets Email Preferences
     *
     * The constructor can be passed an array of config values
     */
    function Email($options = array()) {
        // Set defaults based on static variables
        $this->mailType = self::$defaultMailType;
        $this->characterSet = self::$defaultCharacterSet;
        $this->userAgent = self::$defaultUserAgent;
        $this->sendMailPath = self::$defaultSendMailPath;
        $this->protocol = self::$defaultProtocol;
        $this->smtpHost = self::$defaultSmtpHost;
        $this->smtpUsername = self::$defaultSmtpUsername;
        $this->smtpPassword = self::$defaultSmtpPassword;
        $this->smtpPort = self::$defaultSmtpPort;
        $this->smtpTimeout = self::$defaultSmtpTimeout;
        $this->wordWrap = self::$defaultWordWrap;
        $this->wordWrapCharacters = self::$defaultWordWrapCharacters;
        
        $this->initialize($options);
    }

    // --------------------------------------------------------------------

    /**
     * Initialize preferences
     *
     * @access	public
     * @param	array
     * @return	void
     */
    function initialize($options = array()) {
        foreach($options as $key => $value) {
            if(isset($this->$key)) {
                $method = 'set'.String::upperFirstCharacter($key);
                if(method_exists($this, $method)) {
                    $this->$method($value);
                }
                else {
                    $this->$key = $value;
                }
            }
        }

        if(isset($options['smtpUsername'])) {
            $this->smtpUsername($options['smtpUsername']);
        }
        if(isset($options['smtpPassword'])) {
            $this->smtpPassword($options['smtpPassword']);
        }

        $this->smtpAuthorization = ($this->smtpUsername == '' AND $this->smtpPassword == '') ? false : true;
        $this->safeMode = ((boolean) @ini_get("safe_mode") === false) ? false : true;

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Initialize the Email Data
     *
     * @access	public
     * @return	void
     */
    function clear($clearAttachments = false) {
        $this->subject = "";
        $this->body = "";
        $this->finalBody = "";
        $this->headerString = "";
        $this->replyToFlag = false;
        $this->recipients = array();
        $this->headers = array();
        $this->debugMessage = array();

        $this->setHeader('User-Agent', $this->userAgent);
        $this->setHeader('Date', $this->setDate());

        if($clearAttachments !== false) {
            $this->attachFile = array();
            $this->attachName = array();
            $this->attachType = array();
            $this->attachDisposition = array();
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set FROM
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	void
     */
    function from($from, $name = '') {
        if(preg_match('/\<(.*)\>/', $from, $match)) {
            $from = $match['1'];
        }

        if($this->validate) {
            $this->validateEmailAddressArray($this->stringToArray($from));
        }

        // prepare the display name
        if($name != '') {
            // only use Q encoding if there are characters that would require it
            if(!preg_match('/[\200-\377]/', $name)) {
                // add slashes for non-printing characters, slashes, and double quotes, and surround it in double quotes
                $name = '"'.addcslashes($name, "\0..\37\177'\"\\").'"';
            }
            else {
                $name = $this->prepareQEncoding($name, true);
            }
        }

        $this->setHeader('From', $name.' <'.$from.'>');
        $this->setHeader('Return-Path', '<'.$from.'>');
    }

    // --------------------------------------------------------------------

    /**
     * Set Reply-to
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	void
     */
    function replyTo($replyto, $name = '') {
        if(preg_match('/\<(.*)\>/', $replyto, $match)) {
            $replyto = $match['1'];
        }

        if($this->validate) {
            $this->validateEmailAddressArray($this->stringToArray($replyto));
        }

        if($name == '') {
            $name = $replyto;
        }

        if(strncmp($name, '"', 1) != 0) {
            $name = '"'.$name.'"';
        }

        $this->setHeader('Reply-To', $name.' <'.$replyto.'>');
        $this->replyToFlag = true;
    }

    // --------------------------------------------------------------------

    /**
     * Set Recipients
     *
     * @access	public
     * @param	string
     * @return	void
     */
    function to($to) {
        $to = $this->stringToArray($to);
        $to = $this->cleanEmail($to);

        if($this->validate) {
            $this->validateEmailAddressArray($to);
        }

        if($this->getProtocol() != 'mail') {
            $this->setHeader('To', implode(", ", $to));
        }

        switch($this->getProtocol()) {
            case 'smtp' : $this->recipients = $to;
                break;
            case 'sendmail' : $this->recipients = implode(", ", $to);
                break;
            case 'mail' : $this->recipients = implode(", ", $to);
                break;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set CC
     *
     * @access	public
     * @param	string
     * @return	void
     */
    function cc($cc) {
        $cc = $this->stringToArray($cc);
        $cc = $this->cleanEmail($cc);

        if($this->validate) {
            $this->validateEmailAddressArray($cc);
        }

        $this->setHeader('Cc', implode(", ", $cc));

        if($this->getProtocol() == "smtp") {
            $this->ccArray = $cc;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set BCC
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	void
     */
    function bcc($bcc, $limit = '') {
        if($limit != '' && is_numeric($limit)) {
            $this->bccBatchMode = true;
            $this->bccBatchSize = $limit;
        }

        $bcc = $this->stringToArray($bcc);
        $bcc = $this->cleanEmail($bcc);

        if($this->validate) {
            $this->validateEmailAddressArray($bcc);
        }

        if(($this->getProtocol() == "smtp") OR ($this->bccBatchMode && count($bcc) > $this->bccBatchSize)) {
            $this->bccArray = $bcc;
        }
        else {
            $this->setHeader('Bcc', implode(", ", $bcc));
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set Email Subject
     *
     * @access	public
     * @param	string
     * @return	void
     */
    function subject($subject) {
        $subject = $this->prepareQEncoding($subject);
        $this->setHeader('Subject', $subject);
    }

    // --------------------------------------------------------------------

    /**
     * Set Body
     *
     * @access	public
     * @param	string
     * @return	void
     */
    function message($body) {
        $this->body = stripslashes(rtrim(str_replace("\r", "", $body)));
    }

    // --------------------------------------------------------------------

    /**
     * Assign file attachments
     *
     * @access	public
     * @param	string
     * @return	void
     */
    function attach($file, $name = null, $disposition = 'attachment') {
        $this->attachFile[] = $file;
        if($name == null) {
            $this->attachName[] = $file;
        }
        else {
            $this->attachName[] = $name;
        }
        $this->attachType[] = $this->mimeTypes(next(explode('.', basename($name))));
        $this->attachDisposition[] = $disposition; // Can also be 'inline'  Not sure if it matters
    }

    // --------------------------------------------------------------------

    /**
     * Add a Header Item
     *
     * @access	private
     * @param	string
     * @param	string
     * @return	void
     */
    function setHeader($header, $value) {
        $this->headers[$header] = $value;
    }

    // --------------------------------------------------------------------

    /**
     * Convert a String to an Array
     *
     * @access	private
     * @param	string
     * @return	array
     */
    function stringToArray($email) {
        if(!is_array($email)) {
            if(strpos($email, ',') !== false) {
                $email = preg_split('/[\s,]/', $email, -1, PREG_SPLIT_NO_EMPTY);
            }
            else {
                $email = trim($email);
                settype($email, "array");
            }
        }
        return $email;
    }

    // --------------------------------------------------------------------

    /**
     * Set Multipart Value
     *
     * @access	public
     * @param	string
     * @return	void
     */
    function setAlternativeMessage($str = '') {
        $this->alternativeMessage = ($str == '') ? '' : $str;
    }

    // --------------------------------------------------------------------

    /**
     * Set Mailtype
     *
     * @access	public
     * @param	string
     * @return	void
     */
    function setMailType($type = 'text') {
        $this->mailType = ($type == 'html') ? 'html' : 'text';
    }

    // --------------------------------------------------------------------

    /**
     * Set Wordwrap
     *
     * @access	public
     * @param	string
     * @return	void
     */
    function setWordWrap($wordwrap = true) {
        $this->wordWrap = ($wordwrap === false) ? false : true;
    }

    // --------------------------------------------------------------------

    /**
     * Set Protocol
     *
     * @access	public
     * @param	string
     * @return	void
     */
    function setProtocol($protocol = 'mail') {
        $this->protocol = (!in_array($protocol, $this->protocols, true)) ? 'mail' : strtolower($protocol);
    }

    // --------------------------------------------------------------------

    /**
     * Set Priority
     *
     * @access	public
     * @param	integer
     * @return	void
     */
    function setPriority($n = 3) {
        if(!is_numeric($n)) {
            $this->priority = 3;
            return;
        }

        if($n < 1 OR $n > 5) {
            $this->priority = 3;
            return;
        }

        $this->priority = $n;
    }

    // --------------------------------------------------------------------

    /**
     * Set Newline Character
     *
     * @access	public
     * @param	string
     * @return	void
     */
    function setNewLine($newline = "\n") {
        if($newline != "\n" AND $newline != "\r\n" AND $newline != "\r") {
            $this->newLine = "\n";
            return;
        }

        $this->newLine = $newline;
    }

    // --------------------------------------------------------------------

    /**
     * Set CRLF
     *
     * @access	public
     * @param	string
     * @return	void
     */
    function setClrf($crlf = "\n") {
        if($crlf != "\n" AND $crlf != "\r\n" AND $crlf != "\r") {
            $this->crlf = "\n";
            return;
        }

        $this->crlf = $crlf;
    }

    // --------------------------------------------------------------------

    /**
     * Set Message Boundary
     *
     * @access	private
     * @return	void
     */
    function setBoundaries() {
        $this->alternativeBoundary = "B_ALT_".uniqid(''); // multipart/alternative
        $this->atcBoundary = "B_ATC_".uniqid(''); // attachment boundary
    }

    // --------------------------------------------------------------------

    /**
     * Get the Message ID
     *
     * @access	private
     * @return	string
     */
    function getMessageId() {
        $from = $this->headers['Return-Path'];
        $from = str_replace(">", "", $from);
        $from = str_replace("<", "", $from);

        return "<".uniqid('').strstr($from, '@').">";
    }

    // --------------------------------------------------------------------

    /**
     * Get Mail Protocol
     *
     * @access	private
     * @param	bool
     * @return	string
     */
    function getProtocol($return = true) {
        $this->protocol = strtolower($this->protocol);
        $this->protocol = (!in_array($this->protocol, $this->protocols, true)) ? 'mail' : $this->protocol;

        if($return == true) {
            return $this->protocol;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get Mail Encoding
     *
     * @access	private
     * @param	bool
     * @return	string
     */
    function getEncoding($return = true) {
        $this->encoding = (!in_array($this->encoding, $this->bitDepths)) ? '8bit' : $this->encoding;

        foreach($this->baseCharacterSets as $charset) {
            if(strncmp($charset, $this->characterSet, strlen($charset)) == 0) {
                $this->encoding = '7bit';
            }
        }

        if($return == true) {
            return $this->encoding;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get content type (text/html/attachment)
     *
     * @access	private
     * @return	string
     */
    function getContentType() {
        if($this->mailType == 'html' && count($this->attachName) == 0) {
            return 'html';
        }
        elseif($this->mailType == 'html' && count($this->attachName) > 0) {
            return 'html-attach';
        }
        elseif($this->mailType == 'text' && count($this->attachName) > 0) {
            return 'plain-attach';
        }
        else {
            return 'plain';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Set RFC 822 Date
     *
     * @access	private
     * @return	string
     */
    function setDate() {
        $timezone = date("Z");
        $operator = (strncmp($timezone, '-', 1) == 0) ? '-' : '+';
        $timezone = abs($timezone);
        $timezone = floor($timezone / 3600) * 100 + ($timezone % 3600 ) / 60;

        return sprintf("%s %s%04d", date("D, j M Y H:i:s"), $operator, $timezone);
    }

    // --------------------------------------------------------------------

    /**
     * Mime message
     *
     * @access	private
     * @return	string
     */
    function getMimeMessage() {
        return "This is a multi-part message in MIME format.".$this->newLine."Your email application may not support this format.";
    }

    // --------------------------------------------------------------------

    /**
     * Validate Email Address
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    function validateEmailAddressArray($email) {
        if(!is_array($email)) {
            $this->log('email_must_be_array');
            return false;
        }

        foreach($email as $val) {
            if(!$this->validateEmailAddress($val)) {
                $this->log('email_invalid_address', $val);
                return false;
            }
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Email Validation
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    function validateEmailAddress($address) {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? false : true;
    }

    // --------------------------------------------------------------------

    /**
     * Clean Extended Email Address: Joe Smith <joe@smith.com>
     *
     * @access	public
     * @param	string
     * @return	string
     */
    function cleanEmail($email) {
        if(!is_array($email)) {
            if(preg_match('/\<(.*)\>/', $email, $match)) {
                return $match['1'];
            }
            else {
                return $email;
            }
        }

        $clean_email = array();

        foreach($email as $addy) {
            if(preg_match('/\<(.*)\>/', $addy, $match)) {
                $clean_email[] = $match['1'];
            }
            else {
                $clean_email[] = $addy;
            }
        }

        return $clean_email;
    }

    // --------------------------------------------------------------------

    /**
     * Build alternative plain text message
     *
     * This function provides the raw message for use
     * in plain-text headers of HTML-formatted emails.
     * If the user hasn't specified his own alternative message
     * it creates one by stripping the HTML
     *
     * @access	private
     * @return	string
     */
    function getAlternativeMessage() {
        if($this->alternativeMessage != "") {
            return $this->wordWrap($this->alternativeMessage, '76');
        }

        if(preg_match('/\<body.*?\>(.*)\<\/body\>/si', $this->body, $match)) {
            $body = $match['1'];
        }
        else {
            $body = $this->body;
        }

        $body = trim(strip_tags($body));
        $body = preg_replace('#<!--(.*)--\>#', "", $body);
        $body = str_replace("\t", "", $body);

        for($i = 20; $i >= 3; $i--) {
            $n = "";

            for($x = 1; $x <= $i; $x++) {
                $n .= "\n";
            }

            $body = str_replace($n, "\n\n", $body);
        }

        return $this->wordWrap($body, '76');
    }

    // --------------------------------------------------------------------

    /**
     * Word Wrap
     *
     * @access	public
     * @param	string
     * @param	integer
     * @return	string
     */
    function wordWrap($str, $charlim = '') {
        // Se the character limit
        if($charlim == '') {
            $charlim = ($this->wordWrapCharacters == "") ? "76" : $this->wordWrapCharacters;
        }

        // Reduce multiple spaces
        $str = preg_replace("| +|", " ", $str);

        // Standardize newlines
        if(strpos($str, "\r") !== false) {
            $str = str_replace(array("\r\n", "\r"), "\n", $str);
        }

        // If the current word is surrounded by {unwrap} tags we'll
        // strip the entire chunk and replace it with a marker.
        $unwrap = array();
        if(preg_match_all("|(\{unwrap\}.+?\{/unwrap\})|s", $str, $matches)) {
            for($i = 0; $i < count($matches['0']); $i++) {
                $unwrap[] = $matches['1'][$i];
                $str = str_replace($matches['1'][$i], "{{unwrapped".$i."}}", $str);
            }
        }

        // Use PHP's native function to do the initial wordwrap.
        // We set the cut flag to FALSE so that any individual words that are
        // too long get left alone.  In the next step we'll deal with them.
        $str = wordwrap($str, $charlim, "\n", false);

        // Split the string into individual lines of text and cycle through them
        $output = "";
        foreach(explode("\n", $str) as $line) {
            // Is the line within the allowed character count?
            // If so we'll join it to the output and continue
            if(strlen($line) <= $charlim) {
                $output .= $line.$this->newLine;
                continue;
            }

            $temp = '';
            while((strlen($line)) > $charlim) {
                // If the over-length word is a URL we won't wrap it
                if(preg_match("!\[url.+\]|://|wwww.!", $line)) {
                    break;
                }

                // Trim the word down
                $temp .= substr($line, 0, $charlim - 1);
                $line = substr($line, $charlim - 1);
            }

            // If $temp contains data it means we had to split up an over-length
            // word into smaller chunks so we'll add it back to our current line
            if($temp != '') {
                $output .= $temp.$this->newLine.$line;
            }
            else {
                $output .= $line;
            }

            $output .= $this->newLine;
        }

        // Put our markers back
        if(count($unwrap) > 0) {
            foreach($unwrap as $key => $val) {
                $output = str_replace("{{unwrapped".$key."}}", $val, $output);
            }
        }

        return $output;
    }

    // --------------------------------------------------------------------

    /**
     * Build final headers
     *
     * @access	private
     * @param	string
     * @return	string
     */
    function buildHeaders() {
        $this->setHeader('X-Sender', $this->cleanEmail($this->headers['From']));
        $this->setHeader('X-Mailer', $this->userAgent);
        $this->setHeader('X-Priority', $this->priorities[$this->priority - 1]);
        $this->setHeader('Message-ID', $this->getMessageId());
        $this->setHeader('Mime-Version', '1.0');
    }

    // --------------------------------------------------------------------

    /**
     * Write Headers as a string
     *
     * @access	private
     * @return	void
     */
    function writeHeaders() {
        if($this->protocol == 'mail') {
            $this->subject = $this->headers['Subject'];
            unset($this->headers['Subject']);
        }

        reset($this->headers);
        $this->headerString = "";

        foreach($this->headers as $key => $val) {
            $val = trim($val);

            if($val != "") {
                $this->headerString .= $key.": ".$val.$this->newLine;
            }
        }

        if($this->getProtocol() == 'mail') {
            $this->headerString = rtrim($this->headerString);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Build Final Body and attachments
     *
     * @access	private
     * @return	void
     */
    function buildMessage() {
        if($this->wordWrap === true AND $this->mailType != 'html') {
            $this->body = $this->wordWrap($this->body);
        }

        $this->setBoundaries();
        $this->writeHeaders();

        $hdr = ($this->getProtocol() == 'mail') ? $this->newLine : '';

        switch($this->getContentType()) {
            case 'plain' :

                $hdr .= "Content-Type: text/plain; charset=".$this->characterSet.$this->newLine;
                $hdr .= "Content-Transfer-Encoding: ".$this->getEncoding();

                if($this->getProtocol() == 'mail') {
                    $this->headerString .= $hdr;
                    $this->finalBody = $this->body;

                    return;
                }

                $hdr .= $this->newLine.$this->newLine.$this->body;

                $this->finalBody = $hdr;
                return;

                break;
            case 'html' :

                if($this->sendMultipart === false) {
                    $hdr .= "Content-Type: text/html; charset=".$this->characterSet.$this->newLine;
                    $hdr .= "Content-Transfer-Encoding: quoted-printable";
                }
                else {
                    $hdr .= "Content-Type: multipart/alternative; boundary=\"".$this->alternativeBoundary."\"".$this->newLine.$this->newLine;
                    $hdr .= $this->getMimeMessage().$this->newLine.$this->newLine;
                    $hdr .= "--".$this->alternativeBoundary.$this->newLine;

                    $hdr .= "Content-Type: text/plain; charset=".$this->characterSet.$this->newLine;
                    $hdr .= "Content-Transfer-Encoding: ".$this->getEncoding().$this->newLine.$this->newLine;
                    $hdr .= $this->getAlternativeMessage().$this->newLine.$this->newLine."--".$this->alternativeBoundary.$this->newLine;

                    $hdr .= "Content-Type: text/html; charset=".$this->characterSet.$this->newLine;
                    $hdr .= "Content-Transfer-Encoding: quoted-printable";
                }

                $this->body = $this->prepareQuotedPrintable($this->body);

                if($this->getProtocol() == 'mail') {
                    $this->headerString .= $hdr;
                    $this->finalBody = $this->body.$this->newLine.$this->newLine;

                    if($this->sendMultipart !== false) {
                        $this->finalBody .= "--".$this->alternativeBoundary."--";
                    }

                    return;
                }

                $hdr .= $this->newLine.$this->newLine;
                $hdr .= $this->body.$this->newLine.$this->newLine;

                if($this->sendMultipart !== false) {
                    $hdr .= "--".$this->alternativeBoundary."--";
                }

                $this->finalBody = $hdr;
                return;

                break;
            case 'plain-attach' :

                $hdr .= "Content-Type: multipart/".$this->multipart."; boundary=\"".$this->atcBoundary."\"".$this->newLine.$this->newLine;
                $hdr .= $this->getMimeMessage().$this->newLine.$this->newLine;
                $hdr .= "--".$this->atcBoundary.$this->newLine;

                $hdr .= "Content-Type: text/plain; charset=".$this->characterSet.$this->newLine;
                $hdr .= "Content-Transfer-Encoding: ".$this->getEncoding();

                if($this->getProtocol() == 'mail') {
                    $this->headerString .= $hdr;

                    $body = $this->body.$this->newLine.$this->newLine;
                }

                $hdr .= $this->newLine.$this->newLine;
                $hdr .= $this->body.$this->newLine.$this->newLine;

                break;
            case 'html-attach' :

                $hdr .= "Content-Type: multipart/".$this->multipart."; boundary=\"".$this->atcBoundary."\"".$this->newLine.$this->newLine;
                $hdr .= $this->getMimeMessage().$this->newLine.$this->newLine;
                $hdr .= "--".$this->atcBoundary.$this->newLine;

                $hdr .= "Content-Type: multipart/alternative; boundary=\"".$this->alternativeBoundary."\"".$this->newLine.$this->newLine;
                $hdr .= "--".$this->alternativeBoundary.$this->newLine;

                $hdr .= "Content-Type: text/plain; charset=".$this->characterSet.$this->newLine;
                $hdr .= "Content-Transfer-Encoding: ".$this->getEncoding().$this->newLine.$this->newLine;
                $hdr .= $this->getAlternativeMessage().$this->newLine.$this->newLine."--".$this->alternativeBoundary.$this->newLine;

                $hdr .= "Content-Type: text/html; charset=".$this->characterSet.$this->newLine;
                $hdr .= "Content-Transfer-Encoding: quoted-printable";

                $this->body = $this->prepareQuotedPrintable($this->body);

                if($this->getProtocol() == 'mail') {
                    $this->headerString .= $hdr;

                    $body = $this->body.$this->newLine.$this->newLine;
                    $body .= "--".$this->alternativeBoundary."--".$this->newLine.$this->newLine;
                }

                $hdr .= $this->newLine.$this->newLine;
                $hdr .= $this->body.$this->newLine.$this->newLine;
                $hdr .= "--".$this->alternativeBoundary."--".$this->newLine.$this->newLine;

                break;
        }

        $attachment = array();

        $z = 0;

        for($i = 0; $i < count($this->attachName); $i++) {
            $file = $this->attachFile[$i];
            $basename = basename($this->attachName[$i]);
            $ctype = $this->attachType[$i];

            if(!file_exists($file)) {
                //echo 'File does not exist!';
                $this->log('email_attachment_missing', $file);
                return false;
            }

            $h = "--".$this->atcBoundary.$this->newLine;
            $h .= "Content-type: ".$ctype."; ";
            $h .= "name=\"".$basename."\"".$this->newLine;
            $h .= "Content-Disposition: ".$this->attachDisposition[$i].";".$this->newLine;
            $h .= "Content-Transfer-Encoding: base64".$this->newLine;

            $attachment[$z++] = $h;
            $fileSize = filesize($file) + 1;

            if(!$fp = fopen($file, 'r')) {
                echo 'Could not read file!';
                $this->log('E-mail attachment unreadable.', $file);
                return false;
            }

            $attachment[$z++] = chunk_split(base64_encode(fread($fp, $fileSize)));
            fclose($fp);
        }

        if($this->getProtocol() == 'mail') {
            $this->finalBody = $body.implode($this->newLine, $attachment).$this->newLine."--".$this->atcBoundary."--";

            return;
        }

        $this->finalBody = $hdr.implode($this->newLine, $attachment).$this->newLine."--".$this->atcBoundary."--";

        return;
    }

    // --------------------------------------------------------------------

    /**
     * Prep Quoted Printable
     *
     * Prepares string for Quoted-Printable Content-Transfer-Encoding
     * Refer to RFC 2045 http://www.ietf.org/rfc/rfc2045.txt
     *
     * @access	private
     * @param	string
     * @param	integer
     * @return	string
     */
    function prepareQuotedPrintable($str, $charlim = '') {
        // Set the character limit
        // Don't allow over 76, as that will make servers and MUAs barf
        // all over quoted-printable data
        if($charlim == '' OR $charlim > '76') {
            $charlim = '76';
        }

        // Reduce multiple spaces
        $str = preg_replace("| +|", " ", $str);

        // kill nulls
        $str = preg_replace('/\x00+/', '', $str);

        // Standardize newlines
        if(strpos($str, "\r") !== false) {
            $str = str_replace(array("\r\n", "\r"), "\n", $str);
        }

        // We are intentionally wrapping so mail servers will encode characters
        // properly and MUAs will behave, so {unwrap} must go!
        $str = str_replace(array('{unwrap}', '{/unwrap}'), '', $str);

        // Break into an array of lines
        $lines = explode("\n", $str);

        $escape = '=';
        $output = '';

        foreach($lines as $line) {
            $length = strlen($line);
            $temp = '';

            // Loop through each character in the line to add soft-wrap
            // characters at the end of a line " =\r\n" and add the newly
            // processed line(s) to the output (see comment on $crlf class property)
            for($i = 0; $i < $length; $i++) {
                // Grab the next character
                $char = substr($line, $i, 1);
                $ascii = ord($char);

                // Convert spaces and tabs but only if it's the end of the line
                if($i == ($length - 1)) {
                    $char = ($ascii == '32' OR $ascii == '9') ? $escape.sprintf('%02s', dechex($ascii)) : $char;
                }

                // encode = signs
                if($ascii == '61') {
                    $char = $escape.strtoupper(sprintf('%02s', dechex($ascii)));  // =3D
                }

                // If we're at the character limit, add the line to the output,
                // reset our temp variable, and keep on chuggin'
                if((strlen($temp) + strlen($char)) >= $charlim) {
                    $output .= $temp.$escape.$this->crlf;
                    $temp = '';
                }

                // Add the character to our temporary line
                $temp .= $char;
            }

            // Add our completed line to the output
            $output .= $temp.$this->crlf;
        }

        // get rid of extra CRLF tacked onto the end
        $output = substr($output, 0, strlen($this->crlf) * -1);

        return $output;
    }

    // --------------------------------------------------------------------

    /**
     * Prep Q Encoding
     *
     * Performs "Q Encoding" on a string for use in email headers.  It's related
     * but not identical to quoted-printable, so it has its own method
     *
     * @access	public
     * @param	str
     * @param	bool	// set to TRUE for processing From: headers
     * @return	str
     */
    function prepareQEncoding($str, $from = false) {
        
        $str = str_replace(" ", "_", trim($str));
        // We need to delete "=\r\n" produced by imap_8bit() and replace '?'
        $str = str_replace("?", "=3F", str_replace("=\r\n", "", quoted_printable_encode($str)));

        // Now we split by \r\n - i'm not sure about how many chars (header name counts or not?)
        $str = chunk_split($str, 73);
        // We also have to remove last unneeded \r\n :
        $str = substr($str, 0, strlen($str) - 2);
        // replace newlines with encoding text "=?UTF ..."
        $str = str_replace("\r\n", "?=  =?" . $this->characterSet  . "?Q?", $str);
        
        return '=?' . $this->characterSet . '?Q?' . $str . '?=';
     
    }

    // --------------------------------------------------------------------

    /**
     * Send Email
     *
     * @access	public
     * @return	bool
     */
    function send() {
        if($this->replyToFlag == false) {
            $this->replyTo($this->headers['From']);
        }

        if((!isset($this->recipients) AND !isset($this->headers['To'])) AND
                (!isset($this->bccArray) AND !isset($this->headers['Bcc'])) AND
                (!isset($this->headers['Cc']))) {
            $this->log('email_no_recipients');
            return false;
        }

        $this->buildHeaders();

        if($this->bccBatchMode AND count($this->bccArray) > 0) {
            if(count($this->bccArray) > $this->bccBatchSize)
                return $this->batchBccSend();
        }

        $this->buildMessage();

        if(!$this->spoolEmail()) {
            return false;
        }
        else {
            return true;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Batch Bcc Send.  Sends groups of BCCs in batches
     *
     * @access	public
     * @return	bool
     */
    function batchBccSend() {
        $float = $this->bccBatchSize - 1;

        $set = "";

        $chunk = array();

        for($i = 0; $i < count($this->bccArray); $i++) {
            if(isset($this->bccArray[$i])) {
                $set .= ", ".$this->bccArray[$i];
            }

            if($i == $float) {
                $chunk[] = substr($set, 1);
                $float = $float + $this->bccBatchSize;
                $set = "";
            }

            if($i == count($this->bccArray) - 1) {
                $chunk[] = substr($set, 1);
            }
        }

        for($i = 0; $i < count($chunk); $i++) {
            unset($this->headers['Bcc']);
            unset($bcc);

            $bcc = $this->stringToArray($chunk[$i]);
            $bcc = $this->cleanEmail($bcc);

            if($this->protocol != 'smtp') {
                $this->setHeader('Bcc', implode(", ", $bcc));
            }
            else {
                $this->bccArray = $bcc;
            }

            $this->buildMessage();
            $this->spoolEmail();
        }
    }

    // --------------------------------------------------------------------

    /**
     * Unwrap special elements
     *
     * @access	private
     * @return	void
     */
    function unwrapSpecialElements() {
        $this->finalBody = preg_replace_callback("/\{unwrap\}(.*?)\{\/unwrap\}/si", array($this, 'removeLineBreakCallback'), $this->finalBody);
    }

    // --------------------------------------------------------------------

    /**
     * Strip line-breaks via callback
     *
     * @access	private
     * @return	string
     */
    function removeLineBreakCallback($matches) {
        if(strpos($matches[1], "\r") !== false OR strpos($matches[1], "\n") !== false) {
            $matches[1] = str_replace(array("\r\n", "\r", "\n"), '', $matches[1]);
        }

        return $matches[1];
    }

    // --------------------------------------------------------------------

    /**
     * Spool mail to the mail server
     *
     * @access	private
     * @return	bool
     */
    function spoolEmail() {
        $this->unwrapSpecialElements();
        //echo 'Sending!'; exit();

        switch($this->getProtocol()) {
            case 'mail':
                if(!$this->sendWithMail()) {
                    $this->log('email_send_failure_phpmail');
                    return false;
                }
                break;
            case 'sendmail':
                if(!$this->sendWithSendMail()) {
                    $this->log('email_send_failure_sendmail');
                    return false;
                }
                break;
            case 'smtp':
                if(!$this->sendWithSmtp()) {
                    $this->log('smtpSendFailure');
                    return false;
                }
                break;
        }

        $this->log('sent', $this->getProtocol());
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Send using mail()
     *
     * @access	private
     * @return	bool
     */
    function sendWithMail() {
        if($this->safeMode == true) {
            if(!mail($this->recipients, $this->subject, $this->finalBody, $this->headerString)) {
                return false;
            }
            else {
                return true;
            }
        }
        else {
            // most documentation of sendmail using the "-f" flag lacks a space after it, however
            // we've encountered servers that seem to require it to be in place.
            if(!mail($this->recipients, $this->subject, $this->finalBody, $this->headerString, "-f ".$this->cleanEmail($this->headers['From']))) {
                return false;
            }
            else {
                return true;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Send using Sendmail
     *
     * @access	private
     * @return	bool
     */
    function sendWithSendMail() {
        $fp = @popen($this->sendMailPath." -oi -f ".$this->cleanEmail($this->headers['From'])." -t", 'w');

        if($fp === false OR $fp === NULL) {
            // server probably has popen disabled, so nothing we can do to get a verbose error.
            return false;
        }

        fputs($fp, $this->headerString);
        fputs($fp, $this->finalBody);

        $status = pclose($fp);

        if(version_compare(PHP_VERSION, '4.2.3') == -1) {
            $status = $status >> 8 & 0xFF;
        }

        if($status != 0) {
            $this->log('email_exit_status', $status);
            $this->log('email_no_socket');
            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Send using SMTP
     *
     * @access	private
     * @return	bool
     */
    function sendWithSmtp() {
        if($this->smtpHost == '') {
            $this->log('noSmtpHost');
            return false;
        }

        $this->smtpConnect();
        $this->smtpAuthenticate();

        $this->sendCommand('from', $this->cleanEmail($this->headers['From']));

        if(Arr::is($this->recipients)) {
            foreach($this->recipients as $val) {
                $this->sendCommand('to', $val);
            }
        }
        else {
            $this->sendCommand('to', $this->recipients);
        }
        

        if(count($this->ccArray) > 0) {
            foreach($this->ccArray as $val) {
                if($val != "") {
                    $this->sendCommand('to', $val);
                }
            }
        }

        if(count($this->bccArray) > 0) {
            foreach($this->bccArray as $val) {
                if($val != "") {
                    $this->sendCommand('to', $val);
                }
            }
        }

        $this->sendCommand('data');

        // perform dot transformation on any lines that begin with a dot
        $this->sendData($this->headerString.preg_replace('/^\./m', '..$1', $this->finalBody));

        $this->sendData('.');

        $reply = $this->getSmtpData();

        $this->log($reply);

        if(strncmp($reply, '250', 3) != 0) {
            $this->log('smtpError', $reply);
            return false;
        }

        $this->sendCommand('quit');
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * SMTP Connect
     *
     * @access	private
     * @param	string
     * @return	string
     */
    function smtpConnect() {
        
        $this->smtpConnect = fsockopen($this->smtpHost,
                        $this->smtpPort,
                        $errno,
                        $errstr,
                        $this->smtpTimeout);

        if(!is_resource($this->smtpConnect)) {
            //echo 'Not connected!'; exit();
            $this->log('smtpError', $errno.' '.$errstr);
            return false;
        }
        else {
            //echo 'Connected!'; exit();
        }

        $this->log($this->getSmtpData());
        return $this->sendCommand('hello');
    }

    // --------------------------------------------------------------------

    /**
     * Send SMTP command
     *
     * @access	private
     * @param	string
     * @param	string
     * @return	string
     */
    function sendCommand($command, $data = '') {
        switch($command) {
            case 'hello' :
                if($this->smtpAuthorization OR $this->getEncoding() == '8bit')
                    $this->sendData('EHLO '.$this->getHostname());
                else
                    $this->sendData('HELO '.$this->getHostname());
                $resp = 250;
                break;
            case 'from' :
                $this->sendData('MAIL FROM:<'.$data.'>');
                $resp = 250;
                break;
            case 'to' :
                $this->sendData('RCPT TO:<'.$data.'>');
                $resp = 250;
                break;
            case 'data' :
                $this->sendData('DATA');
                $resp = 354;
                break;
            case 'quit' :
                $this->sendData('QUIT');
                $resp = 221;
                break;
        }

        $reply = $this->getSmtpData();

        $this->debugMessage[] = "<pre>".$command.": ".$reply."</pre>";

        if(substr($reply, 0, 3) != $resp) {
            $this->log('smtpError', $reply);
            return false;
        }

        if($command == 'quit') {
            fclose($this->smtpConnect);
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     *  SMTP Authenticate
     *
     * @access	private
     * @return	bool
     */
    function smtpAuthenticate() {
        if(!$this->smtpAuthorization) {
            return true;
        }

        if($this->smtpUsername == '' AND $this->smtpPassword == '') {
            $this->log('email_no_smtp_unpw');
            return false;
        }

        $this->sendData('AUTH LOGIN');

        $reply = $this->getSmtpData();

        if(strncmp($reply, '334', 3) != 0) {
            $this->log('email_failed_smtp_login', $reply);
            return false;
        }

        $this->sendData(base64_encode($this->smtpUsername));

        $reply = $this->getSmtpData();

        if(strncmp($reply, '334', 3) != 0) {
            $this->log('email_smtp_auth_un', $reply);
            return false;
        }

        $this->sendData(base64_encode($this->smtpPassword));

        $reply = $this->getSmtpData();

        if(strncmp($reply, '235', 3) != 0) {
            $this->log('email_smtp_auth_pw', $reply);
            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Send SMTP data
     *
     * @access	private
     * @return	bool
     */
    function sendData($data) {
        if(!fwrite($this->smtpConnect, $data.$this->newLine)) {
            $this->log('email_smtp_data_failure', $data);
            return false;
        }
        else {
            return true;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get SMTP data
     *
     * @access	private
     * @return	string
     */
    function getSmtpData() {
        $data = "";

        while($str = fgets($this->smtpConnect, 512)) {
            $data .= $str;

            if(substr($str, 3, 1) == " ") {
                break;
            }
        }

        return $data;
    }

    // --------------------------------------------------------------------

    /**
     * Get Hostname
     *
     * @access	private
     * @return	string
     */
    function getHostname() {
        return (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : 'localhost.localdomain';
    }

    // --------------------------------------------------------------------

    /**
     * Get IP
     *
     * @access	private
     * @return	string
     */
    function getIpAddress() {
        if($this->ipAddress !== false) {
            return $this->ipAddress;
        }

        $cip = (isset($_SERVER['HTTP_CLIENT_IP']) AND $_SERVER['HTTP_CLIENT_IP'] != "") ? $_SERVER['HTTP_CLIENT_IP'] : false;
        $rip = (isset($_SERVER['REMOTE_ADDR']) AND $_SERVER['REMOTE_ADDR'] != "") ? $_SERVER['REMOTE_ADDR'] : false;
        $fip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND $_SERVER['HTTP_X_FORWARDED_FOR'] != "") ? $_SERVER['HTTP_X_FORWARDED_FOR'] : false;

        if($cip && $rip)
            $this->ipAddress = $cip;
        elseif($rip)
            $this->ipAddress = $rip;
        elseif($cip)
            $this->ipAddress = $cip;
        elseif($fip)
            $this->ipAddress = $fip;

        if(strstr($this->ipAddress, ',')) {
            $x = explode(',', $this->ipAddress);
            $this->ipAddress = end($x);
        }

        if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $this->ipAddress)) {
            $this->ipAddress = '0.0.0.0';
        }

        unset($cip);
        unset($rip);
        unset($fip);

        return $this->ipAddress;
    }

    // --------------------------------------------------------------------

    /**
     * Get Debug Message
     *
     * @access	public
     * @return	string
     */
    function printDebugger() {
        $msg = '';

        if(count($this->debugMessage) > 0) {
            foreach($this->debugMessage as $val) {
                $msg .= $val;
            }
        }

        $msg .= "<pre>".$this->headerString."\n".htmlspecialchars($this->subject)."\n".htmlspecialchars($this->finalBody).'</pre>';
        return $msg;
    }

    // --------------------------------------------------------------------

    /**
     * Set Message
     *
     * @access	private
     * @param	string
     * @return	string
     */
    function log($message, $value = '') {
        $this->log[] = array('message' => $message, 'value' => $value);
    }

    function smtpUsername($username) {
        $this->smtpUsername = $username;
        $this->smtpAuthorization = true;
    }

    function smtpPassword($password) {
        $this->smtpPassword = $password;
        $this->smtpAuthorization = true;
    }

    // --------------------------------------------------------------------

    /**
     * Mime Types
     *
     * @access	private
     * @param	string
     * @return	string
     */
    function mimeTypes($ext = "") {
        $mimes = array('hqx' => 'application/mac-binhex40',
            'cpt' => 'application/mac-compactpro',
            'doc' => 'application/msword',
            'bin' => 'application/macbinary',
            'dms' => 'application/octet-stream',
            'lha' => 'application/octet-stream',
            'lzh' => 'application/octet-stream',
            'exe' => 'application/octet-stream',
            'class' => 'application/octet-stream',
            'psd' => 'application/octet-stream',
            'so' => 'application/octet-stream',
            'sea' => 'application/octet-stream',
            'dll' => 'application/octet-stream',
            'oda' => 'application/oda',
            'pdf' => 'application/pdf',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'mif' => 'application/vnd.mif',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'wbxml' => 'application/vnd.wap.wbxml',
            'wmlc' => 'application/vnd.wap.wmlc',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'dxr' => 'application/x-director',
            'dvi' => 'application/x-dvi',
            'gtar' => 'application/x-gtar',
            'php' => 'application/x-httpd-php',
            'php4' => 'application/x-httpd-php',
            'php3' => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'phps' => 'application/x-httpd-php-source',
            'js' => 'application/x-javascript',
            'swf' => 'application/x-shockwave-flash',
            'sit' => 'application/x-stuffit',
            'tar' => 'application/x-tar',
            'tgz' => 'application/x-tar',
            'xhtml' => 'application/xhtml+xml',
            'xht' => 'application/xhtml+xml',
            'zip' => 'application/zip',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mpga' => 'audio/mpeg',
            'mp2' => 'audio/mpeg',
            'mp3' => 'audio/mpeg',
            'aif' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'ram' => 'audio/x-pn-realaudio',
            'rm' => 'audio/x-pn-realaudio',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'ra' => 'audio/x-realaudio',
            'rv' => 'video/vnd.rn-realvideo',
            'wav' => 'audio/x-wav',
            'bmp' => 'image/bmp',
            'gif' => 'image/gif',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'png' => 'image/png',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'css' => 'text/css',
            'html' => 'text/html',
            'htm' => 'text/html',
            'shtml' => 'text/html',
            'txt' => 'text/plain',
            'text' => 'text/plain',
            'log' => 'text/plain',
            'rtx' => 'text/richtext',
            'rtf' => 'text/rtf',
            'xml' => 'text/xml',
            'xsl' => 'text/xml',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie',
            'doc' => 'application/msword',
            'word' => 'application/msword',
            'xl' => 'application/excel',
            'eml' => 'message/rfc822'
        );

        return (!isset($mimes[strtolower($ext)])) ? "application/x-unknown-content-type" : $mimes[strtolower($ext)];
    }

}