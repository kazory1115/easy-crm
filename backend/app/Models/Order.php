<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity; // Added LogsActivity trait

class Order extends Model
{
    use HasFactory, SoftDeletes, LogsActivity; // Added SoftDeletes and LogsActivity traits

    protected $fillable = [
        'order_number',
        'quote_id',
        'customer_id',
        'customer_name',
        'contact_phone',
        'contact_email',
        'project_name',
        'order_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'tax_rate',
        'status',
        'payment_status',
        'shipped_at',
        'completed_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'due_date' => 'date',
        'subtotal'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'tax_rate'        => 'decimal:4',
        'shipped_at'      => 'datetime',
        'completed_at'    => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    /**
     * Boot method - 自動生成訂單號
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * 生成訂單號
     */
    public static function generateOrderNumber()
    {
        $prefix = 'O';
        $date = now()->format('Ymd');

        $lastOrder = self::where('order_number', 'like', $prefix . $date . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        $sequence = $lastOrder ? (intval(substr($lastOrder->order_number, -4)) + 1) : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 關聯：原始報價單
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * 關聯：客戶
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 關聯：訂單項目
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class)->orderBy('sort_order');
    }

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
     * 重新計算所有金額（小計、稅金、總計）
     */
    public function recalculateTotal()
    {
        $this->load('items');

        $subtotal = $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $taxRate = $this->tax_rate ?? 0;
        $taxAmount = $subtotal * $taxRate;

        $discountAmount = $this->discount_amount ?? 0;
        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        $this->subtotal = round($subtotal, 2);
        $this->tax_amount = round($taxAmount, 2);
        $this->total_amount = round($totalAmount, 2);
        
        $this->save();
    }
}