<?php

namespace MCyunpeng98\LoginHnsfxy_Grap;

use GuzzleHttp\Client;

class LoginHnsfxyPhone_Grap{
    private $homeUri = 'http://211.70.176.123/wap/';
    private $loginUri = 'index.asp';
    private $studentInfoUri = 'http://211.70.176.123/wap/grxx.asp';
    private $photoUri = 'http://211.70.176.123/dbsdb/tp.asp?xh=';
    private $currentStudentNum = '';
    public $client = null;
    private $cookies = null;
    public function __construct(Client $client = null)
    {
        if ($this->client == NULL){
            $client = new Client([
                'base_uri' => $this->loginUri,
                'timeout' => 5,
            ]);
        }
        $this->client = $client;
    }

    public function LoginHnsfxyPhone($studeentNum, $idCard){
        $reponse = null;
        $this->cookies = new \GuzzleHttp\Cookie\CookieJar;
            $reponse =$this->client->request('post',$this->loginUri,[
                'cookiees' => $this->cookies,
                'form_parms' => [
                    'xh' => $studeentNum,
                    'sfzh' => $idCard,
                ]
            ]);
        $content = mb_convert_encoding($reponse->getBody(), 'UTF-8', 'gbk');
        print_r($content);
        die();
    }
}