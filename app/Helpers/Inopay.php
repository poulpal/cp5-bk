<?php

namespace App\Helpers;

use App\Models\Building;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Receipt;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Multipay\Request;
use DateTimeZone;
use DateTime;
use Illuminate\Support\Str;

class Inopay
{
    /**
     * Guzzle client
     *
     * @var object
     */
    protected $client;

    /**
     * Driver settings
     *
     * @var object
     */
    protected $settings;

    protected $token;

    /**
     * Prepared invoice's data
     *
     * @var array
     */
    protected $preparedData = array();

    public function __construct($settings = null)
    {
        // $this->invoice($invoice);
        if (!$settings) {
            $settings = config('payment.drivers.inopay');
        }
        $this->settings = (object) $settings;
        $this->client = new Client();
        $this->token = $this->getToken();
    }

    public function test()
    {
        $fields = [
            // 'accountNumber' => $this->createPerson(User::first())['accountNumber'],
            'username' => 'svz',
            'tenant' => $this->settings->tenant,
            // 'from' => 0,
            // 'size' => 10,
        ];
        $result = $this->request('http://inopay.ham-sun.com/rest/channel/wallet/v1/balance' , $fields, 'GET');
        return $result;
    }

    public function createPerson(User $user)
    {
        $data = [
            'tenant' => $this->settings->tenant,
            'firstName' => $user->first_name ?? ' ',
            'lastName' => $user->last_name ?? ' ',
            "username" => $user->mobile,
            "phoneNumber" => $this->addCountryCode($user->mobile),
            "email" => "user@chargepal.ir",
            "postalCode" => "1234567890",
            "address" => "x",
            "city" => "x",
            "province" => "x",
            "country" => "ایران",
            "identifier" => $user->mobile,
        ];
        $result = $this->request('http://inopay.ham-sun.com/rest/channel/wallet/v1/person', $data, 'POST');
        return $result;
    }

    public function createOrganization(Building $building)
    {
        $user = $building->mainBuildingManagers()->first();
        $data = [
            'tenant' => $this->settings->tenant,
            'name' => $building->name,
            'registerCode' => $building->name_en,
            "username" => $building->name_en,
            "phoneNumber" => $this->addCountryCode($user->details->phone_number),
            "email" => "building@chargepal.ir",
            "postalCode" => $user->details->postal_code,
            "address" => $user->details->address,
            "city" => $user->details->city,
            "province" => "x",
            "country" => "ایران",
            "identifier" => $building->name_en,
        ];
        $result = $this->request('http://inopay.ham-sun.com/rest/channel/wallet/v1/organization', $data, 'POST');
        return $result;
    }

    public function chargeWallet($amount, User|Building $account, $identifier = null)
    {
        if ($account instanceof User) $this->createPerson($account);
        if ($account instanceof Building) $this->createOrganization($account);

        $data = [
            'identifier' => $identifier ?? strval(Str::uuid()),
            'tenant' => $this->settings->tenant,
            'amount' => $amount,
            'username' => $account instanceof User ? $account->mobile : $account->name_en,
            'localDate' => Carbon::now()->format('Y-m-d H:i:s'),
            'callBackUrl' => $this->settings->callbackUrl,
        ];
        $result = $this->request('http://inopay.ham-sun.com/rest/channel/wallet/v1/chargeWallet', $data, 'POST');
        return $result;
    }

    public function verifyChargeWallet($identifier)
    {
        $data = [
            'tenant' => $this->settings->tenant,
            'identifier' => $identifier,
            'from' => 0,
            'size' => 1,
        ];
        $result = $this->request('http://inopay.ham-sun.com/rest/channel/wallet/v1/chargeWallet', $data, 'GET');
        return $result['chargeWallets'][0];
    }

    public function getBalance(User|Building $account)
    {
        if ($account instanceof User) $this->createPerson($account);
        if ($account instanceof Building) $this->createOrganization($account);

        $data = [
            'username' => $account instanceof User ? $account->mobile : $account->name_en,
            'tenant' => $this->settings->tenant,
        ];
        $result = $this->request('http://inopay.ham-sun.com/rest/channel/wallet/v1/balance', $data, 'GET');
        return $result['balance'] / 10;
    }

    public function paymentOrder(int $amount, Building $building, $sheba, $name, $description)
    {
        $this->createOrganization($building);
        $data = [
            'tenant' => $this->settings->tenant,
            'amount' => $amount * 10,
            'sourceAccountNumber' => $this->createOrganization($building)['accountNumber'],
            'beneficiaryId' => '2710000001',
            'beneficiaryName' => $name,
            'beneficiaryIban' => $sheba,
            'username' => $building->name_en,
            'stan' => strval(Str::uuid()),
            'description' => $description,
        ];
        $result = $this->request('http://inopay.ham-sun.com/rest/channel/wallet/v1/paymentOrder', $data, 'POST');
        if (!isset($result['paymentReference'])) {
            throw new InvalidPaymentException('خطا در ارتباط با درگاه پرداخت');
        }

        return $result;
    }

    public function verifyPaymentOrder($paymentReference)
    {
        $data = [
            'tenantCode' => $this->settings->tenant,
            'paymentReference' => $paymentReference,
            'from' => 0,
            'size' => 1,
        ];
        $result = $this->request('http://inopay.ham-sun.com/rest/channel/wallet/v1/paymentOrder', $data, 'GET');
        return $result['items'][0];
    }

    public function transfer(int $amount, User|Building $from, User|Building $to, $description)
    {
        if ($from instanceof User) $this->createPerson($from);
        if ($from instanceof Building) $this->createOrganization($from);
        if ($to instanceof User) $this->createPerson($to);
        if ($to instanceof Building) $this->createOrganization($to);

        $fromAccountNumber = $from instanceof User ? $this->createPerson($from)['accountNumber'] : $this->createOrganization($from)['accountNumber'];
        $toAccountNumber = $to instanceof User ? $this->createPerson($to)['accountNumber'] : $this->createOrganization($to)['accountNumber'];
        $data = [
            'tenant' => $this->settings->tenant,
            'fromAccountNumber' => $fromAccountNumber,
            'toAccountNumber' => $toAccountNumber,
            'amount' => $amount * 10,
            'identifier' => strval(Str::uuid()),
            'description' => $description,
            'stan' => strval(Str::uuid()),
        ];
        $result = $this->request('http://inopay.ham-sun.com/rest/channel/wallet/v1/transfer', $data, 'POST');
        return $result;
    }

    protected function request(string $url, array $body, $method = 'POST', $removeFromSign = []): array
    {
        $sign = $this->sign($body, $removeFromSign);
        if ($method == 'GET') {
            $url .= '?' . http_build_query(array_merge($body, ['sign' => $sign]));
        }
        // dump(json_encode(
        //     array_merge($body, ['sign' => $sign])
        // ));
        $options = [
            'body' => json_encode(
                array_merge($body, ['sign' => $sign])
            ),
            'headers' => [
                'content-type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer ' . $this->token,
            ],
            "http_errors" => false,
        ];

        if (app()->environment() !== 'local') {
            $options["proxy"] = [
                'http' => 'http://7xzFnlS0Hr:4hSLQiNDKp@188.121.107.233:80',
                'https' => 'http://7xzFnlS0Hr:4hSLQiNDKp@188.121.107.233:80'
            ];
        }

        $response = $this->client->request(
            $method,
            $url,
            $options
        );

        $result = json_decode($response->getBody(), true);

        return $result;
    }

    public function getToken()
    {
        $data = [
            'username' => $this->settings->username,
            'password' => $this->settings->password,
        ];
        $sign = $this->sign($data);
        $options = [
            'body' => json_encode(
                array_merge($data, ['sign' => $sign])
            ),
            'headers' => [
                'content-type' => 'application/json',
            ],
            "http_errors" => false,
        ];

        if (app()->environment() !== 'local') {
            $options["proxy"] = [
                'http' => 'http://7xzFnlS0Hr:4hSLQiNDKp@188.121.107.233:80',
                'https' => 'http://7xzFnlS0Hr:4hSLQiNDKp@188.121.107.233:80'
            ];
        }

        $response = $this->client->request(
            'POST',
            $this->settings->apiGetToken,
            $options
        );

        if ($response->getStatusCode() !== 200) {
            throw new InvalidPaymentException('خطا در ارتباط با درگاه پرداخت');
        }
        $result = json_decode($response->getBody(), true);
        return $result['token'];
    }

    protected function sign($data, $removeFromSign = [])
    {
        $str = '#';
        foreach ($data as $key => $value) {
            if (in_array($key, $removeFromSign)) {
                continue;
            }
            if (is_array($value)) {
                $value = implode('#', $value) . '#';
            } else {
                $str .=  strval($value) . '#';
            }
        }
        $sign = hash_hmac('sha256', $str, $this->settings->key);
        return $sign;
    }

    protected function addCountryCode($mobile)
    {
        if (substr($mobile, 0, 2) == '09') {
            return '98' . substr($mobile, 1);
        }
        return $mobile;
    }
}
