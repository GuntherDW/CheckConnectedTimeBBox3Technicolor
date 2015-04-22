<?php
/**
 * Created by PhpStorm.
 * User: guntherdw
 * Date: 27/03/15
 * Time: 21:37
 */

class bbox3
{
    private $ip = "192.168.1.1", $port = 80, $ssl = false, $user = "user", $password = "";
    private $cookiejar, $connectedSeconds = 0;

    function __construct($ip, $user, $password)
    {
        $this->ip = $ip;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @param boolean $ssl
     */
    public function setSsl($ssl)
    {
        $this->ssl = $ssl;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return int $connectedSeconds
     */
    public function getConnectedSeconds()
    {
        return $this->connectedSeconds;
    }

    /**
     * @return boolean
     */
    public function isSsl()
    {
        return $this->ssl;
    }

    private function getLoginPageURL() {
        return "http".($this->ssl?'s':'')."://".$this->ip.':'.$this->port.'/login.lp';
    }

    private function getMainPageURL() {
        return "http".($this->ssl?'s':'')."://".$this->ip.':'.$this->port.'/';
    }

    private function getDevicesLP() {
        return "http".($this->ssl?'s':'')."://".$this->ip.':'.$this->port.'/network-global.lp';
    }

    public function initSessionKey()
    {
        $this->cookiejar = dirname(__FILE__).DIRECTORY_SEPARATOR.'bbox3-cookie';
        if(file_exists($this->cookiejar)) return; // We already have a session cookie, don't generate a new one.

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiejar);
        curl_setopt($ch, CURLOPT_URL, $this->getLoginPageURL());

        ob_start();
        curl_exec($ch);
        ob_end_clean();

        curl_close($ch);
        unset($ch);
    }

    public function login() {
        if(empty($this->cookiejar)) $this->initSessionKey();
        // see if we still have a valid session!

        else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiejar);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiejar);
            curl_setopt($ch, CURLOPT_URL, $this->getMainPageURL());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);

            $output = curl_exec($ch);

            if (preg_match('/login\.lp/i', $output)) {
                echo 'Our stored session key was invalid, getting a new one!' . "\n";
            } else {
                echo 'Using stored session key!' . "\n";
                return;
            }
            curl_close($ch);

            @unlink($this->cookiejar);
            $this->initSessionKey();
        }
        // end check

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiejar);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiejar);
        curl_setopt($ch, CURLOPT_URL, $this->getLoginPageURL());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        ob_start();
        $output = curl_exec($ch);
        ob_end_clean();

        $rn    = null;
        $realm = null;
        $nonce = null;
        $qop   = null;
        $uri   = null;

        // We need to get a couple javascript values first
        foreach(explode("\n", $output) as $k => $v)
        {
            if($this->startsWith($v, 'var realm = ')) {
                $realm = $this->decodeVar($v);
            } else if ($this->startsWith($v, 'var nonce = ')) {
                $nonce = $this->decodeVar($v);
            } else if ($this->startsWith($v, 'var qop = ')) {
                $qop = $this->decodeVar($v);
            } else if ($this->startsWith($v, 'var uri = ')) {
                $uri = $this->decodeVar($v);
            }

            $rnPosPos = strpos($v, '"rn" value="');

            if($rnPosPos > 0) {
                $tmp = substr($v, $rnPosPos);
                $rnPos = strpos($tmp, 'value="');
                $rn = $this->decodeVar(substr($tmp, $rnPos));
                if($rn == 0) $rn = null;
                // else echo 'rn    = '.$rn."\n";
            }
        }

      /*echo 'realm = '.$realm."\n";
        echo 'nonce = '.$nonce."\n";
        echo 'qop   = '.$qop."\n";
        echo 'uri   = '.$uri."\n";*/

        curl_close($ch);
        unset($ch);


        $HA1 = md5($this->user.':'.$realm.':'.$this->password);
        $HA2 = md5('GET:'.$uri);
        $hidepw = md5($HA1.':'.$nonce.':00000001:xyz:'.$qop.':'.$HA2);

        // To send ->
        // rn
        // hidepw (from $hidepw)
        // user
        // password
        // ok

        $post_fields = array(
            'rn'       => $rn,
            'hidepw'   => $hidepw,
            'user'     => $this->user,
            'password' => $this->password,
            'ok'       => 'Aanmelden'
        );
        $fields_string = "";
        foreach($post_fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiejar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiejar);
        curl_setopt($ch, CURLOPT_URL, $this->getLoginPageURL());
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

        ob_start();
        $output = curl_exec($ch);
        ob_end_clean();

        curl_close($ch);
        unset($ch);
    }

    function getMainPage() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiejar);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiejar);
        curl_setopt($ch, CURLOPT_URL, $this->getMainPageURL());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        ob_start();
        $output = curl_exec($ch);
        ob_end_clean();

        curl_close($ch);
        unset($ch);
    }

    function getDeviceInfo() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiejar);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiejar);
        curl_setopt($ch, CURLOPT_URL, $this->getDevicesLP());
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        ob_start();
        $output = curl_exec($ch);
        ob_end_clean();

        curl_close($ch);
        unset($ch);

        foreach(explode("\n", $output) as $k => $v) {
            $needle = "var dsl_time = ";
            if($this->startsWith($v, $needle)) {
                $this->connectedSeconds = $this->decodeInteger($v);
            }
        }
    }

    private function decodeVar($line) {
        // $start = strstr($line, '"');
        $lines = explode('"', $line);
        return $lines[1];
    }

    private function decodeInteger($line) {
        $lines = explode('=', $line);
        return rtrim(trim($lines[1]), ';');
    }

    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
    function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    public function closeSession() {
        global $deletecookie;
        if(empty($this->cookiejar)) return;
        if($deletecookie) {
            echo 'Removing cookie, warning, this might starve the bbox3 of resources if used extensively!'. "\n";
            @unlink($this->cookiejar);
        }
    }

}