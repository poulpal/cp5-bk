<?php

namespace App\Console\Commands;

use App\Models\Building;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\Facades\Image;

class CreateQrcodes extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qrcodes:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Qrcodes for specific building';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $building_id = $this->ask('Enter Building id in db');
        $building = Building::find($building_id);
        if (!$building) {
            return Command::FAILURE;
        }
        if ($building_id == 2) {
            $this->info('Hamrah Shahr');
            return $this->handleHarmrahShar($building);
        } else {
            $building_name_en = $building->name_en;
            $zip = new \ZipArchive();
            $zip->open(public_path('img/qrcodes/' . $building_name_en . '.zip'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $this->withProgressBar($building->units, function ($unit) use ($zip, $building_name_en) {
                $image_name = $building_name_en . "_" . $unit->unit_number . ".png";
                if (file_exists(public_path("img/qrcodes/" . $image_name))) {
                    $zip->addFile(public_path("img/qrcodes/" . $image_name), $image_name);
                    return;
                }
                $qrcode = Image::make(base64_encode(QrCode::format('png')->errorCorrection('H')->size(310)->margin(1)->backgroundColor(255, 255, 255)->color(0, 0, 0)->generate("https://c36.ir/b" . $unit->token)));
                $output = Image::make(public_path("img/qrcode_template.png"));
                $output->insert($qrcode, 'center', $x = 0, $y = -12);
                // $output->text($unit->unit_number, 745, 1320, function ($font) {
                //     $font->file(public_path('fonts/Inter-Bold.ttf'));
                //     $font->size(80);
                //     $font->color('#FFFFFF');
                //     $font->align('center');
                //     $font->valign('center');
                // });
                $Arabic = new \ArPHP\I18N\Arabic();
                $text = $Arabic->utf8Glyphs($unit->unit_number);
                $output->text($unit->unit_number, 525, 805, function ($font) {
                    $font->file(public_path('fnt/Samim-Bold-FD.ttf'));
                    $font->size(50);
                    $font->color('#f58220');
                    $font->align('center');
                    $font->valign('center');
                });
                $output->save(public_path("img/qrcodes/" . $image_name));
                $zip->addFile(public_path("img/qrcodes/" . $image_name), $image_name);
            });
            $zip->close();
            $this->info(asset('img/qrcodes/' . $building_name_en . '.zip'));
            return Command::SUCCESS;
        }
    }

    private function handleHarmrahShar($building)
    {
        $building_name_en = $building->name_en . '_new';
        $zip = new \ZipArchive();
        $zip->open(public_path('img/qrcodes/' . $building_name_en . '.zip'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $this->withProgressBar($building->units, function ($unit) use ($zip, $building_name_en) {
            $image_name = $building_name_en . "_" . $unit->unit_number . ".png";
            if (file_exists(public_path("img/qrcodes/" . $image_name))) {
                $zip->addFile(public_path("img/qrcodes/" . $image_name), $image_name);
                return;
            }
            $qrcode = Image::make(base64_encode(QrCode::format('png')->errorCorrection('H')->size(310)->margin(1)->backgroundColor(255, 255, 255)->color(0, 0, 0)->generate("https://c36.ir/b" . $unit->token)));
            $output = Image::make(public_path("img/hsh_template.png"));
            $output->insert($qrcode, 'center', $x = 0, $y = -12);
            // $output->text($unit->unit_number, 745, 1320, function ($font) {
            //     $font->file(public_path('fonts/Inter-Bold.ttf'));
            //     $font->size(80);
            //     $font->color('#FFFFFF');
            //     $font->align('center');
            //     $font->valign('center');
            // });
            $Arabic = new \ArPHP\I18N\Arabic();
            $text = $Arabic->utf8Glyphs($unit->unit_number);
            $output->text($unit->unit_number, 525, 805, function ($font) {
                $font->file(public_path('fnt/Samim-Bold-FD.ttf'));
                $font->size(50);
                $font->color('#f58220');
                $font->align('center');
                $font->valign('center');
            });
            $output->save(public_path("img/qrcodes/" . $image_name));
            $zip->addFile(public_path("img/qrcodes/" . $image_name), $image_name);
        });
        $zip->close();
        $this->info(asset('img/qrcodes/' . $building_name_en . '.zip'));
        return Command::SUCCESS;
    }
}
