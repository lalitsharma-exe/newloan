<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
class AppServiceProvider extends ServiceProvider {
    public function register():void {}
    public function boot():void {
        Paginator::useBootstrapFive();
        Route::model('campaign', \App\Models\SmsCampaign::class);
        Route::model('role', \App\Models\AdminRole::class);
    }
}
