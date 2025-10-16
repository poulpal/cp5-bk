<?php

namespace App\Helpers;

use Coduo\PHPHumanizer\NumberHumanizer;
use Illuminate\Support\Str;

class SmsMelli
{
    public function send($mobile, $text)
    {
        $this->CallAPI($mobile, $text);
    }

    public function getCredit()
    {
        return $this->CallAPIData()['result']['credit'];
    }

    private function CallAPI($mobile, $msg)
    {
        $parameters = array(
            'uname' => 'arcenciel',
            'pass' => 'D8h8hHD',
            'from' => '+985000144411',
            'to' => array($mobile),
            'msg' => $msg,
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, 'http://mysmsapi.ir/class/sms/restful/sendSms_OneToMany.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);
        return json_decode($result, true);
    }

    private function CallAPIData()
    {
        $parameters = array(
            'uname' => 'arcenciel',
            'pass' => '0CB8fceD',
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, 'http://mysmsapi.ir/class/sms/restful/getData.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);
        return json_decode($result, true);
    }
}
