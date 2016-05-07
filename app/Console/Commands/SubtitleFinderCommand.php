<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SubtitleFinderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sub:find {imdb_id} {lang}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $imdb_id = $this->argument('imdb_id'); 
        $lang = $this->argument('lang');

        $output = shell_exec('node '.__DIR__.'/findsub.js '.$imdb_id." ".$lang);
        $data = json_decode($output,true);
        if(empty($data)) {
            $this->error('erreur output '.$output);
            return false;
        }

        $this->info($output);

        return true;
    }
}
