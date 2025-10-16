<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;

class GatewayTestController extends Controller
{
    public function index()
    {
        return view('gatewayTest');
    }

    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'driver' => 'required|string|in:zarinpal,sep,sepehr,pasargad',
            'merchantId' => 'nullable|string',
            'terminalId' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // try {
            $payment_invoice = (new Invoice)->amount($request->amount);

            switch ($request->driver) {
                case 'sepehr':
                    # code...
                    $payment = Payment::config([
                        'terminalId' => '98706462'
                    ]);
                    break;
                case 'sep':
                    # code...
                    $payment = Payment::config([
                        'terminalId' => '14150448',
                    ]);
                    break;
                case 'pasargad':
                    # code...
                    $payment = Payment::config([
                        'merchantId' => '5184921',
                        'terminalCode' => '2452058',
                        'certificate' => '<RSAKeyValue><Modulus>xbup109SKo7YqjvzKHeDX6ch8E+WW8EEzwnia1sidfotqWd8dkONWbDbrEOQdtugZdrx0gt22U4tKrj3fNTjhIPIbJrwBSavoOuRBlK1sAkpQu8FrlNpo0UTxZvt7eFW4p66U2POmkaFsojCNWbe572xmfUd1xZhc8nxcrd2rc0=</Modulus><Exponent>AQAB</Exponent><P>9MI4EDbWicycTK5TUXIgL/iDmCRHaivOJ4p2FarV6XbPPBjSut0+AGt26FEAX400pyulshbPGEjkudqmh2N5+w==</P><Q>ztCI8BL9RCI4NobD7zz3LRAcUmvNvNhFdmqjqPjsriw+hSHBqjnGwkXmteHtRkwjgla5vM97ulX4O9oe/3j01w==</Q><DP>H8CKcWAL2PiYVkJPQMOjdVWyDKy4LwfbyLlntEvjUFQ/cjZuMBu/jWJjnKPVfo/dAsrgxge7ehUKxymcbPf0vQ==</DP><DQ>ydgo7fX6jPbA2iapL+LWcnqYybBBOZ/yG2J0nENl649u7UxG0TZWT+EdDEKV6tgAiALQgAAYB4JEJVX6juekPQ==</DQ><InverseQ>qVZ3ZsRyJNiZbD2a1oCdClL50EJvEGGMUyY4svnP04t3kYjl3IJi/cIdFnid+g5niws9X5DHcIh8wpT4+J6ehA==</InverseQ><D>P6rTXx6NSGLCZN30x3zj8jKwfN5DfbvCvp9iJksr52zssvU9YB8ULmMB+I+wvnStSt6aqpVCaWoApRb1qV4q+3jET0L2bEE1oxufLnVRZmtV/ud6rh1bfkvDVndkYFfU7Z98eJUvq5bNEaKNC/+cuMRwCJoa3LEcwNAJ0YIqyEk=</D></RSAKeyValue>', // can be string (and set certificateType to xml_string) or an xml file path (and set cetificateType to xml_file)
                        'certificateType' => 'xml_string', // can be: xml_file, xml_string
                    ]);
                    break;
                case 'pasargad':
                    # code...
                    $payment = Payment::config([
                        'merchantId' => '77029422',
                        'terminalCode' => '77021395',
                    ]);
                    break;
                case 'zarinpal':
                    # code...
                    $payment = Payment::config([
                        'mode' => 'normal', // can be normal, sandbox, zaringate
                        'merchantId' => '8757df2f-9fb6-4efb-80f6-f22c060c832c',
                    ]);
                    break;

                default:
                    # code...
                    break;
            }

            $payment = $payment->via($request->driver)->callbackUrl('https://chargepal.ir/api/callback/pasargad')->purchase(
                $payment_invoice,
                function ($driver, $transactionId) use ($request) {
                    Cache::put('test_gateway_' . $transactionId, $request->all(), now()->addMinutes(30));
                }
            )->pay()->render();

            return $payment;
        // } catch (\Exception $e) {
        //     return redirect()->back()->withErrors($e->getMessage());
        // }
    }

    public function testCallback(Request $request)
    {
        dd($request->all());
    }
}
