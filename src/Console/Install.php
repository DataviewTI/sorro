<?php
namespace Dataview\Sorro\Console;

ini_set('max_execution_time', 3600);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Dataview\Sorro\SorroServiceProvider;
// use OwenIt\Auditing\AuditingServiceProvider;
use Illuminate\Support\Facades\Schema;
use Dataview\Sorro\Sorro;
use Dataview\Sorro\DatabaseSeeder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Install extends Command
{
    // protected $name = 'sorro:install';
    // protected $description = 'Instalação do pacote Dataview/Sorro';
    protected $signature = "sorro:install {--assets}";
    protected $description = 'Instalação do pacote Dataview/Sorro';
        
    public function handle()
    {
      if(!$this->option('assets')){
        Sorro::installMessages($this);

        $this->line('Publicando os arquivos de configuração...');
        
        Artisan::call('vendor:publish', [
            '--provider' => SorroServiceProvider::class,
        ]);

        Sorro::installMessages($this);

        // $this->line('Publicando providers de terceiros...');
        // Artisan::call('vendor:publish', [
        //     '--provider' => SentinelServiceProvider::class,
        // ]);

        // $audits_exists = false;
        $migrations_to_remove = [];
        $base_migrations = scandir(database_path('migrations'));
        foreach($base_migrations as $f){

          if(strpos($f,'users_table')!==false)
            array_push($migrations_to_remove,$f);
  
          if(strpos($f,'password_resets_table')!==false)
            array_push($migrations_to_remove,$f);

          // if(strpos($f,'create_audits_table')!==false)
          //   $audits_exists = true;
        }

        Sorro::installMessages($this);


        Sorro::installMessages($this);
        if(count($migrations_to_remove))
          $this->line('Apagando arquivos de migração desnecessários...');
          foreach($migrations_to_remove as $f){
            unlink(database_path("migrations/{$f}"));
        }

        // if(!$audits_exists){
        //   $this->line('Publicando AuditingServiceProvider...');
        //   Artisan::call('vendor:publish', [
        //     '--provider' => AuditingServiceProvider::class,
        //   ]);
        // }

        $this->line('Criando link simbólico...');
        Artisan::call('storage:link');

        Sorro::installMessages($this,2);

        //só executa migração se as tabelas ainda não existirem
        // if(!Schema::hasTable('activations')){//uma delas
        //   $this->line('Executando migrações de terceiros...');
        //   Artisan::call('migrate', [
        //     '--path' => 'database/migrations',
        //   ]);
        // }

        if(!Schema::hasTable('services')){//uma delas
          $this->line('Executando migrações de sorro dashboard...');
          Artisan::call('migrate', [
            '--path' => 'vendor/dataview/sorro/src/database/migrations',
          ]);
        }
        
        
        Sorro::installMessages($this,1);

        $this->line('seeding database...');
        Artisan::call('db:seed', [
          '--class' => DatabaseSeeder::class,
        ]);
        $this->assets();

      }
      else{
        $this->assets();
      }
        //Compilação de assets
        
        $this->info('Sorro Dashboard Instalado com sucesso! _|_');
    }

    public function assets(){

        // $this->line('instalando cross-env');
        // (new Process(['npm','i','cross-env','--save']))->setTimeout(3600)->mustRun();
        // $this->line('Executando npm install');
        // (new Process(['npm','i']))->setTimeout(3600)->mustRun();
          // Artisan::call('io-user:install');
        // $this->line('Instalando IO config service...');
        // Artisan::call('io-config:install');

        /** Processo de instalação individual de pacotes via PNPM via package.json->IODependencies */
      $pkg = json_decode(file_get_contents(SorroServiceProvider::pkgAddr('/assets/package.json')),true);

      (new Process(['npm','set','progress=false']))->run();
      
      $this->comment("Instalando npm package {$pkg['name']}@{$pkg['version']}");

      // try{
      //   (new Process(['npm','install',"vendor/dataview/{$pkg['name']}/src/assets/",'--save']))->setTimeout(3600)->mustRun();
      // }
      // catch(ProcessFailedException $exception){
      //   $this->error($exception->getMessage());
      // }


      $this->line('Instalando dependencias...');
      $deps = $pkg["SRdependencies"];

      $bar = $this->output->createProgressBar(count($deps)+1);

      foreach($deps as $key => $value){
        //checa se já existe e é a mesma versão
        $_oldpkg = null;
        if(File::isDirectory(base_path("node_modules/{$key}"))){
          $_oldpkg = json_decode(file_get_contents("node_modules/{$key}/package.json"));
        }

        try{
          $bar->advance();
          if($_oldpkg==null){
            $this->comment(" instalando {$key}@{$deps[$key]}");
            (new Process(['npm','i',"{$key}@{$deps[$key]}", '--save']))->setTimeout(3600)->mustRun();
          }
          else{ 
            $old_version = preg_replace("/[^0-9]/", "",$_oldpkg->version);
            $new_version = preg_replace("/[^0-9]/", "",$deps[$key]);
            if($old_version == $new_version)
              $this->comment(" em cache {$key}@{$deps[$key]}");
            else{
              $this->comment(" atualizando {$key} de {$deps[$key]} para {$_oldpkg->version}");
              (new Process(['npm','i', "{$key}@{$deps[$key]}", '--save']))->setTimeout(3600)->mustRun();
            }
          }
        }catch (ProcessFailedException $exception){
          $this->error($exception->getMessage());
        }
        catch (RuntimeException $exception){
          $this->error($exception->getMessage());
          $this->error("colocar em fila e tentar novamente");
        }
      }
      (new Process(['npm','set','progress=true']))->run();
      $bar->finish();
      /** fim do processo de instalação de pacotes */
    }
}
