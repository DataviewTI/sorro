<?php
namespace Dataview\Sorro\Console;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Dataview\Sorro\Sorro;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Str;

class SorroServiceInstallCmd extends Command
{
    protected $signature = "";
    protected $description = "";
    public function __construct($param){
      $this->param = (object) $param;
      $this->service = Str::slug($this->param->service);
      $this->signature = "sorro-{$this->service}:install";
      $this->description = "Instalação do serviço {$this->param->service} para Sorro Dashboard";
      parent::__construct();
    }

    public function handle()
    {
      $s = Str::slug($this->param->service);
      
      $this->line('Publicando arquivos...');
        
      Sorro::installMessages($this);

      Artisan::call('vendor:publish', [
          '--provider' => $this->param->provider
      ]);

      if(!Schema::hasTable(Str::plural($s))){
        $this->line("Executando migrações {$s} service...");
        Artisan::call('migrate', [
          '--path' => "vendor/dataview/sorro-{$s}/src/database/migrations",
        ]);
      }
      
      Sorro::installMessages($this);

      $this->line('registrando serviço...');
      
      Artisan::call('db:seed', [
        '--class' => $this->param->seeder
      ]);
    
      
      /** Processo de instalação individual de pacotes via PNPM via package.json->IODependencies */
      $pkg = json_decode(file_get_contents($this->param->provider::pkgAddr('/assets/package.json')),true);



      (new Process(['npm','set','progress=false']))->run();
      
      $this->comment("Instalando npm package {$pkg['name']}@{$pkg['version']}");

      try{
        (new Process(['npm', 'install',"vendor/dataview/io{$this->param->service}/src/assets", '--save']))->setTimeout(3600)->run();
      }
      catch(ProcessFailedException $exception){
        $this->error($exception->getMessage());
      }


      $this->line('Instalando dependencias...');
      $depPrefix = "SRdependencies";

      $bar = $this->output->createProgressBar(count($pkg[$depPrefix])+1);

      foreach($pkg[$depPrefix] as $key => $value){
        //checa se já existe e é a mesma versão
        $_oldpkg = null;
        if(File::isDirectory(base_path("node_modules/{$key}"))){
          $_oldpkg = json_decode(file_get_contents("node_modules/{$key}/package.json"));
        }

        try{
          $bar->advance();
          if($_oldpkg==null){
            $this->comment(" instalando {$key}@{$pkg[$depPrefix][$key]}");
            (new Process(['npm','i',"{$key}@{$pkg[$depPrefix][$key]}",'--save']))->setTimeout(3600)->mustRun();
          }
          else{ 
            $old_version = preg_replace("/[^0-9]/", "",$_oldpkg->version);
            $new_version = preg_replace("/[^0-9]/", "",$pkg[$depPrefix][$key]);
            if($old_version == $new_version)
              $this->comment(" em cache {$key}@{$pkg[$depPrefix][$key]}");
            else{
              $this->comment(" atualizando {$key}@{$_oldpkg->version} para {$pkg[$depPrefix][$key]}");
              (new Process(['npm','install',"{$key}@{$pkg[$depPrefix][$key]}",'--save']))->setTimeout(3600)->mustRun();
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

      $this->info(" Sorro Dashbord - {$s} Instalado com sucesso!");
  }
}
