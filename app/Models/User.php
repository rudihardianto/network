<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
   use HasApiTokens, HasFactory, Notifiable;

   /**
    * The attributes that are mass assignable.
    *
    * @var string[]
    */
   protected $fillable = [
      'username',
      'name',
      'email',
      'password',
   ];

   /**
    * The attributes that should be hidden for serialization.
    *
    * @var array
    */
   protected $hidden = [
      'password',
      'remember_token',
   ];

   /**
    * The attributes that should be cast.
    *
    * @var array
    */
   protected $casts = [
      'email_verified_at' => 'datetime',
   ];

   public function gravatar($size = 50)
   {
      $default = "mm";

      return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($this->email))) . "?d=" . urlencode($default) . "&s=" . $size;
   }

   public function statuses()
   {
      return $this->hasMany(Status::class);
   }

   public function makeStatus($string)
   {
      $this->statuses()->create([
         'body'       => $string,
         'identifier' => Str::slug(Str::random(31) . $this->id),
      ]);
   }

   public function timeline()
   {
      $following = $this->follows->pluck('id');

      return Status::whereIn('user_id', $following) //   whereIn('key', [1,2,3,4])
         ->orWhere('user_id', $this->id)
         ->latest()->get();
      //   $statuses = $this->statuses;
   }

   public function follows()
   {
      //Relasi
      return $this->belongsToMany(User::class, 'follows', 'user_id', 'following_user_id')->withTimestamps();
   }

   public function followers()
   {
      //Relasi
      return $this->belongsToMany(User::class, 'follows', 'following_user_id', 'user_id')->withTimestamps();
   }

   public function follow(User $user)
   {
      //Action
      return $this->follows()->save($user);
   }

}
