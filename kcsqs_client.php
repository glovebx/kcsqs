<?php
/*
----------------------------------------------------------------------------------------------------------------
HTTP Simple Queue Service - kcsqs client class for PHP v1.0.1

Author: Zhang Yan (http://blog.s135.com), E-mail: net@s135.com
This is free software, and you are welcome to modify and redistribute it under the New BSD License
----------------------------------------------------------------------------------------------------------------
Useage:
<?php
include_once("kcsqs_client.php");
$kcsqs = new kcsqs($kcsqs_host, $kcsqs_port, $kcsqs_auth, $kcsqs_charset);
$result = $kcsqs->put($queue_name, $queue_data); //1. PUT text message into a queue. If PUT successful, return boolean: true. If an error occurs, return boolean: false. If queue full, return text: HTTPSQS_PUT_END
$result = $kcsqs->get($queue_name); //2. GET text message from a queue. Return the queue contents. If an error occurs, return boolean: false. If there is no unread queue message, return text: HTTPSQS_GET_END
$result = $kcsqs->gets($queue_name); //3. GET text message and pos from a queue. Return example: array("pos" => 7, "data" => "text message"). If an error occurs, return boolean: false. If there is no unread queue message, return: array("pos" => 0, "data" => "HTTPSQS_GET_END")
$result = $kcsqs->status($queue_name); //4. View queue status
$result = $kcsqs->status_json($queue_name); //5. View queue status in json. Return example: {"name":"queue_name","maxqueue":5000000,"putpos":130,"putlap":1,"getpos":120,"getlap":1,"unread":10}
$result = $kcsqs->view($queue_name, $queue_pos); //6. View the contents of the specified queue pos (id). Return the contents of the specified queue pos.
$result = $kcsqs->reset($queue_name); //7. Reset the queue. If reset successful, return boolean: true. If an error occurs, return boolean: false
$result = $kcsqs->maxqueue($queue_name, $num); //8. Change the maximum queue length of per-queue. If change the maximum queue length successful, return boolean: true. If  it be cancelled, return boolean: false
$result = $kcsqs->synctime($num); //9. Change the interval to sync updated contents to the disk. If change the interval successful, return boolean: true. If  it be cancelled, return boolean: false
?>
----------------------------------------------------------------------------------------------------------------
*/

class kcsqs
{
        private $kcsqs_host;
        private $kcsqs_port;
        private $kcsqs_auth;
        private $kcsqs_charset;
        
        public function __construct($host='121.199.7.3', $port=1228, $auth='', $charset='utf-8') {
                $this->kcsqs_host = $host;
                $this->kcsqs_port = $port;
                $this->kcsqs_auth = $auth;
                $this->kcsqs_charset = $charset;
                return true;
        }

    public function http_get($query)
    {
        $socket = fsockopen($this->kcsqs_host, $this->kcsqs_port, $errno, $errstr, 5);
        if (!$socket)
        {
            return false;
        }
        $out = "GET ${query} HTTP/1.1\r\n";
        $out .= "Host: ${host}\r\n";
        $out .= "Connection: close\r\n";
        $out .= "\r\n";
        fwrite($socket, $out);
        $line = trim(fgets($socket));
        $header .= $line;
        list($proto, $rcode, $result) = explode(" ", $line);
        $len = -1;
        while (($line = trim(fgets($socket))) != "")
        {
            $header .= $line;
            if (strstr($line, "Content-Length:"))
            {
                list($cl, $len) = explode(" ", $line);
 
            }
            if (strstr($line, "Pos:"))
            {
                list($pos_key, $pos_value) = explode(" ", $line);
            }                   
            if (strstr($line, "Connection: close"))
            {
                $close = true;
            }
        }
        if ($len < 0)
        {
            return false;
        }
        
        $body = fread($socket, $len);
        $fread_times = 0;
        while(strlen($body) < $len){
                $body1 = fread($socket, $len);
                $body .= $body1;
                unset($body1);
                if ($fread_times > 100) {
                        break;
                }
                $fread_times++;
        }
        //if ($close) fclose($socket);
                fclose($socket);
                $result_array["pos"] = (int)$pos_value;
                $result_array["data"] = $body;
        return $result_array;
    }

    public function http_post($query, $body)
    {
        $socket = fsockopen($this->kcsqs_host, $this->kcsqs_port, $errno, $errstr, 1);
        if (!$socket)
        {
            return false;
        }
        $out = "POST ${query} HTTP/1.1\r\n";
        $out .= "Host: ${host}\r\n";
        $out .= "Content-Length: " . strlen($body) . "\r\n";
        $out .= "Connection: close\r\n";
        $out .= "\r\n";
        $out .= $body;
        fwrite($socket, $out);
        $line = trim(fgets($socket));
        $header .= $line;
        list($proto, $rcode, $result) = explode(" ", $line);
        $len = -1;
        while (($line = trim(fgets($socket))) != "")
        {
            $header .= $line;
            if (strstr($line, "Content-Length:"))
            {
                list($cl, $len) = explode(" ", $line);
            }
            if (strstr($line, "Pos:"))
            {
                list($pos_key, $pos_value) = explode(" ", $line);
            }                   
            if (strstr($line, "Connection: close"))
            {
                $close = true;
            }
        }
        if ($len < 0)
        {
            return false;
        }
        $body = @fread($socket, $len);
        //if ($close) fclose($socket);
                fclose($socket);
                $result_array["pos"] = (int)$pos_value;
                $result_array["data"] = $body;
        return $result_array;
    }
        
    public function put($queue_name, $queue_data)
    {
        $result = $this->http_post("/?auth=".$this->kcsqs_auth."&charset=".$this->kcsqs_charset."&name=".$queue_name."&opt=put", $queue_data);
                if ($result["data"] == "HTTPSQS_PUT_OK") {
                        return true;
                } else if ($result["data"] == "HTTPSQS_PUT_END") {
                        return $result["data"];
                }
                return false;
    }
    
    public function get($queue_name)
    {
        $result = $this->http_get("/?auth=".$this->kcsqs_auth."&charset=".$this->kcsqs_charset."&name=".$queue_name."&opt=get");
                if ($result == false || $result["data"] == "HTTPSQS_ERROR" || $result["data"] == false) {
                        return false;
                }
        return $result["data"];
    }
        
    public function gets($queue_name)
    {
        $result = $this->http_get("/?auth=".$this->kcsqs_auth."&charset=".$this->kcsqs_charset."&name=".$queue_name."&opt=get");
                if ($result == false || $result["data"] == "HTTPSQS_ERROR" || $result["data"] == false) {
                        return false;
                }
        return $result;
    }   
        
    public function status($queue_name)
    {
        $result = $this->http_get("/?auth=".$this->kcsqs_auth."&charset=".$this->kcsqs_charset."&name=".$queue_name."&opt=status");
                if ($result == false || $result["data"] == "HTTPSQS_ERROR" || $result["data"] == false) {
                        return false;
                }
        return $result["data"];
    }
        
    public function view($queue_name, $queue_pos)
    {
        $result = $this->http_get("/?auth=".$this->kcsqs_auth."&charset=".$this->kcsqs_charset."&name=".$queue_name."&opt=view&pos=".$pos);
                if ($result == false || $result["data"] == "HTTPSQS_ERROR" || $result["data"] == false) {
                        return false;
                }
        return $result["data"];
    }
        
    public function reset($queue_name)
    {
        $result = $this->http_get("/?auth=".$this->kcsqs_auth."&charset=".$this->kcsqs_charset."&name=".$queue_name."&opt=reset");
                if ($result["data"] == "HTTPSQS_RESET_OK") {
                        return true;
                }
        return false;
    }
        
    public function maxqueue($queue_name, $num)
    {
        $result = $this->http_get("/?auth=".$this->kcsqs_auth."&charset=".$this->kcsqs_charset."&name=".$queue_name."&opt=maxqueue&num=".$num);
                if ($result["data"] == "HTTPSQS_MAXQUEUE_OK") {
                        return true;
                }
        return false;
    }
        
    public function status_json($queue_name)
    {
        $result = $this->http_get("/?auth=".$this->kcsqs_auth."&charset=".$this->kcsqs_charset."&name=".$queue_name."&opt=status_json");
                if ($result == false || $result["data"] == "HTTPSQS_ERROR" || $result["data"] == false) {
                        return false;
                }
        return $result["data"];
    }

    public function synctime($num)
    {
        $result = $this->http_get("/?auth=".$this->kcsqs_auth."&charset=".$this->kcsqs_charset."&name=kcsqs_synctime&opt=synctime&num=".$num);
                if ($result["data"] == "HTTPSQS_SYNCTIME_OK") {
                        return true;
                }
        return false;
    }
}
?>
