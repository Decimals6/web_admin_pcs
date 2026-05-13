<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiUser extends Model
{
    protected $table = 'premi_user';
    protected $fillable = ['user_id', 'nominal'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}