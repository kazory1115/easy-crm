<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity; // Added LogsActivity trait

class Customer extends Model
{
    use HasFactory, SoftDeletes, LogsActivity; // Added LogsActivity trait

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'mobile',
        'email',
        'tax_id',
        'website',
        'industry',
        'address',
        'notes',
        'status',
        // 向下相容舊欄位命名（透過 mutator 映射到實際欄位）
        'contact_phone',
        'contact_email',
        'company_tax_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 關聯：建立者
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 關聯：更新者
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 關聯：報價單
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * 關聯：客戶聯絡人
     */
    public function contacts()
    {
        return $this->hasMany(CustomerContact::class);
    }

    /**
     * 關聯：客戶活動
     */
    public function activities()
    {
        return $this->hasMany(CustomerActivity::class);
    }

    /**
     * 關聯：商機
     */
    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    /**
     * Scope: 只取得啟用的客戶
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: 搜尋客戶
     */
    public function scopeSearch($query, $keyword)
    {
        if (!$keyword) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('contact_person', 'like', "%{$keyword}%")
                ->orWhere('phone', 'like', "%{$keyword}%")
                ->orWhere('mobile', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%")
                ->orWhere('tax_id', 'like', "%{$keyword}%");
        });
    }

    /**
     * 向下相容：舊欄位 contact_phone -> phone
     */
    public function setContactPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $value;
    }

    /**
     * 向下相容：舊欄位 contact_email -> email
     */
    public function setContactEmailAttribute($value): void
    {
        $this->attributes['email'] = $value;
    }

    /**
     * 向下相容：舊欄位 company_tax_id -> tax_id
     */
    public function setCompanyTaxIdAttribute($value): void
    {
        $this->attributes['tax_id'] = $value;
    }

    public function getContactPhoneAttribute(): ?string
    {
        return $this->attributes['phone'] ?? null;
    }

    public function getContactEmailAttribute(): ?string
    {
        return $this->attributes['email'] ?? null;
    }

    public function getCompanyTaxIdAttribute(): ?string
    {
        return $this->attributes['tax_id'] ?? null;
    }
}
