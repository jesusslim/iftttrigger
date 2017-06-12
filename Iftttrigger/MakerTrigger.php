<?php
/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 2017/6/12
 * Time: 下午7:10
 */

namespace Iftttrigger;

/**
 * trigger for
 * Class MakerTrigger
 * @package Iftttrigger
 */
class MakerTrigger
{

    protected $base_url;
    protected $key;
    protected $timeout;

    public function __construct($key)
    {
        $this->base_url = 'maker.ifttt.com';
        $this->key = $key;
        $this->timeout = 10;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * @param string $base_url
     */
    public function setBaseUrl($base_url)
    {
        $this->base_url = $base_url;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param $event
     * @return string
     */
    public function makerAction($event){
        return '/trigger/'.$event.'/with/key/'.$this->key;
    }

    /**
     * @param $event
     * @return string
     */
    public function makerUrl($event){
        return $this->base_url.$this->makerAction($event);
    }

    /**
     * fire the event of ifttt maker
     * @param $event
     * @param array $data
     * @param bool $async
     * @return bool|mixed
     */
    public function fire($event,$data = [],$async = false){
        $url = $this->makerUrl($event);
        $data_string = json_encode($data);
        return $async ? $this->sendFsock($event,$data_string) : $this->sendCurl($url,$data_string);
    }

    /**
     * send by fsockopen
     * @param $event
     * @param $data_string
     * @return bool
     * @throws IftttException
     */
    protected function sendFsock($event,$data_string){
        $fp = fsockopen($this->base_url, 80, $err_no, $err_msg, $this->timeout);
        if (!$fp) {
            throw new IftttException('fsockopen error:'.$err_no.' '.$err_msg);
        } else {
            $out = "POST ".$this->makerAction($event)." HTTP/1.1\r\n";
            $out .= "Host:".$this->base_url."\r\n";
            $out .= "Content-type:application/json\r\n";
            $out .= "Content-length:".strlen($data_string)."\r\n";
            $out .= "Connection:close\r\n\r\n";
            $out .= $data_string;
            fputs($fp, $out);
            usleep(20000); //sleep for nginx,if fclose without sleeping,nginx will return 499
            fclose($fp);
        }
        return true;
    }

    /**
     * send by curl
     * @param $url
     * @param $data_string
     * @return mixed
     * @throws IftttException
     */
    protected function sendCurl($url,$data_string){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) throw new IftttException('curl err:'.$error);
        return $result;
    }
}