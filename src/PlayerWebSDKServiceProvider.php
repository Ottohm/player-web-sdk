<?php
namespace Ottohm\PlayerWebSDK;

use Illuminate\Support\ServiceProvider;


class PlayerWebSDKServiceProvider extends ServiceProvider{

    public function boot(){
        // $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/views','playerwebsdk');
        $this->mergeConfigFrom(__DIR__.'/config/playerwebsdk.php','playerwebsdk');
        $this->publishes([
            __DIR__.'/public'=> public_path('Ottohm/PlayerWebSDK'),
        ],'public');

    }

    public function register(){


    }
}