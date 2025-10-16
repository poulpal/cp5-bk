<?php

namespace App\Helpers;

use Shetabit\Multipay\Drivers\SEP\SEP as SEPSEP;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Multipay\Invoice;
use GuzzleHttp\Client;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;

class SEP extends SEPSEP
{
    public function __construct(Invoice $invoice, $settings)
    {
        $this->invoice($invoice);
        $this->settings = (object)$settings;
        $this->client = new Client();
    }
}
