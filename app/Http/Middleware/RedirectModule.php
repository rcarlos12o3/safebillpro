<?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Modules\LevelAccess\Traits\SystemActivityTrait;

    /**
     * Class RedirectModule
     *
     * @package App\Http\Middleware
     */
    class RedirectModule
    {

        use SystemActivityTrait;

        private $route_path;

        /**
         * Handle an incoming request.
         *
         * @param Request $request
         * @param Closure $next
         *
         * @return mixed
         */
        public function handle($request, Closure $next)
        {

            $module = $request->user()->getModule();
            $path = explode('/', $request->path());
            $modules = $request->user()->getModules();
            $this->route_path = $request->path();


            if (!$request->ajax()) {

                if (count($modules)) {
                    // if(count($modules) < 15){

                    $group = $this->getGroup($path, $module);
                    if ($group) {
                        if ($this->getModuleByGroup($modules, $group) === 0) {
 
                            return $this->redirectRoute($module);
                        }
                    }

                    // }

                }
            }

            return $next($request);

        }

        /**
         * @param $path
         * @param $module
         *
         * @return string
         */
        private function getGroup($path, $module)
        {

            $firstLevel = $path[0] ?? null;
            $secondLevel = $path[1] ?? null;
            $group = null;
            //dd($firstLevel);
            ///* Module Documents */
            if (
                $firstLevel == "pos" ||
                $firstLevel == "documents" ||
                $firstLevel == "dashboard" ||
                // $firstLevel == "items" ||
                $firstLevel == "summaries" ||
                $firstLevel == "voided") {
                $group = "documents";
            } ///* Module purchases  */
            elseif (
                $firstLevel == "bank_loan" ||
                $firstLevel == "purchases" ||
                $firstLevel == "expenses") {
                $group = "purchases";
            } ///* Module advanced */
            elseif (
                $firstLevel == "retentions" ||
                $firstLevel == "dispatches" ||
                $firstLevel == "dispatch_carrier" ||
                $firstLevel == "perceptions") {
                $group = "advanced";
            } ///* Module reports */
            elseif (
                $firstLevel == "list-reports" ||
                ($firstLevel == "reports" && $secondLevel == "purchases") ||
                ($firstLevel == "reports" && $secondLevel == "sales") ||
                ($firstLevel == "reports" && in_array($secondLevel, [
                    "items",
                    "validate-documents",
                    "extra-general-items",
                    "general-items",
                    "quotations",
                    "document-detractions",
                    "sales-consolidated",
                    "tips",
                    "user-commissions",
                    "state_account",
                    "comissions",
                    "commissions-detail",
                    "order-notes-general",
                    "guides",
                    "consistency-documents"
                ])) ) {
                $group = "reports";
            } // cuenta / listado de pagos
            elseif (
                $firstLevel == "cuenta") {
                $group = "cuenta";
            } ///* Module configuration */
            elseif (
                $firstLevel == "users" ||
                $firstLevel == "establishments"||
                in_array($firstLevel, ['list-platforms', 'list-cards', 'list-currencies', 'list-bank-accounts', 'list-banks', 'list-attributes', 'list-detractions', 'list-units', 'list-payment-methods', 'list-incomes', 'list-payments', 'company_accounts', 'list-vouchers-type',     'companies', 'advanced', 'tasks', 'inventories','bussiness_turns','offline-configurations','series-configurations','configurations', 'login-page', 'list-settings'])) {
                $group = "establishments";
                // $group = "configuration";
            }//
            elseif (
                $firstLevel == "companies") {
                $group = "configuration";
                if (count($path) > 0 && $secondLevel == "uploads" && $module == "documents") {
                    $group = "documents";
                }
            }//
            elseif (
                $firstLevel == "catalogs" ||
                $firstLevel == "advanced") {
                $group = "configuration";
            } ///* Determinate type person */
            elseif (
                $firstLevel == "persons") {
                if ($secondLevel == "suppliers") {
                    $group = "purchases";
                }//
                elseif ($secondLevel == "customers") {
                    $group = "persons";
                } else {
                    $group = null;
                }
            }//
            elseif (
                $firstLevel == "person-types") {
                $group = "persons";
            } ///* Module pos */
            // elseif (
            //     $firstLevel == "pos" ||
            //     $firstLevel == "cash") {
            //     $group = "pos";
            // } ///* Module inventory */
            elseif (
                $firstLevel == "warehouses"||
                $firstLevel == "inventory" ||
                ($firstLevel == "reports" && $secondLevel == "kardex") ||
                ($firstLevel == "reports" && $secondLevel == "inventory")) {
                $group = "inventory";
            } ///* Module accounting */
            elseif (
                $firstLevel == "account") {
                $group = "accounting";
            } ///* Module finance */
            elseif (
                $firstLevel == "cash" ||
                $firstLevel == "finances") {
                $group = "finance";
            }//
            elseif (
                $firstLevel == "orders" ||
                ($firstLevel == "ecommerce" && $secondLevel == "configuration") ||
                $firstLevel == "items_ecommerce" ||
                $firstLevel == "tags" ||
                $firstLevel == "promotions") {
                $group = "ecommerce";
            }//
            elseif (
                $firstLevel == "hotels" ||
                ($firstLevel == "hotels" && $secondLevel == "document-hotels")) {
                $group = "hotels";
            }//
            elseif (
                $firstLevel == "documentary-procedure") {
                $group = "documentary-procedure";
            }//
            elseif (
                $firstLevel == "digemid") {
                $group = "digemid";
            }//
            elseif (
                $firstLevel == "full_suscription") {
                $group = "suscription_app";
            }
            else if($this->existLevelInModules($firstLevel, ['items']))
            {
                $group = 'items';
            }elseif ($firstLevel == "dispatchers") {
                $group = "dispatchers";
            }elseif ($firstLevel == "drivers") {
                $group = "drivers";
            }elseif ($firstLevel == "transports") {
                $group = "transports";
            }elseif ($firstLevel == "origin_addresses") {
                $group = "origin_addresses";
            }elseif ($firstLevel == "advanced_purchase_settlements") { 
                $group = "advanced_purchase_settlements";
            }elseif ($firstLevel == "advanced_order_forms") {
                $group = "advanced_order_forms";
            }elseif ($firstLevel == "account_summary") {
                $group = "account_summary";
            }elseif ($firstLevel == "ecommerce_items") {
                $group = "ecommerce_items";
            }elseif ($firstLevel == "restaurant" || 
            ($firstLevel == "restaurant" && $secondLevel == "list")|| 
            ($firstLevel == "restaurant" && $secondLevel == "orders")||
            ($firstLevel == "restaurant" && $secondLevel == "promotions")||
            ($firstLevel == "restaurant" && $secondLevel == "configuration")) {
                $group = "restaurant_app";
            }elseif ($firstLevel == "suscription") {
                $group = "full_suscription_app";
            }elseif ($firstLevel == "production"
            || $firstLevel == "machine-production"
            || $firstLevel == "packaging"
            || $firstLevel == "mill-production"
            || $firstLevel == "machine-type-production"
            || $firstLevel == "workers") {
                $group = "production_app";
            }elseif ($firstLevel == "live-app") {
                $group = "apps";
            }elseif ($firstLevel == "list-extras") {
                $group = "app_2_generator";
            }elseif ($firstLevel == "quotations") {
                $group = "preventa";
            }
            return $group;
        }

        
        /**
         *
         * @param  string $level
         * @param  array $options
         * @return bool
         */
        private function existLevelInModules($level, $options)
        {
            return in_array($level, $options);
        }
        

        /**
         * @param $modules
         * @param $group
         *
         * @return mixed
         */
        private function getModuleByGroup($modules, $group)
        {

            $modules_x_group = $modules->filter(function ($module, $key) use ($group) {
                return $module->value === $group;
            });

            return $modules_x_group->count();
        }

        /**
         * @param $module
         *
         * @return RedirectResponse
         */
        private function redirectRoute($module)
        {
            // registrar log de actividades cuando el usuario no tiene permiso al modulo
            $this->saveGeneralSystemActivity(auth()->user(), 'module_access_error', $this->route_path);
            //dd($module);
            switch ($module) {

                case 'documents':
                    return redirect()->route('tenant.documents.create');

                case 'purchases':
                    return redirect()->route('tenant.purchases.index');

                case 'advanced':
                    return redirect()->route('tenant.retentions.index');

                case 'reports':
                    return redirect()->route('tenant.reports.purchases.index');

                case 'configuration':
                    return redirect()->route('tenant.companies.create');

                case 'inventory':
                    return redirect()->route('inventory.index');
                    // return redirect()->route('warehouses.index');

                case 'accounting':
                    return redirect()->route('tenant.account.index');

                case 'finance':
                    return redirect()->route('tenant.finances.global_payments.index');

                case 'establishments':
                    return redirect()->route('tenant.users.index');

                case 'documentary-procedure':
                case 'hotels':
                    return redirect()->route('tenant.hotels.index');
                case 'digemid':
                    return redirect()->route('tenant.digemid.index');
                case 'suscription_app':
                    return redirect()->route('tenant.fullsuscription.client.index');
                case 'full_suscription_app':
                    return redirect()->route('tenant.suscription.client.index');
                case 'dispatchers':
                    return redirect()->route('tenant.dispatchers.index');
                case 'drivers':
                    return redirect()->route('tenant.drivers.index');
                case 'transports':
                    return redirect()->route('tenant.transports.index');
                case 'advanced_purchase_settlements':
                    return redirect()->route('tenant.purchase-settlements.index');
                case 'advanced_order_forms':
                    return redirect()->route('tenant.order_forms.index');
                case 'account_summary':
                    return redirect()->route('tenant.accounting_ledger.index');
                case 'restaurant_app':
                    return redirect()->route('tenant.restaurant.menu');
                case 'production_app':
                    return redirect()->route('tenant.production.index');
                case 'apps':
                    return redirect()->route('tenant.liveapp.configuration');
                case 'app_2_generator':
                    return redirect()->route('list-extras');
                default;
                    return redirect()->route('tenant.dashboard.index');
                /*case 'ecommerce':
                    return redirect()->route('tenant.ecommerce.index');*/

            }
        }

    }
