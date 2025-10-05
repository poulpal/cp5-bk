<?php

namespace App\Models;

use App\Helpers\Inopay;
use App\Models\Accounting\AccountingAccount;
use App\Models\Accounting\AccountingDetail;
use App\Models\Accounting\AccountingDocument;
use App\Models\Accounting\AccountingTransaction;
use App\Models\Forum\ForumPost;
use Carbon\Carbon;
use FontLib\Table\Type\fpgm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Building extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            do {
                $uniqid = Str::random(32);
                $exists = self::where('contract_key', $uniqid)->exists();
            } while ($exists);
            $model->contract_key = $uniqid;
        });
    }

    public function buildingManagers()
    {
        return $this->hasMany(BuildingManager::class);
    }

    public function mainBuildingManagers()
    {
        return $this->hasMany(BuildingManager::class)->where('building_manager_type', 'main');
    }

    public function units()
    {
        return $this->hasMany(BuildingUnit::class);
    }

    public function residents()
    {
        return $this->hasManyThrough(User::class, BuildingUnit::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function depositRequests()
    {
        return $this->hasMany(DepositRequest::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function voiceMessages()
    {
        return $this->hasMany(VoiceMessage::class);
    }

    public function options()
    {
        return $this->hasOne(BuildingOptions::class);
    }

    public function polls()
    {
        return $this->hasMany(Poll::class);
    }

    public function reservables()
    {
        return $this->hasMany(Reservable::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function tolls()
    {
        return $this->hasMany(Toll::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_slug', 'slug');
    }

    public function accountingAccounts()
    {
        return $this->hasMany(AccountingAccount::class);
    }

    public function accountingDetails()
    {
        return $this->hasMany(AccountingDetail::class);
    }

    public function accountingDocuments()
    {
        return $this->hasMany(AccountingDocument::class);
    }

    public function accountingTransactions()
    {
        return $this->hasManyThrough(AccountingTransaction::class, AccountingDocument::class);
    }

    public function smsMessages()
    {
        return $this->hasMany(SmsMessage::class);
    }
    public function fcmMessages()
    {
        return $this->hasMany(FcmMessage::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function debtTypes()
    {
        return $this->hasMany(DebtType::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'buildings_modules', 'building_id', 'module_slug', 'id', 'slug')
            ->whereNull('buildings_modules.deleted_at')
            ->where(function ($query) {
                $query
                    ->where('buildings_modules.starts_at', '<=', now())
                    ->where('buildings_modules.ends_at', '>=', now())
                    ->orWhereNull('buildings_modules.ends_at');
            })
            ->withPivot(['id', 'starts_at', 'ends_at', 'created_at', 'updated_at', 'deleted_at']);
    }

    public function factors()
    {
        return $this->hasMany(Factor::class);
    }

    public function balances()
    {
        return $this->hasMany(Balance::class);
    }

    public function forumPosts()
    {
        return $this->hasMany(ForumPost::class);
    }

    public function getCurrentPlanAttribute()
    {
        if (Carbon::parse($this->plan_expires_at)->isPast()) {
            return null;
        } else {
            return $this->plan;
        }
    }
}
