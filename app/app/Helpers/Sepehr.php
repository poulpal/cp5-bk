<?php

namespace App\Helpers;

use Shetabit\Multipay\Drivers\Sepehr\Sepehr as SepehrSepehr;
use Shetabit\Multipay\RedirectionForm;

class Sepehr extends SepehrSepehr
{
    public function pay(): RedirectionForm
    {
        if (!empty($this->invoice->getDetails()['business'])) {
            return $this->redirectWithForm($this->settings->apiPaymentUrl, [
                'token' => $this->invoice->getTransactionId(),
                'terminalID' => $this->settings->terminalId,
                'nationalCode' => $this->invoice->getDetails()['business']
            ], 'POST');
        }
        return $this->redirectWithForm($this->settings->apiPaymentUrl, [
            'token' => $this->invoice->getTransactionId(),
            'terminalID' => $this->settings->terminalId
        ], 'POST');
    }
}
