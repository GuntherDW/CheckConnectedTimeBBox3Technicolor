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
    private $cookiejar;

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
     * @return boolean
     */
    public function isSsl()
    {
        return $this->ssl;
    }

    private function getLoginPageURL() {
        return "http".($this->ssl?'s':'')."://".$this->ip.':'.$this->port.'/login.lp';
    }

    public function initSessionKey()
    {
        if(!empty($this->cookiejar)) @unlink($this->cookiejar);
        $this->cookiejar = tempnam("/tmp", "bbox3-cookie");
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
        if(!empty($this->cookiejar)) $this->initSessionKey();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiejar);
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
                else echo 'rn    = '.$rn."\n";
            }
        }

        echo 'realm = '.$realm."\n";
        echo 'nonce = '.$nonce."\n";
        echo 'qop   = '.$qop."\n";
        echo 'uri   = '.$uri."\n";

        curl_close($ch);
        unset($ch);


        $HA1 = md5($this->user.':'.$realm.':'.$this->password);
        $HA2 = md5('GET:'.$uri);
        $hidepwd = md5($HA1.':'.$nonce.':00000001:xyz:'.$qop.':'.$HA2);
    }

    private function decodeVar($line) {
        // $start = strstr($line, '"');
        $lines = explode('"', $line);
        return $lines[1];
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
        if(empty($this->cookiejar)) return;
        @unlink($this->cookiejar);
    }

}