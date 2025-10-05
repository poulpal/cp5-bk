<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DownloadController extends Controller
{
    public function downloadExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_name' => 'required|string',
            'data' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $fileName = $request->input('file_name');
        $data = base64_decode($request->input('data'));

        if ($data === false) {
            return response()->json(['error' => 'Invalid base64 data'], 400);
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'download_');
        file_put_contents($tempFilePath, $data);

        return response()->download($tempFilePath, $fileName)->deleteFileAfterSend(true);
    }
}
