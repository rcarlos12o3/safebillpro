<?php

namespace App\Http\Controllers\System;

use Config;
use Artisan;
use DateTime;
use Exception;
use App\Traits\BackupTrait;
use Illuminate\Http\Request;
use App\Models\System\Client;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Models\Hostname;
use App\Http\Controllers\Controller;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\BufferedOutput;
use Illuminate\Support\Facades\File;

class DemoEnvironmentController extends Controller
{

    use BackupTrait;

    public function create(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'name' => 'required',
        ]);

        $database = '';

        $client = Client::findOrFail($request->client_id);
        $hostname = Hostname::findOrFail($client->hostname_id);
        $website = Website::findOrFail($hostname->website_id);
        $database = $website->uuid;

        $output = new BufferedOutput();

        $status = Artisan::call('demobk:dbcreate', [
            'database' => $database,
            'new_database' => $request->name,
            'client_id' => $client->id,
        ], $output);

        $message = $output->fetch();

        return response()->json([
            'success' => $status === 0,
            'message' => $message,
        ]);
    }

    public function getFiles()
    {
        
        $response = [];
        $responseDemo = [];
        $responseSystem = [];
        $pathdemo = storage_path('app/demo_backups/demo');
        $pathsystem = storage_path('app/demo_backups/system');;

        if (File::exists($pathdemo)) {
            $files = glob($pathdemo . '/*.sql');

            $responseDemo = array_map(function ($file) {
                return [
                    'type' => 'demo',
                    'name' => pathinfo($file, PATHINFO_FILENAME),
                ];
            }, $files);
        }

        if (File::exists($pathsystem)) {

            $files = glob($pathsystem . '/*.sql');

            $responseSystem = array_map(function ($file) {
                return [
                    'type' => 'sistema',
                    'name' => pathinfo($file, PATHINFO_FILENAME),
                ];
            }, $files);
        }

        $response = array_merge($responseDemo, $responseSystem);

        return [
            'success' => true,
            'message' => 'Listado de archivos',
            'data' => $response
        ];

    }

    public function restore(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'restore_dbname_bkdemo' => 'required',
            'restore_type_bkdemo' => 'required',
        ]);

        $database = '';

        $client = Client::findOrFail($request->client_id);
        $hostname = Hostname::findOrFail($client->hostname_id);
        $website = Website::findOrFail($hostname->website_id);
        $database = $website->uuid;
        $client->restore_dbname_bkdemo = $request->restore_dbname_bkdemo;
        $client->restore_type_bkdemo = $request->restore_type_bkdemo;
        $client->save();

        $output = new BufferedOutput();

        $status = Artisan::call('demobk:dbrestore', [
            'database' => $database,
            'restore_dbname' => $request->restore_dbname_bkdemo,
            'restore_type' => $request->restore_type_bkdemo,
        ], $output);

        $message = $output->fetch();

        return response()->json([
            'success' => $status === 0,
            'message' => $message,
        ]);
    }

    public function enableCron(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'enabled_cron_restore_bkdemo' => 'required'
        ]);

        $client = Client::findOrFail($request->client_id);
        $client->enabled_cron_restore_bkdemo = $request->enabled_cron_restore_bkdemo;
        $client->save();

        return response()->json([
            'success' => true,
            'message' => 'Opción actualizada con éxito',
        ]);
    }

    public function client($client_id)
    {
        $client = Client::find($client_id);

        return [
            'success' => true,
            'data' =>[
                'enabled_cron_restore_bkdemo' => (bool)$client->enabled_cron_restore_bkdemo,
                'restore_dbname_bkdemo' => $client->restore_dbname_bkdemo,
                'restore_type_bkdemo' => $client->restore_type_bkdemo
            ]
        ];

    }

}
