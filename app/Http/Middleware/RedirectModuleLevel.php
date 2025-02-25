<?php

    namespace App\Http\Middleware;

    use App\Models\Tenant\User;
    use Closure;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Modules\LevelAccess\Models\ModuleLevel;
    use Modules\LevelAccess\Traits\SystemActivityTrait;

    /**
     * Class RedirectModuleLevel
     * Debe aplicarse el middleware ->middleware('redirect.level'); a la ruta
     * Controla los niveles de acceso desde el modulo de administracion.
     *
     * @package App\Http\Middleware
     */
    class RedirectModuleLevel
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

            /** @var User $user */
            $user = $request->user();
            $level = $user->getLevel();
            $path = explode('/', $request->path());
            $levels = $user->getLevels();
            $this->route_path = $request->path();

            if (!$request->ajax()) {

                if (count($levels) != 0) {
                    // dd("w");

                    /** Se comenta el limite para poder aceptar todos los filtros cuando se añadan,
                     * tambien el superior es diferente a 0 para que evalue cuando existan niveles de module_levels
                     */
                    //if (count($levels) < 72) {
                    // dd($levels);

                    $group = $this->getGroup($path, $level);
                    // dd($group);

                    if ($group) {
                        if ($this->getLevelByGroup($levels, $group) === 0) {
                            $this->fixPermissions($level, $path);
                            return $this->redirectRoute($level);
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
         * @return string|null
         */
        private function getGroup($path, $module)
        {

            ///* Module Documents */
            // dd($path[1]);
            $group = null;
            $firstLevel = $path[0] ?? null;
            $secondLevel = $path[1] ?? null;

            if (isset($path[1])) {

                if ($path[0] == "documents" && $path[1] == "create") {
                    $group = "new_document";
                } else {
                    if ($path[0] == "documents" && $path[1] == "not-sent") {
                        $group = "document_not_sent";
                    }else {
                        if ($path[0] == "documents" && $path[1] == "regularize_shipping") {
                            $group = "regularize_shipping";
                        }else {
                            if ($path[0] == "persons" && $path[1] == "customers") {
                                $group = "catalogs";
                            } else {
                                if ($path[0] == "quotations" && $path[1] == "create") {
                                    $group = "quotations";
                                } else {
                                    if ($path[0] == "quotations" && $path[1] == "edit") {
                                        $group = "quotations";
                                    } else {
                                        if ($path[0] == "sale-notes" && $path[1] == "create") {
                                            $group = "sale_notes";
                                        } else {
                                            if ($path[0] == "contracts" && $path[1] == "create") {
                                                $group = "contracts";
                                            } else {
                                                if ($path[0] == "sale-opportunities" && $path[1] == "create") {
                                                    $group = "sale-opportunity";
                                                } else {
                                                    if ($path[0] == "order-notes" && $path[1] == "create") {
                                                        $group = "order-note";
                                                    } else {
                                                        if ($path[0] == "sire" && $path[1] == "sale") {
                                                            $group = "account_summary";
                                                        } else {
                                                            if ($path[0] == "sire" && $path[1] == "purchase") {
                                                                $group = "account_summary";
                                                            }else{
                                                                if ($firstLevel == "ecommerce" && $secondLevel == "item-sets") {
                                                                    $group = "ecommerce_items";
                                                                }else{
                                                                    if ($firstLevel == "full_suscription") {
                                                                        if ($secondLevel == "client") {
                                                                            $group = "suscription_app_client";
                                                                        } elseif ($secondLevel == "service") {
                                                                            $group = "suscription_app_service";
                                                                        } elseif ($secondLevel == "payment_receipt") {
                                                                            $group = "suscription_app_payments";
                                                                        } elseif ($secondLevel == "plans") {
                                                                            $group = "suscription_app_plans";
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                /** Configuracion avanzada */
                if (
                    ($firstLevel == "company_accounts" && $secondLevel == 'create') ||
                    ($firstLevel == "inventories" && $secondLevel == 'configuration') ||
                    ($firstLevel == "configurations" && $secondLevel == 'sale-notes')
                ) {
                    $group = "configuration_advance";
                }
                /** Giro de negocio */
                if (
                    ($firstLevel == "companies" && $secondLevel == 'create')

                ) {
                    $group = "configuration_company";
                }

            } else {
                /** Documentos */
                if ($path[0] == "documents") {
                    $group = "list_document";
                } elseif ($path[0] == "contingencies") {
                    $group = "document_contingengy";
                } elseif (in_array($path[0], ["items", "brands", "item-sets"])) {
                    $group = "items";
                } elseif (in_array($path[0], ["categories"])) {
                    // $group = "catalogs";
                    $group = "items";
                } elseif (in_array($path[0], ["summaries", "voided"])) {
                    $group = "summary_voided";
                } elseif ($path[0] == "quotations") {
                    $group = "quotations";
                } elseif ($path[0] == "sale-notes") {
                    $group = "sale_notes";
                } elseif (in_array($path[0], ["incentives", "user-commissions"])) {
                    $group = "incentives";
                } elseif ($path[0] == "sale-opportunities") {
                    $group = "sale-opportunity";
                } elseif (in_array($path[0], ["contracts", "production-orders"])) {
                    $group = "contracts";
                } elseif ($path[0] == "order-notes") {
                    $group = "order-note";
                } elseif ($path[0] == "technical-services") {
                    $group = "technical-service";
                } elseif ($path[0] == "purchase-orders") {
                    $group = "purchases_orders";
                } elseif ($path[0] == "digemid") {
                    $group = "digemid";
                } elseif ($path[0] == "cash") {
                    $group = "cash";
                } elseif ($path[0] == "dispatchers") {
                    $group = "dispatchers";
                } elseif ($path[0] == "drivers") {
                    $group = "drivers";
                } elseif ($path[0] == "transports") {
                    $group = "transports";
                } elseif ($path[0] == "bank_loan") {
                    $group = "bank_loan";
                } elseif ($path[0] == "purchase-settlements") {
                    $group = "advanced_purchase_settlements";
                } elseif ($path[0] == "order-forms") {
                    $group = "advanced_order_forms";
                } elseif ($path[0] == "accounting_ledger") {
                    $group = "account_summary";
                } elseif ($path[0] == "pos") {
                    $group = "pos";
                } else {
                    $group = null;
                }
                /** Configuracion Avanzada */
                if (
                    $firstLevel == "tasks" ||
                    $firstLevel == "offline-configurations" ||
                    $firstLevel == "series-configurations"
                ) {
                    $group = "configuration_advance";
                } /** Giro de negocio */
                elseif (
                    $firstLevel == "bussiness_turns" ||
                    $firstLevel == "advanced"
                ) {
                    $group = "configuration_company";
                } /** Giro de negocio */
                elseif ($firstLevel == "login-page") {
                    $group = "configuration_visual";
                }
            }
            return $group;
        }

        /**
         * @param Collection $levels
         * @param string     $group
         *
         * @return int
         */
        private function getLevelByGroup($levels, $group)
        {
            /** @var Collection $levels_x_group */
            $levels_x_group = $levels->filter(function ($module, $key) use ($group) {
                /** @var ModuleLevel $module */
                return $module->value === $group;
            });

            return $levels_x_group->count();
        }

        /**
         * Bajo ciertas circunstancias, $group se genera como new_document, este ajuste evalua el valor para nuevos
         * componentes.
         * configuration_advance
         * configuration_company
         * configuration_visual
         *
         * @param string $group
         * @param array  $path
         */
        private function fixPermissions(&$group, $path = [])
        {

            $firstLevel = $path[0] ?? null;
            $secondLevel = $path[1] ?? null;
            /** Configuracion avanzada */
            if (
                ($firstLevel == "company_accounts" && $secondLevel == 'create') ||
                ($firstLevel == "inventories" && $secondLevel == 'configuration') ||
                ($firstLevel == "configurations" && $secondLevel == 'sale-notes')
            ) {
                $group = "configuration_advance";
            } /** Giro de negocio */
            elseif (
                ($firstLevel == "companies" && $secondLevel == 'create')

            ) {
                $group = "configuration_company";
            } /** Configuracion Avanzada */
            elseif (
                $firstLevel == "tasks" ||
                $firstLevel == "offline-configurations" ||
                $firstLevel == "series-configurations"
            ) {
                $group = "configuration_advance";
            } /** Giro de negocio */
            elseif (
                $firstLevel == "bussiness_turns" ||
                $firstLevel == "advanced"
            ) {
                $group = "configuration_company";
            } /** Giro de negocio */
            elseif ($firstLevel == "login-page") {
                $group = "configuration_visual";
            }

        }

        /**
         * @param $level
         *
         * @return RedirectResponse
         */
        private function redirectRoute($level)
        {
            // registrar log de actividades cuando el usuario no tiene permiso
            $this->saveGeneralSystemActivity(auth()->user(), 'level_module_access_error', $this->route_path);

            switch ($level) {

                case 'new_document':
                    return redirect()->route('tenant.documents.create');

                case 'list_document':
                    return redirect()->route('tenant.documents.index');

                case 'document_not_sent':
                    return redirect()->route('tenant.documents.not_sent');

                case 'document_contingengy':
                    return redirect()->route('tenant.contingencies.index');

                case 'items':
                    return redirect()->route('tenant.items.index');

                case 'summary_voided':
                    return redirect()->route('tenant.summaries.create');

                case 'quotations':
                    return redirect()->route('tenant.quotations.create');

                case 'sale_notes':
                    return redirect()->route('tenant.sale_notes.create');

                case 'incentives':
                    return redirect()->route('tenant.incentives.create');

                case 'sale-opportunity':
                    return redirect()->route('tenant.sale_opportunities.index');

                case 'contracts':
                    return redirect()->route('tenant.contracts.index');

                case 'order-note':
                    return redirect()->route('tenant.order_notes.index');

                case 'technical-services':
                    return redirect()->route('tenant.technical_services.index');

                case 'purchases_orders':
                    return redirect()->route('tenant.purchase-orders.index');
                case 'digemid':
                    return redirect()->route('tenant.digemid.index');
                case 'cash':
                    return redirect()->route('tenant.cash.index');
                case 'dispatchers':
                    return redirect()->route('tenant.dispatchers.index');
                case 'drivers':
                    return redirect()->route('tenant.drivers.index');
                case 'transports':
                    return redirect()->route('tenant.transports.index');
                case 'bank_loan':
                    return redirect()->route('tenant.bank_loan.index');
                case 'regularize_shipping':
                    return redirect()->route('tenant.documents.regularize_shipping');
                case 'advanced_purchase_settlements':
                    return redirect()->route('tenant.purchase-settlements.index');
                case 'advanced_order_forms':
                    return redirect()->route('tenant.order_forms.index');
                case 'account_summary':
                    return redirect()->route('tenant.accounting_ledger.index');
                case 'pos':
                    return redirect()->route('tenant.pos.index');
                case 'configuration_visual':
                case 'configuration_advance':
                case 'configuration_company':
                    //'configuration_visual' 'configuration_advance' 'configuration_company' redirecciona a configuracion
                    return redirect()->route('tenant.general_configuration.index');

                case  "suscription_app_client":
                case  "suscription_app_service":
                case  "suscription_app_payments":
                case  "suscription_app_plans":
                return redirect()->route('tenant.suscription.client.index');
                    //return redirect()->route('tenant.suscription.service.index');
                    //return redirect()->route('tenant.suscription.payments.index');
                    //return redirect()->route('tenant.suscription.plans.index');
                case 'ecommerce_items':
                    return redirect()->route('tenant.ecommerce.item_sets.index');
                    
                default;
                    return redirect()->route('tenant.dashboard.index');


            }
        }

    }
