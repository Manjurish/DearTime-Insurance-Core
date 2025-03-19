<?php     

namespace App\Console\Commands;

use Aws\Laravel\AwsServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;


class UpdateTemplateDocx extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-docx';

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
     * @return int
     */
    public function handle()
    {
        $path = resource_path('documents');
        $s3 = App::make('aws')->createClient('s3');
        $status = $s3->uploadDirectory($path, env('AWS_BUCKET'));
        return 0;
    }
}
