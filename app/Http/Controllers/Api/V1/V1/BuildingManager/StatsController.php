<?php

public function index(Request $request)
{
    try {
        \Log::info('BM Stats called', [
            'user_id' => optional($request->user())->id,
            'query'   => $request->all(),
        ]);

        // --- منطق اصلی فعلی شما اینجاست ---
        // return response()->json([...]);
        // -----------------------------------

    } catch (\Throwable $e) {
        \Log::error('BM Stats error', [
            'msg'  => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        // به‌جای 500 خام، پیام کنترل‌شده بدهیم تا داشبورد نخوابد
        return response()->json([
            'ok'    => false,
            'error' => 'SERVER_ERROR',
            'hint'  => 'stats-failed', // برای تشخیص در فرانت
        ], 200);
    }
}
