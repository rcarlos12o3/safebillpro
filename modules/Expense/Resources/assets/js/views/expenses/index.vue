<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/expenses">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-bag"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304z" /><path d="M9 11v-5a3 3 0 0 1 6 0v5" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Gastos diversos</span></li>
            </ol>
            <div class="right-wrapper pull-right pt-2 mr-2">
                <!--<el-button class="submit" type="success" @click.prevent="clickDownload('excel')"><i class="fa fa-file-excel"></i> Exportar Excel </el-button>-->
                <a :href="`/${resource}/create`" class="btn btn-custom btn-sm "><i class="fa fa-plus-circle"></i> Nuevo</a>
            </div>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <!-- <th>#</th> -->
                        <th class="text-left">Fecha Emisión</th>
                        <th>Proveedor</th>
                        <th>Número</th>
                        <th>Motivo</th>
                        <th class="text-center">Pagos</th>
                        <th class="text-center">Moneda</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Dist. Gasto</th>
                    </tr>
                    <tr slot-scope="{ index, row }" :class="{'text-danger': (row.state_type_id === '11'), 'text-warning': (row.state_type_id === '13'), 'border-light': (row.state_type_id === '01'), 'border-left border-info': (row.state_type_id === '03'), 'border-left border-success': (row.state_type_id === '05'), 'border-left border-secondary': (row.state_type_id === '07'), 'border-left border-dark': (row.state_type_id === '09'), 'border-left border-danger': (row.state_type_id === '11'), 'border-left border-warning': (row.state_type_id === '13')}">
                        <!-- <td>{{ index }}</td> -->
                        <td class="text-left">{{ row.date_of_issue }}</td>
                        <td>{{ row.supplier_name }}<br/><small v-text="row.supplier_number"></small></td>
                        <td>{{ row.number }}<br/>
                            <small v-text="row.expense_type_description"></small><br/>
                        </td>
                        <td class="">{{ row.expense_reason_description }}</td>
                        <td class="text-center">
                            <button
                                type="button"
                                style="min-width: 41px"
                                class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                @click.prevent="clickExpensePayment(row.id)"
                            >Pagos</button>
                        </td>
                        <td class="text-center">{{ row.currency_type_id }}</td>
                        <td class="text-right">{{ row.total }}</td>

                        <td class="text-center">

                            <button type="button" style="min-width: 41px" class="btn waves-effect waves-light btn-xs btn-success m-1__2"
                                    @click.prevent="clickPrint(row.external_id)">
                                    <i class="fas fa-print"></i>
                            </button>

                            <button type="button" v-if="row.state_type_id != '11'" style="min-width: 41px" class="btn waves-effect waves-light btn-xs btn-primary m-1__2"
                                    @click.prevent="clickCreate(row.id)">
                                    <i class="fa fa-pen"></i>
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


            <document-payments :showDialog.sync="showDialogPayments"
                               :expenseId="recordId"></document-payments>
            <expense-voided :showDialog.sync="showDialogVoided"
                               :expenseId="recordId"></expense-voided>

            <expense-payments
                :showDialog.sync="showDialogExpensePayments"
                :expenseId="recordId"
                :external="true"
                ></expense-payments>
        </div>
    </div>

</template>

<script>

    import DataTable from '../../components/DataTableExpenses.vue'
    import DocumentPayments from './partials/payments.vue'
    import ExpenseVoided from './partials/voided.vue'
    import ExpensePayments from '@viewsModuleExpense/expense_payments/payments.vue'
    import queryString from 'query-string'

    export default {
        components: {DataTable, DocumentPayments, ExpenseVoided, ExpensePayments},
        data() {
            return {
                showDialogVoided: false,
                resource: 'expenses',
                showDialogPayments: false,
                showDialogExpensePayments: false,
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
            clickCreate(id = '') {
                location.href = `/${this.resource}/create/${id}`
            },
            clickExpensePayment(recordId) {
                this.recordId = recordId;
                this.showDialogExpensePayments = true
            },
            clickVoided(recordId) {
                this.recordId = recordId;
                this.showDialogVoided = true;
            },
            /*clickDownload(download) {
                let data = this.$root.$refs.DataTable.getSearch();
                let query = queryString.stringify({
                    'column': data.column,
                    'value': data.value
                });

                window.open(`/${this.resource}/report/excel/?${query}`, '_blank');
            },*/
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
