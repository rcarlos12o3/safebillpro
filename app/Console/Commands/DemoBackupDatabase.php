<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Ifsnop\Mysqldump as IMysqldump;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Hyn\Tenancy\Environment;
use App\Models\System\Client;

class DemoBackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demobk:dbcreate --database={database} --new_database={new_database} --client_id={client_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demo backup database';

    protected $process;

    protected $host;
    protected $username;
    protected $password;

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
        try {
            $database = $this->argument('database');
            $new_database = $this->argument('new_database');
            $client_id = $this->argument('client_id');

            $this->initDbConfig();

            if (!File::exists(storage_path('app/demo_backups/demo'))) {
                File::makeDirectory(storage_path('app/demo_backups/demo'), 0755, true);
            }

            $tenant_dump = new IMysqldump\Mysqldump(
                'mysql:host=' . $this->host . ';dbname=' . $database, $this->username, $this->password
            );
            $tenant_dump->start(storage_path("app/demo_backups/demo/{$new_database}.sql"));
            Log::info('Backup ' . $database . ' into '. $new_database .' database success');
            
            $this->output->success('Copia de base de datos creada con éxito');

            $this->addImagesZip($client_id , $new_database);

            return 0;

        }catch (Exception $e) {

            Log::error("Backup failed -- Line: {$e->getLine()} - Message: {$e->getMessage()} - File: {$e->getFile()}");

            $this->output->error('Error inesperado: ' . $e->getMessage());
            return 1;
        }

    }


    private function initDbConfig(){

        $dbConfig = config('database.connections.' . config('tenancy.db.system-connection-name', 'system'));

        $this->host = Arr::first(Arr::wrap($dbConfig['host'] ?? ''));
        $this->username = $dbConfig['username'];
        $this->password = $dbConfig['password'];

    }

    private function addImagesZip($client_id , $new_database) {

        $client = Client::findOrFail($client_id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);

        $items = DB::connection('tenant')->table('items')->get(['image', 'image_medium', 'image_small'])->where('image','<>','imagen-no-disponible.jpg');
        $categories = DB::connection('tenant')->table('categories')->get(['image'])->where('image','<>','imagen-no-disponible.jpg');
        $promotions = DB::connection('tenant')->table('promotions')->get(['image'])->where('image','<>','imagen-no-disponible.jpg');
        $company = DB::connection('tenant')->table('companies')->select('logo')->first();
        
        $imagesFolder = storage_path('app/public/uploads/items');
        $categoriesFolder = storage_path('app/public/uploads/categories');
        $promotionsFolder = storage_path('app/public/uploads/promotions');
        $logosFolder = storage_path('app/public/uploads/logos');

        $tempFolder = storage_path('app/demo_backups/temp');
        if (!File::exists($tempFolder)) {
            File::makeDirectory($tempFolder, 0755, true);
        }

        $zipFileName = $new_database . '.zip';
        $zipFilePath = $tempFolder . '/' . $zipFileName;

        $zip = new ZipArchive;

        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {

            $filesAdded = false;

            foreach ($items as $item) {
                foreach (['image', 'image_medium', 'image_small'] as $size) {
                    $imagePath = $imagesFolder . '/' . $item->$size;
                    if (!empty($item->$size) && file_exists($imagePath)) {
                        $zip->addFile($imagePath, "items/" . basename($item->$size));
                        $filesAdded = true;
                    }
                }
            }

            foreach ($categories as $item) {
                $imagePath = $categoriesFolder . '/' . $item->image;
                if (!empty($item->image) && file_exists($imagePath)) {
                    $zip->addFile($imagePath, "categories/" . basename($item->image));
                    $filesAdded = true;
                }
            }

            foreach ($promotions as $item) {
                $imagePath = $promotionsFolder . '/' . $item->image;
                if (!empty($item->image) && file_exists($imagePath)) {
                    $zip->addFile($imagePath, "promotions/" . basename($item->image));
                    $filesAdded = true;
                }
            }

            $imagePath = $logosFolder . '/' . $company->logo;
            if (!empty($company->logo) && file_exists($imagePath)) {
                $zip->addFile($imagePath, "logos/" . basename($company->logo));
                $filesAdded = true;
            }

            if (!$filesAdded) {
                $zip->addFromString('Demo.txt', 'Empty file');
            }

            $zip->close();

            if (file_exists($zipFilePath)) {

                $destinationFolder = 'demo_backups/demo';
                $destinationPath = $destinationFolder . '/' . $zipFileName;

                if (!Storage::exists($destinationFolder)) {
                    Storage::makeDirectory($destinationFolder);
                }

                Storage::putFileAs($destinationFolder, new \Illuminate\Http\File($zipFilePath), $zipFileName);
                File::delete($zipFilePath);

                Log::info('Backup ' . $new_database . ' with images created successfully.');
            } else {
                Log::error('Backup failed: ZIP file not found after creation.');
            }
        } else {
            Log::error("Backup with images failed: Unable to create ZIP file.");
        }

    }

}