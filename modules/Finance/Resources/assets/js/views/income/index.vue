<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/finances/income">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calculator"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 3m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 7m0 1a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-6a1 1 0 0 1 -1 -1z" /><path d="M8 14l0 .01" /><path d="M12 14l0 .01" /><path d="M16 14l0 .01" /><path d="M8 17l0 .01" /><path d="M12 17l0 .01" /><path d="M16 17l0 .01" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Ingresos diversos</span></li>
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
                        <th>Número</th>
                        <th>Motivo</th>
                        <!-- <th class="text-center">Pagos</th> -->
                        <th class="text-center">Moneda</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Dist. Ingreso</th>
                    <tr>
                    <tr slot-scope="{ index, row }" :class="{'text-danger': (row.state_type_id === '11'), 'text-warning': (row.state_type_id === '13'), 'border-light': (row.state_type_id === '01'), 'border-left border-info': (row.state_type_id === '03'), 'border-left border-success': (row.state_type_id === '05'), 'border-left border-secondary': (row.state_type_id === '07'), 'border-left border-dark': (row.state_type_id === '09'), 'border-left border-danger': (row.state_type_id === '11'), 'border-left border-warning': (row.state_type_id === '13')}">
                        <!-- <td>{{ index }}</td> -->
                        <td class="text-left">{{ row.date_of_issue }}</td>
                        <td>{{ row.customer_name }}</td>
                        <td >{{ row.number }}<br/>
                            <small v-text="row.income_type_description"></small><br/>
                        </td>
                        <td class="">{{ row.income_reason_description }}</td>
                        <!-- <td class="text-center">
                            <button
                                type="button"
                                style="min-width: 41px"
                                class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                @click.prevent="clickExpensePayment(row.id)"
                            >Pagos</button>
                        </td> -->
                        <td class="text-center">{{ row.currency_type_id }}</td>
                        <td class="text-right">{{ row.total }}</td>

                        <td class="text-center">
                            
                            <button type="button" style="min-width: 41px" class="btn waves-effect waves-light btn-xs btn-primary m-1__2"
                                    @click.prevent="clickPrint(row.external_id)">
                                    <i class="fas fa-print"></i>
                            </button>

                            <button type="button" style="min-width: 41px" class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                    @click.prevent="clickPayment(row.id)">
                                    <i class="fa fa-search"></i>
                            </button>
                            <button type="button" style="min-width: 41px" class="btn waves-effect waves-light btn-xs btn-danger m-1__2"
                                    @click.prevent="clickVoided(row.id)"
                                    v-if="row.state_type_id === '05'">
                                    <i class="fa fa-trash"></i>
                            </button>
                        </td>

                    </tr>
                </data-table>
            </div>


            <income-payments :showDialog.sync="showDialogPayments"
                               :recordId="recordId"></income-payments>
  
 
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

    import DataTable from '@components/DataTableResource.vue'
    import {deletable} from '@mixins/deletable'
    import IncomePayments from './partials/payments.vue'

    export default {
        mixins: [deletable],
        components: {DataTable, IncomePayments},
        data() {
            return {
                showDialogVoided: false,
                resource: 'finances/income',
                showDialogPayments: false,
                recordId: null,
                showDialogOptions: false
            }
        },
        created() {
        },
        methods: {
            clickPrint(external_id){

                window.open(`/${this.resource}/print/${external_id}`, '_blank');

            },
            clickExpensePayment(recordId) {
                this.recordId = recordId;
                this.showDialogExpensePayments = true
            },
            clickVoided(recordId) {
                this.voided(`/${this.resource}/voided/${recordId}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                )
            },
            clickDownload(download) {
                window.open(download, '_blank');
            },
            clickOptions(recordId = null) {
                this.recordId = recordId
                this.showDialogOptions = true
            },
            clickPayment(recordId) {
                this.recordId = recordId;
                this.showDialogPayments = true;
            },
        }
    }
</script>
