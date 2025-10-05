<?php

namespace App\Jobs;

use App\Models\Building;
use Google_Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Morilog\Jalali\Jalalian;
use Revolution\Google\Sheets\Facades\Sheets;

class UpdateCRMGoogleSheets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Google_Client();
        $client->setAuthConfig(resource_path('chargepal-413ae-43ebc86b83cd.json'));
        $client->addScope('https://www.googleapis.com/auth/spreadsheets');

        $googleSheets = new \Google_Service_Sheets($client);
        $sheet = $googleSheets->spreadsheets->get('1S_fHxuB2GqUT-0mm4hgZ0PUWgrwl9LGxsShMD5O8jf4');
        $buildings = Building::all();

        $values = $buildings->map(function ($building) {
            $building_manager = $building->mainBuildingManagers()->first();
            $details = $building_manager->details ?? new \stdClass();

            return [
                $building->id,
                $building->name,
                $building->name_en,
                $building->plan_slug,
                Jalalian::forge($building->plan_expires_at)->format('Y/m/d'),
                $building->is_verified,
                $building->signed_contract,
                Jalalian::forge($building->created_at)->format('Y/m/d'),
                $building_manager->first_name ?? "",
                $building_manager->last_name ?? "",
                $building_manager->mobile ?? "",
                $details->phone_number ?? "",
                $details->province ?? "",
                $details->city ?? "",
                $details->district ?? "",
                $details->address ?? "",
                $details->postal_code ?? "",
                $details->national_id ?? "",
                $details->email ?? "",
                $details->sheba_number ?? "",
                $details->card_number ?? "",
            ];
        })->toArray();

        # prepend the header
        array_unshift($values, [
            'شناسه',
            'نام',
            'نام انگلیسی',
            'نوع پلن',
            'تاریخ انقضا',
            'تایید شده',
            'قرارداد امضا شده',
            'تاریخ ثبت',
            'نام مدیر',
            'نام خانوادگی مدیر',
            'موبایل مدیر',
            'تلفن',
            'استان',
            'شهر',
            'منطقه',
            'آدرس',
            'کد پستی',
            'کد ملی',
            'ایمیل',
            'شماره شبا',
            'شماره کارت',
        ]);

        // dd($values);

        $body = new \Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        $result = $googleSheets->spreadsheets_values->update('1S_fHxuB2GqUT-0mm4hgZ0PUWgrwl9LGxsShMD5O8jf4', 'Sheet1', $body, $params);
    }
}
