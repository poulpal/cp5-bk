<?php

namespace App\Helpers;

use Exception;
use SoapClient;

class Avanak
{
    private $username = "09301397979";
    private $password = "gLkKA49w8Crv8nF";

    public function QuickSendWithTTS($mobile, $text)
    {
        try {
            ini_set("soap.wsdl_cache_enabled", "0");

            $service_client = new SoapClient('https://portal.avanak.ir/webservice3.asmx?wsdl', array('encoding' => 'UTF-8'));

            $parameters['userName'] = $this->username;
            $parameters['password'] = $this->password;

            $parameters['number'] = $mobile;
            $parameters['vote'] = false;
            $parameters['serverid'] = 0;
            $parameters['text'] = $text;
            $parameters['CallFromMobile'] = "";

            $result = $service_client->QuickSendWithTTS($parameters)->QuickSendWithTTSResult;
            if ($result <= 0) {
                throw new Exception($this->getExceptionMessage($result));
            } else {
                return true;
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function getExceptionMessage($code)
    {
        switch ($code) {
            case 0:
                return ("عدم مجوز تایید صوت یا کاربری دمو می باشد");
                break;
            case -1:
                return ("نام کاربری یا رمز عبور اشتباه است");
                break;
            case -2:
                return ("شماره اشتباه میباشد");
                break;
            case -3:
                return ("عدم اعتبار کافی");
                break;
            case -5:
                return ("طول متن بیش از حد مجاز (1000 کاراکتر)");
                break;
            case -6:
                return ("زمان ارسال غیرمجاز میباشد");
                break;
            case -7:
                return ("خطا در تولید فایل صوتی");
                break;
            case -8:
                return ("طول متن بسیار کوتاه (کمتر از 3 ثانیه)");
                break;
            case -9:
                return ("خطا در تولید فایل صوتی");
                break;
            case -10:
                return ("متن خالی می باشد");
                break;
            case -71:
                return ("مدت ضبط صدا غیرمجاز میباشد");
                break;
            case -72:
                return ("عدم مجوز ضبط صدا");
                break;
            case -25:
                return ("ثبت ارسال سریع غیرفعال میباشد");
                break;
            case -20:
                return ("خطای ناشناخته");
                break;
            case -100:
                return ("عدم مجوز وب سرویس");
                break;
            case -101:
                return ("عدم احراز موبایل");
                break;
            case -102:
                return ("کاربری منقضی شده");
                break;
            default:
                return ("Could not send Voice Message.");
                break;
        }
    }
}
