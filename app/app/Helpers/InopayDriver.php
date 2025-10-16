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
use Illuminate\Support\Str;


class InopayDriver extends Driver
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

    /**
     * Prepared invoice's data
     *
     * @var array
     */
    protected $preparedData = array();

    /**
     * Inopay Helper
     *
     * @var Inopay
     */
    protected Inopay $inopay;

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
        $this->inopay = new Inopay($settings);
    }

    /**
     * Purchase Invoice.
     *
     * @return string
     */
    public function purchase()
    {
        $this->invoice->amount($this->invoice->getAmount() * 10);
        $this->invoice->transactionId(strval(Str::uuid()));
        return $this->invoice->getTransactionId();
    }

    /**
     * Pay the Invoice
     *
     * @return RedirectionForm
     */
    public function pay(): RedirectionForm
    {
        $account = $this->invoice->getDetail('account');
        try {
            $response = $this->inopay->chargeWallet($this->invoice->getAmount(), $account , $this->invoice->getTransactionId());
        } catch (\Exception $e) {
            throw new InvalidPaymentException($e->getMessage());
        }

        $paymentUrl = $response['link'];

        $query = parse_url($paymentUrl, PHP_URL_QUERY);
        parse_str($query, $params);
        $token = $params['token'];
        $paymentUrl = parse_url($paymentUrl, PHP_URL_SCHEME) . '://' . parse_url($paymentUrl, PHP_URL_HOST) . parse_url($paymentUrl, PHP_URL_PATH);


        // redirect using HTML form
        return $this->redirectWithForm($paymentUrl, [
            'token' => $token,
        ], 'GET');
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
        $identifier = Request::input('identifier');
        $verifyResult = $this->inopay->verifyChargeWallet($identifier);

        if ($verifyResult['ipgResponseValid'] != true) {
            throw new InvalidPaymentException('پرداخت ناموفق بود');
        }

        return $this->createReceipt($verifyResult);
    }

    /**
     * Generate the payment's receipt
     *
     * @param $referenceId
     *
     * @return Receipt
     */
    protected function createReceipt($verifyResult)
    {
        $reciept = new Receipt('inopay', $verifyResult['reference']);

        $reciept->detail('TraceNumber', $verifyResult['reference']);
        $reciept->detail('maskedPan', $verifyResult['maskedPan']);

        return $reciept;
    }

    /**
     * A default message for exceptions
     *
     * @return string
     */
    protected function getDefaultExceptionMessage()
    {
        return 'مشکلی در دریافت اطلاعات از درگاه پرداخت به وجود آمده است';
    }
}
