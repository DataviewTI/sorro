<?php

namespace Dataview\Sorro;

use Illuminate\Support\ServiceProvider;

class SorroServiceProvider extends ServiceProvider
{
    public static function pkgAddr($addr){
      return __DIR__.'/'.$addr;
    }

    public function boot()
    {
      // $this->publishes([
      //   __DIR__.'/config/intranetone.php' => config_path('intranetone.php'),
      // ]);
      
      // $this->publishes([
      //   __DIR__.'/root' => base_path('/')
      // ],'root');

    // $this->publishes([__DIR__ .'/resources/js/components' =>
    // resource_path('js/components/vendor/sorro')], 'vue-components');

      $this->loadViewsFrom(__DIR__.'/views', 'Sorro');
      //$this->loadMigrationsFrom(__DIR__.'/database/migrations');
      
      //$this->mergeConfigFrom(__DIR__.'/config/audit.php', 'audit.resolver');
      // $this->mergeConfigFrom(__DIR__.'/config/filesystems.php', 'filesystems.disks');

      
    }

    public function register()
    {
      /*$this->app->bind('dataview-intranetone', function() {
        return new IntranetOne;
      });*/
      $this->commands([
        Console\Install::class,
        Console\Remove::class,
      ]);

      //define um namespace para cada rota carregada através do package
      $this->app['router']->group(['namespace' => 'dataview\sorro'], function () {
        include __DIR__.'/routes/web.php';
      });


    // $this->loadViewComponentsAs('courier', [
    //     ExampleComponent::class,
    // ]);

      // $this->loadRoutesFrom(__DIR__.'/routes/web.php');

      //adiciona as middlewares HttpsProtocol e SentinelAdmin ao Kernel
      // $kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');
      // $kernel->pushMiddleware('\Dataview\IntranetOne\Http\Middleware\HttpsProtocol');
      // $this->app['router']->aliasMiddleware('admin', '\Dataview\IntranetOne\Http\Middleware\SentinelAdmin');
      
       $this->app->make('Dataview\Sorro\SorroController');
      // $this->app->make('Dataview\IntranetOne\AuthController');
    }
}
