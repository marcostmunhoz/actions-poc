<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'total',
        'status',
        'user_id',
        'invoice_id',
    ];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany
     */
    public function products()
    {
        return $this
            ->belongsToMany(Product::class)
            ->withPivot('quantity');
    }

    /**
     * @return BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->id = Str::uuid();
        });
    }
}
