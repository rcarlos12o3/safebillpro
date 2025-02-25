<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Store\Helpers\StorageHelper;

class CertificateQzTrayController extends Controller
{
    public function record()
    {
        $certificates_qztray = Company::selectCertificateQzTray()->get();

        return [
            'record' => $certificates_qztray
        ];
    }

    public function uploadFileQzTray(Request $request)
    {

        try {
            $company = Company::active();
            if ($request->hasFile('digital_qztray')) {
                # code...
                $file_digital = $request->file('digital_qztray');
                if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'. DIRECTORY_SEPARATOR . 'qztray'))) {
                    Storage::disk('local')->makeDirectory('certificates'. DIRECTORY_SEPARATOR. 'qztray');
                }
                $name_digital = "digital_certificate_" . $company->number .".". $file_digital->getClientOriginalExtension();
                file_put_contents(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . 'qztray'. DIRECTORY_SEPARATOR . $name_digital), file_get_contents($file_digital->getPathname()) );

                $company->digital_certificate_qztray = $name_digital;
                $company->save();

                return [
                    'success' => true,
                    'type' => 'digital_qztray',
                    'name' => $name_digital,
                    'message' =>  __('app.actions.upload.success'),
                ];
            } else if ($request->hasFile('private_qztray')) {
                # code...
                $file_private = $request->file('private_qztray');
                $name_private = "private_certificate_" . $company->number. "." . $file_private->getClientOriginalExtension();
                if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'. DIRECTORY_SEPARATOR . 'qztray'))) {
                    Storage::disk('local')->makeDirectory('certificates'. DIRECTORY_SEPARATOR. 'qztray');
                }
                file_put_contents(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . 'qztray'. DIRECTORY_SEPARATOR . $name_private), file_get_contents($file_private->getPathname()));

                $company->private_certificate_qztray = $name_private;
                $company->save();
                return [
                    'success' => true,
                    'type' =>'private_qztray', 
                    'name'=> $name_private,
                    'message' =>  __('app.actions.upload.success'),
                ];
            }
        } catch (\Throwable $error) {
            return response([
                'success' => false,
                'message' =>  $error->getMessage(),
            ], 400);
        }
    }

    public function destroy()
    {
        $company = Company::active();
        if (Storage::disk('local')->exists('certificates\\qztray\\'.$company->private_certificate_qztray)) {
            Storage::disk('local')->delete('certificates\\qztray\\'.$company->private_certificate_qztray);
            $company->private_certificate_qztray = null;
        }
        if (Storage::disk('local')->exists('certificates\\qztray\\'.$company->digital_certificate_qztray)) {
            Storage::disk('local')->delete('certificates\\qztray\\'.$company->digital_certificate_qztray);
            $company->digital_certificate_qztray = null;
        }

        $company->save();

        return [
            'success' => true,
            'message' => 'Certificados de Qz Tray eliminado con éxito'
        ];
    }

    public function private()
    {
        return (new StorageHelper())->contentCertificaQzPrivate();
    }

    public function digital()
    {
        return (new StorageHelper())->contentCertificaQzDigital();
    }
}

