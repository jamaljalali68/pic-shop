<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    //.......................................................

    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeMostPopular($query)
    {
       
         return $query->where('price', '>', 8000);
    }

    public function scopeDefault($query)
    {
        
        return $query->where('price', '>', 0);
    }

    public function scopeLowToHigh($query)
    {

        return $query->orderBy('price', 'asc');
    }

    public function scopeHighToLow($query)
    {
        return $query->orderBy('price', 'desc');
    }

    //...............................................................

    public function scopePriceSort1($query)
    {
        return $query->whereBetween('price', [10, 100000]);
    }
    public function scopePriceSort2($query)
    {
        return $query->whereBetween('price', [101000, 200000]);
    }
    public function scopePriceSort3($query)
    {
        return $query->whereBetween('price', [201000, 300000]);
    }
    //................................................................

    public function scopeFree($query)
    {
        return $query->where('price', '=', 0);
    }
  
    public function scopeNotFree($query)
    {
        return $query->where('price', '>', 0);
    }


}
