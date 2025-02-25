<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/purchase-quotations">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-bag"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304z" /><path d="M9 11v-5a3 3 0 0 1 6 0v5" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Solicitar cotización ss</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <a :href="`/${resource}/create`" class="btn btn-custom btn-sm  mt-2 mr-2"><i class="fa fa-plus-circle"></i> Nuevo</a>
            </div>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <div class="data-table-visible-columns"> 
            </div>
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <!-- <th>#</th> -->
                        <th class="text-left">Fecha Emisión</th>
                        <th>Estado</th>
                        <th>Documento</th> 
                        <th class="text-center">Descarga</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                    <tr slot-scope="{ index, row }" >
                        <!-- <td>{{ index }}</td> -->
                        <td class="text-left">{{ row.date_of_issue }}</td>
                        <td>{{row.state_type_description}}</td>
                        <td>{{ row.identifier }} 
                        </td>  
                        <td class="text-center"> 

                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickDownload(row.external_id)">PDF</button>
                        </td>
                        
                        <td class="text-right"> 
                                    
                            <button type="button" v-if="!row.has_purchase_orders" class="btn waves-effect waves-light btn-xs btn-success m-1__2"
                                    @click.prevent="clickGenerateOc(row.id)">Generar OC</button>

                            <button type="button" v-if="!row.has_purchase_orders" class="btn waves-effect waves-light btn-xs btn-custom m-1__2"
                                    @click.prevent="clickCreate(row.id)">Editar</button>

                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                    @click.prevent="clickOptions(row.id)">Opciones</button>
                        </td>

                    </tr>
                </data-table>
            </div>
 

            <purchase-quotation-options :showDialog.sync="showDialogOptions"
                              :recordId="recordId"
                              :showGenerate="true"
                              :showClose="true"></purchase-quotation-options>
 
        </div>
    </div>
</template>
<style scoped>
    .anulate_color{
        color:red;
    }
</style>
<style>
@media only screen and (max-width: 485px){
    .filter-container{
      margin-top: 0px;
      & .btn-filter-content, .btn-container-mobile{
        display: flex;
        align-items: center;
        justify-content: start;
      }
    }
}
</style>
<script>
 
    import PurchaseQuotationOptions from './partials/options.vue'
    import DataTable from '../../../../../../../resources/js/components/DataTable.vue'
    // import {deletable} from '../../../mixins/deletable'

    export default { 
        // mixins: [deletable],
        components: {DataTable,PurchaseQuotationOptions},
        data() {
            return { 
                resource: 'purchase-quotations',
                recordId: null,
                showDialogOptions: false,
            }
        },
        created() {
        },
        methods: { 
            clickCreate(id = '') {
                location.href = `/${this.resource}/create/${id}`
            },
            clickGenerateOc(id = '') {
                location.href = `/purchase-orders/generate/${id}`
            },
            clickDownload(external_id) {
                window.open(`/${this.resource}/download/${external_id}`, '_blank');                
            },
            clickOptions(recordId = null) {
                this.recordId = recordId
                this.showDialogOptions = true
            },  
        }
    }
</script>
