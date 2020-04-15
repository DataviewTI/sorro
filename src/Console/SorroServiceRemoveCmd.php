<?php
namespace Dataview\Sorro\Console;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Dataview\Sorro\Sorro;
use Dataview\Sorro\Service;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Str;

class SorroServiceRemoveCmd extends Command
{
  protected $signature = "";
  protected $description = "";

  public function __construct($param){
    $this->param = (object) $param;
    $this->service = Str::slug($this->param->service);
    $this->signature = "io-{$this->service}:remove {--force}";
    $this->description = "Desinstalação do serviço {$this->service} para Sorro Dashboard";
    parent::__construct();
  }
  public function handle()
  {
    $s = Str::slug($this->param->service);
    if(!$this->option('force'))
      $exec = $this->confirm('Tem certeza que deseja remover o serviço? [y|N]');
    else
      $exec = $this->option('force'); 

    if($exec){
      $has_force = filled(optional($this->param)->force) ? $this->param->force : [];
      $tables = $this->option('force') ? array_merge($this->param->tables,$has_force) : $this->param->tables;

      foreach($tables  as $t){
        $this->line('Removendo tabela '.$t);
        \DB::statement("SET FOREIGN_KEY_CHECKS=0");
        Schema::dropIfExists($t);
        \DB::statement("SET FOREIGN_KEY_CHECKS=1");
      }

  
      Sorro::installMessages($this);
      Service::where('alias',$s)->forceDelete();

      foreach($tables as $t){
        $sgl = Str::singular($t);

        $this->line("Removendo migração {$t} - {$sgl}...");
        \DB::table('migrations')
          ->where('migration','like',"%{$t}%")
          ->orWhere('migration','like',"%{$sgl}%")
          ->delete();
      }


        Sorro::installMessages($this,1);
        $this->line('Removendo assets...');
        (new Process(['npm','set','progress=false']))->run();
        try{
          (new Process(['npm','remove','sorro-'.$s]))->setTimeout(36000)->mustRun();
        }catch (ProcessFailedException $exception){
          $this->error($exception->getMessage());
        }

        (new Process(['npm', 'set', 'progress=true']))->run();
        
        $this->info(' ');
        $titleCase = Str::title($s);
        $this->info("Sorro Dashboard - {$titleCase} removido com sucesso!");
    }
    else
      $this->info('Remoção cancelada...');
  }
}
