<?php

namespace MCyunpeng98\LoginHnsfxy_Grap;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;

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
        if ($client == NULL){
            $client = new Client([
                'base_uri' => $this->homeUri,
                'timeout' => 5,
            ]);
        }
        $this->client = $client;
    }

    /**
     * Step.1 登录手机版的教务处
     * @param $studentNum 学号
     * @param $idCard 身份证号
     */
    public function LoginHnsfxyPhone($studentNum, $idCard){
        $reponse = null;
        $this->cookies = new CookieJar;
        $reponse =$this->client->request('post',$this->loginUri,[
                'cookies' => $this->cookies,
                'form_params' => [
                    'xh' => $studentNum,
                    'sfzh' => $idCard,
                ]
        ]);
    }

    /**
     * Step.2获取学生信息
     * @return mixed|string 返回学生的信息
     */
    public function getStudentInfo(){
        $d = [];
        $reponse = $this->client->get($this->studentInfoUri,[
            RequestOptions::COOKIES => $this->cookies
        ]);
        $content = mb_convert_encoding($reponse->getBody()->getContents(), 'UTF-8', 'gbk');
        if (preg_match('/<IMG SRC="\.\.\/dbsdb\/tp\.asp\?xh=(\d{10})" width="120" height="160">[\s\S]+?<font color=red>(.+)<\/font>[\s\S]+?班级[\s\S]+?<td align="center" width="170" height="22" valign="middle">(.+)<\/td>[\s\S]+?<td align="center" width="150" height="22" valign="middle">(.+)<\/td>[\s\S]+?政治面貌<\/font><\/td>[\s\S]+?<td align="center" width="170" height="22" valign="middle">(.+)<\/td>/'
                                ,$content,$d))
        {
            $info = [
                //学号
                'student_num'   => $d[1],
                //姓名
                'student_name'  => $d[2],
                //二级学院
                'department'    => $d[3],
                //专业+班级
                'student_class' => $d[4],
                //生日
                'birthday'      => $d[5]
            ];
            $this->currentStudentNum = $info['student_num'];
            return $info;
        }
    }

    /**
     * Step.3获取该学生头像
     * @param string $path 头像存储路径
     * @return string 返回照片路径
     */
    public function savePhoto($path =''){
        if ($path == '')
            $path = './src/public/photo/'."$this->currentStudentNum".'.jpg';
            $this->client->get($this->photoUri.$this->currentStudentNum, [
                RequestOptions::SINK=>$path,
                RequestOptions::HTTP_ERRORS => false
            ]);
        return $path;
    }
}