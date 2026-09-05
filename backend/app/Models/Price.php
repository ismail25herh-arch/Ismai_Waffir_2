<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Price extends Model { use HasFactory; protected $fillable = ['user_id','store_id','product_id','unit_id','brand_id','amount','price']; protected $casts = ['amount'=>'float','price'=>'float']; public function product(){return $this->belongsTo(Product::class);} public function store(){return $this->belongsTo(Store::class);} public function user(){return $this->belongsTo(User::class);} public function unit(){return $this->belongsTo(Unit::class);} public function brand(){return $this->belongsTo(Brand::class);} public function ratings(){return $this->hasMany(Rating::class);} public function reports(){return $this->hasMany(Report::class);} }
