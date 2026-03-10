<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
class SystemSetting extends Model {
    protected $fillable = ["group","key","value","type","is_encrypted"];
    protected $casts    = ["is_encrypted"=>"boolean"];
    public static function get(string $key, mixed $default=null): mixed {
        try {
            return Cache::remember("setting_{$key}",3600,function() use($key,$default){
                $s=static::where("key",$key)->first(); return $s ? $s->value : $default;
            });
        } catch(\Exception $e){ return $default; }
    }
    public static function set(string $key, mixed $value, string $group="general"): void {
        static::updateOrCreate(["key"=>$key],["value"=>$value,"group"=>$group]);
        Cache::forget("setting_{$key}");
    }
}
