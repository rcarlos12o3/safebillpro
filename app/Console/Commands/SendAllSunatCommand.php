<?php

namespace App\Console\Commands;

use Facades\App\Http\Controllers\Tenant\DocumentController;
use Illuminate\Console\Command;
use App\Traits\CommandTrait;
use App\Models\Tenant\{
    Configuration,
    Document
};

class SendAllSunatCommand extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'online:send-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all pending documents to be sent to the Sunat';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        if (true) { //if (Configuration::firstOrFail()->cron) {
    $tenantInfo = 'Unknown tenant';
    try {
        $company = \App\Models\Tenant\Company::first();
        $tenantInfo = $company ? $company->number . ' - ' . $company->name : 'No company found';
    } catch (\Exception $e) {
        $tenantInfo = 'Error getting tenant info';
    }
    
    \Log::info("=== ONLINE SEND-ALL START === Tenant: {$tenantInfo}");
    $this->info("Processing tenant: {$tenantInfo}");
    
    if ($this->isOffline()) {
        \Log::info("Offline service enabled - skipping for: {$tenantInfo}");
        $this->info('Offline service is enabled');
        return;
    }

            $documents = Document::query()
                ->where('group_id', '01')
                ->where('send_server', 0)
                ->whereIn('state_type_id', ['01','03'])
                // ->orWhere('sunat_shipping_status', '!=', '')
                ->where('success_sunat_shipping_status', false)
                ->get();

	    \Log::info("Documents found for SUNAT sending: {$documents->count()} - Tenant: {$tenantInfo}");
	    $this->info("Tenant {$tenantInfo}: Documents found: {$documents->count()}");

            foreach ($documents as $document) {
                \Log::info('Processing document: ' . $document->id);
		try {
                    $response = DocumentController::send($document->id);

                    // Log response for debugging
                    \Log::info('SUNAT Response for document ' . $document->id . ': ' . json_encode($response));

                    $responseJson = json_encode($response);
                    $document->sunat_shipping_status = $responseJson;

                    // Check for known error messages that should trigger retry
                    $shouldRetry = false;
                    $errorPatterns = [
                        'Bad Request',
                        'looks like we got no XML document',
                        'looks like',
                    ];

                    foreach ($errorPatterns as $pattern) {
                        if (stripos($responseJson, $pattern) !== false) {
                            \Log::warning("Document {$document->id} contains error pattern: {$pattern} - marking for retry");
                            $shouldRetry = true;
                            break;
                        }
                    }

                    // Mark as success only if no error patterns found
                    $document->success_sunat_shipping_status = !$shouldRetry;
                    $document->save();
                }
                catch (\Exception $e) {

                    $document->success_sunat_shipping_status = false;

                    $document->sunat_shipping_status = json_encode([
                        'sucess' => false,
                        'message' => $e->getMessage(),
                        'payload' => $e
                    ]);

                    $document->save();

                    \Log::error('Exception processing document ' . $document->id . ': ' . $e->getMessage());
                }
            }
        }
        else {
            $this->info('The crontab is disabled');
        }

        $this->info('The command is finished');
    }
}
