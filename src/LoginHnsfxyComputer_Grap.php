<?php

namespace MCyunpeng98\LoginHnsfxy_Grap;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

class LoginHnsfxyComputer_Grap{
    private $homeUri = 'http://211.70.176.123/';
    private $loginUri = 'default2.aspx';
    private $studentInfoUri = '/xsgrxx.aspx?xh=';
    private $captchaUri = 'http://211.70.176.123/CheckCode.aspx';
    private $captchaInfouri = 'http://www.mq1314.cn:5000/image_to_label';
    private $photoUri = null;
    private $studentNum = null;
    private $studentPassword =null;
    private $studentName = null;
    private $VIEWSTATE= null;
    private $captcha = null;
    public $client = null;

    public function __construct(Client $client = null){
        if ($client == null){
            $this->client = new Client([
                'base_uri' =>$this->homeUri,
                'timeout' => 5,
                'cookies' => true
            ]);
            $this->getViewstate();
            $this->disCaptcha();
        }
    }

    public function LoginHnsfxyComputer($studentNum,$studentPassword){
        $d = [];
        $this->studentNum = $studentNum;
        $this->studentPassword = $studentPassword;
        $reponse = null;
        $reponse = $this->client->post("$this->loginUri",[
            'form_params' => [
                '__VIEWSTATE' => $this->VIEWSTATE,
                'txtUserName' => $this->studentNum,
                'TextBox2' => $this->studentPassword,
                'txtSecretCode' =>$this->captcha,
                'Button1' => '1',
            ]
        ]);
        $content = mb_convert_encoding($reponse->getBody()->getContents(), 'UTF-8', 'gbk');
        if (preg_match('/<span id="Label3">欢迎您：<\/span>/',$content)){
            if (preg_match('/<span id="xhxm">(.+)<\/span><\/em>/',$content,$d)){
                $this->studentName = mb_substr($d[1],0,mb_strlen($d[1])-2);
            }
        }else{
            $this->newLoginHnsfxyComputer();
            die();
        }
    }

    public function studentInfo(){
        $d = [];
        $studentNameUrl = urlencode(mb_convert_encoding($this->studentName,'gb2312','utf-8'));
        try{
            $reponse = $this->client->get($this->studentInfoUri."$this->studentNum".'&xm='."$studentNameUrl".'&gnmkdm=N121501',[
                'allow_redirects'=> [
                    'max'             => 50,
                    'strict'          => false,
                    'referer'         => true,
                    'protocols'       => ['http', 'https'],
                    'track_redirects' => false
                ]
            ]);
        }catch (RequestException $e){
            echo $e->getRequest();
            echo $e->getResponse();
        }

        $content = mb_convert_encoding($reponse->getBody()->getContents(), 'UTF-8', 'gbk');
        echo $content;
        if (preg_match('/<img id="xszp" src="(.+)" alt="照片" align="AbsMiddle" border="0" height="210" width="150" \/>[\s\S]+?<TD><span id="lbl_xb">(.+)<\/span><\/TD>[\s\S]+?<TD><span id="lbl_xy">(.+)<\/span><\/TD>[\s\S]+?<TD><span id="lbl_zymc">(.+)<\/span><\/TD>/',$content,$d)){
            $this->photoUri = $d[1];
            $info = [
                //学生姓名
                'student_name' => $this->studentName,
                //学生学号
                'student_num' =>$this->studentNum,
                //学生性别
                'student_xb' => $d[2],
                //学生学院
                'student_xy' => $d[3],
                //学生专业+班级
                'student_zybj' => $d[4],
                //学生教务处密码
                'student_password' => $this->studentPassword
            ];
            return $info;
        }
    }
    /**
     * 暂时有问题
     */
//    public function savePhoto($path =''){
//        if ($path == '')
//            $path = './src/public/photo/'."$this->studentNum".'.JFIF';
//        $this->client->get($this->homeUri."$this->photoUri", [
//            'allow_redirects'=> [
//                'max'             => 50,
//                'strict'          => false,
//                'referer'         => true,
//                'protocols'       => ['http', 'https'],
//                'track_redirects' => false
//            ],
//            RequestOptions::SINK=>$path,
//            RequestOptions::HTTP_ERRORS => false
//        ]);
//        return $path;
//    }

    private function disCaptcha(){
        $path = './src/public/captcha/captcha.jpg';
        $this->client->get($this->captchaUri,[
            RequestOptions::SINK=>$path,
            RequestOptions::HTTP_ERRORS => false
        ]);

        $captcha = $this->client->post("$this->captchaInfouri",[
            'multipart' => [
                [
                    'name' => 'captcha',
                    'contents' => fopen("$path",'r')
                ],
            ]
        ]);
        $code = json_decode($captcha->getBody());
        $this->captcha = $code->captcha_label;
    }

    private function getViewstate(){
        $d = [];
        $reponse = $this->client->get('/');
        $content = mb_convert_encoding($reponse->getBody()->getContents(), 'UTF-8', 'gbk');
        if (preg_match('/<input type="hidden" name="__VIEWSTATE" value="(.+)" \/>/',$content,$d)){
            $this->VIEWSTATE = $d[1];
        }
    }

    private function newLoginHnsfxyComputer(){
        $this->disCaptcha();
        $this->LoginHnsfxyComputer($this->studentNum,$this->studentPassword);
    }
}