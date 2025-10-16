<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Receipt;
use Shetabit\Multipay\Abstracts\Driver;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Drivers\Pasargad\Utils\RSAProcessor;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Multipay\Request;
use DateTimeZone;
use DateTime;

class Pasargad extends Driver
{
    /**
     * Guzzle client
     *
     * @var object
     */
    protected $client;

    /**
     * Invoice
     *
     * @var Invoice
     */
    protected $invoice;

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

    /**
     * Pasargad(PEP) constructor.
     * Construct the class with the relevant settings.
     *
     * @param Invoice $invoice
     * @param $settings
     */
    public function __construct(Invoice $invoice, $settings)
    {
        $this->invoice($invoice);
        $this->settings = (object) $settings;
        $this->client = new Client();
        $this->token = $this->getToken();
    }

    /**
     * Purchase Invoice.
     *
     * @return string
     */
    public function purchase()
    {
        $invoiceData = $this->getPreparedInvoiceData();

        $this->invoice->transactionId($invoiceData['invoice']);

        // return the transaction's id
        return $this->invoice->getTransactionId();
    }

    /**
     * Pay the Invoice
     *
     * @return RedirectionForm
     */
    public function pay(): RedirectionForm
    {
        $purchaseUrl = $this->settings->apiPurchase;
        $tokenData = $this->request($purchaseUrl, $this->getPreparedInvoiceData());

        if ($tokenData['resultCode'] !== 0) {
            throw new InvalidPaymentException($tokenData['resultMsg']);
        }

        $paymentUrl = $tokenData['data']['url'];

        // redirect using HTML form
        return $this->redirectWithForm($paymentUrl, [], 'GET');
    }

    /**
     * Verify payment
     *
     * @return ReceiptInterface
     *
     * @throws InvalidPaymentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verify(): ReceiptInterface
    {
        $invoiceDetails = $this->request(
            $this->settings->apiCheckTransactionUrl,
            [
                'invoiceId' => Request::input('invoiceId')
            ]
        );
        if ($invoiceDetails['resultCode'] !== 0) {
            throw new InvalidPaymentException($invoiceDetails['resultMsg']);
        }
        if ($invoiceDetails['data']['status'] !== 2) {
            throw new InvalidPaymentException('Invalid payment');
        }
        if ($this->invoice->getAmount() * 10 != $invoiceDetails['data']['amount']) {
            throw new InvalidPaymentException('Invalid amount');
        }
        $iranTime = new DateTime('now', new DateTimeZone('Asia/Tehran'));
        $fields = [
            'Invoice' => Request::input('invoiceId'),
            'urlId' => $invoiceDetails['data']['url'],
        ];

        $verifyResult = $this->request($this->settings->apiVerificationUrl, $fields);

        return $this->createReceipt($verifyResult, $invoiceDetails);
    }

    /**
     * Generate the payment's receipt
     *
     * @param $referenceId
     *
     * @return Receipt
     */
    protected function createReceipt($verifyResult, $invoiceDetails)
    {
        $referenceId = $invoiceDetails['data']['referenceNumber'];
        $traceNumber = $invoiceDetails['data']['referenceNumber'];

        $reciept = new Receipt('Pasargad', $referenceId);

        $reciept->detail('TraceNumber', $traceNumber);
        // $reciept->detail('maskedCardNumber', $verifyResult['maskedCardNumber']);

        return $reciept;
    }

    /**
     * A default message for exceptions
     *
     * @return string
     */
    protected function getDefaultExceptionMessage()
    {
        return 'مشکلی در دریافت اطلاعات از بانک به وجود آمده است';
    }

    /**
     * Sign given data.
     *
     * @param string $data
     *
     * @return string
     */
    public function sign($data)
    {
        $certificate = $this->settings->certificate;
        $certificateType = $this->settings->certificateType;

        $processor = new RSAProcessor($certificate, $certificateType);

        return $processor->sign($data);
    }

    /**
     * Retrieve prepared invoice's data
     *
     * @return array
     */
    protected function getPreparedInvoiceData()
    {
        if (empty($this->preparedData)) {
            $this->preparedData = $this->prepareInvoiceData();
        }

        return $this->preparedData;
    }

    /**
     * Prepare invoice data
     *
     * @return array
     */
    protected function prepareInvoiceData(): array
    {
        $serviceCode = 8; // 8 : for buy request (bank standard)
        $serviceType = "PURCHASE"; // PURCHASE : for buy request (bank standard)
        $merchantCode = $this->settings->merchantId;
        $terminalCode = $this->settings->terminalCode;
        $amount = $this->invoice->getAmount() * 10; //rial
        $redirectAddress = $this->settings->callbackUrl;
        $invoiceNumber = $this->invoice->getUuid();

        $iranTime = new DateTime('now', new DateTimeZone('Asia/Tehran'));
        $timeStamp = $iranTime->format("Y/m/d H:i:s");
        $invoiceDate = $iranTime->format("Y-m-d");

        if (!empty($this->invoice->getDetails()['date'])) {
            $invoiceDate = $this->invoice->getDetails()['date'];
        }

        return [
            'invoice' => $invoiceNumber,
            'amount' => (int)$amount,
            'callbackApi' => $redirectAddress,
            'description' => $this->invoice->getDetail('description') ?? '',
            'invoiceDate' => $invoiceDate,
            'mobileNumber' => $this->invoice->getDetail('mobile') ?? '',
            'payerMail' => $this->invoice->getDetail('email') ?? '',
            'payerName' => $this->invoice->getDetail('name') ?? '',
            'serviceCode' => $serviceCode,
            'serviceType' => $serviceType,
            'terminalNumber' => $terminalCode,
            'nationalCode' => $this->invoice->getDetail('nationalCode') ?? '',
            'pans' => $this->invoice->getDetail('pans') ?? '',
        ];
    }

    /**
     * Make request to pasargad's Api
     *
     * @param string $url
     * @param array $body
     * @param string $method
     * @return array
     */
    protected function request(string $url, array $body, $method = 'POST'): array
    {
        $body = json_encode($body);

        $response = $this->client->request(
            'POST',
            $url,
            [
                'body' => $body,
                'headers' => [
                    'content-type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                "http_errors" => false,
                // "proxy" => [
                //     'http' => 'http://7xzFnlS0Hr:4hSLQiNDKp@188.121.107.233:80',
                //     'https' => 'http://7xzFnlS0Hr:4hSLQiNDKp@188.121.107.233:80'
                // ]
            ]
        );

        $result = json_decode($response->getBody(), true);

        return $result;
    }

    protected function getToken()
    {
        $response = $this->client->request(
            'POST',
            $this->settings->apiGetToken,
            [
                'body' => json_encode([
                    'username' => $this->settings->username,
                    'password' => $this->settings->password,
                ]),
                'headers' => [
                    'content-type' => 'application/json',
                ],
                "http_errors" => false,
                // "proxy" => [
                //     'http' => 'http://7xzFnlS0Hr:4hSLQiNDKp@188.121.107.233:80',
                //     'https' => 'http://7xzFnlS0Hr:4hSLQiNDKp@188.121.107.233:80'
                // ]
            ]
        );


        $result = json_decode($response->getBody(), true);

        if ($result['resultCode'] !== 0) {
            throw new InvalidPaymentException($result['resultMsg']);
        }

        return $result['token'];
    }
}
