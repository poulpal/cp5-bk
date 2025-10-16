<?php

namespace App\Console\Commands;

use App\Models\Accounting\AccountingAccount;
use App\Models\Accounting\AccountingDocument;
use App\Models\Accounting\AccountingTransaction;
use App\Models\Announcement;
use App\Models\Contact;
use App\Models\DepositRequest;
use App\Models\Invoice;
use App\Models\Poll;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HotFixDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotfix:demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (env('APP_ENV') == 'production') {
            $this->error('You can not run this command in production environment');
            return Command::FAILURE;
        }

        foreach (Invoice::all() as $invoice) {
            if ($invoice->amount < 0) {
                if ($invoice->description == 'خسارت تاخیر در پرداخت شارژ') continue;
                if ($invoice->description == 'هزینه شارژ فروردین 1403') continue;
                if ($invoice->description == 'هزینه شارژ اردیبهشت 1403') continue;
                $invoice->description = 'بدهی دستی';
                $invoice->save();
            }else{
                $invoice->description = 'پرداخت آنلاین بدهی';
                $invoice->save();
            }
        }


        $this->info('This is a demo command');
        return Command::SUCCESS;
    }
}
