<?php
/**
 * GitHubTreePHP
 * @author Alex Duloz ~ @alexduloz
 * MIT license
 */
class Request
{   
    public function get($url, $data = null)
    {
        $opt = $this->setOpt("get", $url, $data);
        $this->curl($opt);
    }
    
    public function post($url, $data = null)
    {
        $opt = $this->setOpt("post", $url, $data);
        $this->curl($opt);
    }
    
    public function patch($url, $data = null)
    {
        $opt = $this->setOpt("patch", $url, $data);
        $this->curl($opt);
    }
    
    public function delete($url, $data = null)
    {
        $opt = $this->setOpt("delete", $url, $data);
        $this->curl($opt);
    }
    
    public function setOpt($http, $url, $data)
    {
        $opt = array(
            CURLOPT_HEADER => 0,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => "GitHubTree"
        );
        
        if (strtolower($http) === 'get') {
            $opt[CURLOPT_URL] = $url . (strpos($url, '?') === false ? '?' : '') . $this->arrayToQs($data);
        }
        
        if (strtolower($http) === 'post') {
            $opt[CURLOPT_POST]       = 1;
            $opt[CURLOPT_URL]        = $url;
            $opt[CURLOPT_POSTFIELDS] = $data;
        }
        
        if (strtolower($http) === 'delete') {
            $opt[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            $opt[CURLOPT_URL]           = $url;
            $opt[CURLOPT_POSTFIELDS]    = $data;
        }
        
        if (strtolower($http) === 'patch') {
            $opt[CURLOPT_CUSTOMREQUEST] = 'PATCH';
            $opt[CURLOPT_URL]           = $url;
            $opt[CURLOPT_POSTFIELDS]    = $data;
        }
        
        return $opt;
    }
    
    public function curl($opt)
    {
        $ch = curl_init();
        curl_setopt_array($ch, ($opt));
        $this->response = curl_exec($ch);
        $this->code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
    
    public function response()
    {
        return $this->response;
    }
    
    public function code()
    {
        return $this->code;
    }
    
    public function arrayToQs($queryArray)
    {
        if (!$queryArray) {
            return "";
        }
        return http_build_query($queryArray, '_flag', '&');
    }
}