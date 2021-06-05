<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class book extends Model
{
    use HasFactory;

    protected $fillable = [
        "bookName", 	"publication", 	"author", 	"isbn", 	"price", 	"stock",	"edition", 	"year", 	"negotiable", 	"seller_id","image" 
    ];
}
