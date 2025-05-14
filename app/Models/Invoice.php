<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Invoice extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'purchase_date',
        'type',
        'brand',
        'model',
        'shop_name',
        'price',
        'user_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    /**
     * Get the user that owns the invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('invoice_pdfs')
            ->acceptsMimeTypes(['application/pdf'])
            ->singleFile(); // To allow only one PDF per invoice
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // You can define media conversions here if needed, for example, creating a thumbnail.
        // For now, we'll leave it empty as we are just storing the PDF.
    }
} 