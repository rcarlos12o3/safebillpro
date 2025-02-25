<?php

namespace App\Console\Commands;

use App\Models\Tenant\Item;
use Illuminate\Console\Command;
use Modules\Purchase\Helpers\WeightedAverageCostHelper;


class RegularizeWeightedAverageCostCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'regularize:weighted-average-cost';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regularizar costo ponderado de los productos, primer registro del item.';


    /**
     * Create a new command instance.
     *
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
        $this->info('----------------------------------------------');
        $this->info('Hora de inicio: ' . date('Y-m-d H:i:s'));
        $this->info('Iniciando Proceso');

        $helper = new WeightedAverageCostHelper();

        $items = $helper->getItemsWithoutWeightedCost();
        
        $this->info('Productos encontrados: '. $items->count());

        foreach ($items as $item) 
        {
            $this->info('item: '. $item->description);

            $helper->saveWeightedCostFromItem($item, false);
        }

        $this->info('Finalizando Proceso');

        $this->info('Hora de término: ' . date('Y-m-d H:i:s'));
        $this->info('----------------------------------------------');
    }
}
