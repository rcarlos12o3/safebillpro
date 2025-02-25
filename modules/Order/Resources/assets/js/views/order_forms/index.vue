<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/order-forms">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-clipboard-text"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M9 12h6" /><path d="M9 16h6" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Ordenes de pedido</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <a :href="`/${resource}/create`" class="btn btn-custom btn-sm  mt-2 mr-2"><i class="fa fa-plus-circle"></i> Nuevo</a>
            </div>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <!-- <th>#</th> -->
                        <th class="text-left">Fecha Emisión</th>
                        <th>Cliente</th>
                        <th>Conductor</th>
                        <th>Número</th>
                        <th class="text-center">Fecha Envío</th>
                        <th class="text-center">Descargas</th>
                        <th class="text-center">Acciones</th>
                    <tr>
                    <tr slot-scope="{ index, row }" :class="{'text-danger': (row.state_type_id === '11')}">
                        <!-- <td>{{ index }}</td> -->
                        <td class="text-left">{{ row.date_of_issue }}</td>
                        <td>{{ row.customer_name }} <br /> <small>{{ row.customer_number }}</small></td>
                        <td>{{ row.driver_name }}</td>
                        <td>{{ row.number }}</td>
                        <td class="text-center">{{ row.date_of_shipping }}</td>
                        <td class="text-center">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickDownload(row.external_id, 'a4')">PDF</button>
                        </td>
                        <td class="text-center">
                            <a v-if="row.btn_dispatch" :href="`/order-forms/dispatch-create/${row.id}`" class="btn waves-effect waves-light btn-xs btn-primary m-1__2">Generar Guía</a>
                            <a v-if="row.btn_dispatch" :href="`/order-forms/create/${row.id}`" class="btn waves-effect waves-light btn-xs btn-warning m-1__2">Editar</a>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickOptions(row.id)">Opciones</button>
                        </td>
                    </tr>
                </data-table>
            </div>

            
            <order-form-options :showDialog.sync="showDialogOptions"
                              :recordId="recordId"
                              :showClose="true"></order-form-options>
        </div>
    </div>
</template>
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
    import DataTable from '@components/DataTable.vue'
    import OrderFormOptions from './partials/options.vue'

    export default {

        components: {DataTable, OrderFormOptions},
        data() {
            return {
                resource: 'order-forms',
                showDialogOptions: false,
                recordId: null,
            }
        },
        created() {},
        methods: {
            clickOptions(recordId = null) {
                this.recordId = recordId
                this.showDialogOptions = true
            },
            clickDownload(external_id, format) {
                window.open(`/${this.resource}/download/${external_id}/${format}`, '_blank');
            },
            clickPrint(external_id){
                window.open(`/print/dispatch/${external_id}/a4`, '_blank');
            },

        }
    }
</script>
