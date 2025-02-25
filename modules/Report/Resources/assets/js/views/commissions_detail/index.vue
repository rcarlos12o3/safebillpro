<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/list-reports">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-analytics"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17l0 -5" /><path d="M12 17l0 -1" /><path d="M15 17l0 -3" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Utilidades detallado
                    <el-tooltip class="item" effect="dark" content="Total ventas (CPE - NV) - Total compras" placement="top-end">
                        <i class="fa fa-info-circle"></i>
                    </el-tooltip>
                </span></li>
            </ol>
        </div>
        <div class="card mb-0 pt-2 pt-md-0 tab-content-default row-new">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Utilidades detallado
                    <el-tooltip class="item" effect="dark" content="Total ventas (CPE - NV) - Total compras" placement="top-end">
                        <i class="fa fa-info-circle"></i>
                    </el-tooltip>
                </h3>
            </div> -->
            <div class="card mb-0">
                    <div class="card-body">
                        <data-table :resource="resource">
                            <tr slot="heading">
                                <th>#</th>
                                <th>Fecha</th>
                                <th class="text-center">Comprobante</th>
                                <th class="text-center">Serie</th>
                                <th class="text-center">Ruc/Dni</th>
    
                                <th class="text-center">Comercial</th>
                                <th class="text-center">Detalle</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">
                                    Precio compra
                                    <el-tooltip class="item"
                                                content="Por unidad"
                                                effect="dark"
                                                placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </th>
                                <th class="text-center">
                                    Precio venta
                                    <el-tooltip class="item"
                                                content="Por unidad"
                                                effect="dark"
                                                placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </th>
    
                                <th class="text-center">Ganancia unidad</th>
                                <th class="text-center">Ganancia total</th>
                            </tr>
                            <tr slot-scope="{ index, row }">
                                <td>{{ index }}</td>  
                                <td>{{row.date_of_issue}}</td>
                                <td class="text-center">{{row.type_document}}</td>
                                <td class="text-center">{{row.serie}}</td> 
                                <td class="text-center">{{row.customer_number}}</td> 
    
                                <td class="text-center">{{row.customer_name}}</td> 
                                <td class="text-center">{{row.name}}</td> 
                                <td class="text-center">{{row.quantity}}</td> 
                                <td class="text-center">{{row.purchase_unit_price}}</td> 
                                <td class="text-center">{{row.unit_price}}</td> 
    
                                <td class="text-center">{{row.unit_gain}}</td> 
                                <td class="text-center">{{row.overall_profit}}</td> 
    
                            </tr>
                            
                        </data-table>
                         
                        
                    </div> 
            </div>
     
        </div>
    </div>
</template>

<script>
 
    import DataTable from '../../components/DataTableReportsComissionDetail.vue'

    export default { 
        components: {DataTable},
        data() {
            return {
                resource: 'reports/commissions-detail',                 
                form: {}, 

            }
        },
        async created() { 
        },
        methods: { 
             
            
        }
    }
</script>