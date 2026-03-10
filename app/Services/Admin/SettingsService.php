<?php
namespace App\Services\Admin;
use App\Models\SystemSetting;
class SettingsService {
    public function getAllGrouped():array { return SystemSetting::all()->groupBy("group")->toArray(); }
    public function updateGroup(string $group, array $data):void { foreach($data as $k=>$v) SystemSetting::set($k,$v,$group); }
    public function get(string $key, mixed $default=null):mixed { return SystemSetting::get($key,$default); }
}
