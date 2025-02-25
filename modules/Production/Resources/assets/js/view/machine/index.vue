<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/machine-production">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-building-factory-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21h18" /><path d="M5 21v-12l5 4v-4l5 4h4" /><path d="M19 21v-8l-1.436 -9.574a.5 .5 0 0 0 -.495 -.426h-1.145a.5 .5 0 0 0 -.494 .418l-1.43 8.582" /><path d="M9 17h1" /><path d="M14 17h1" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active">
                    <span>
                        Listado de maquinas
                    </span>
                </li>
            </ol>
            <div class="right-wrapper pull-right pt-2 mr-2">
                <a :href="`/machine-type-production/create`"
                   class="btn btn-custom btn-sm ">
                    <i class="fa fa-plus-circle"></i>
                    Nuevo tipo de maquina
                </a>
            </div>
            <div class="right-wrapper pull-right pt-2 mr-2">
                <a :href="`/${resource}/create`"
                   class="btn btn-custom btn-sm ">
                    <i class="fa fa-plus-circle"></i>
                    Nueva maquina
                </a>
            </div>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <!-- <th>#</th> -->
                        <th>Nombre</th>
                        <th>Tipo de maquinaria</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Fuerza de cierre</th>
                        <th></th>
                    <tr>
                    <tr slot-scope="{ index, row }">
                        <!-- <td>{{ index }}</td> -->
                        <td>{{ row.name }}</td>

                        <td>
                            <div v-if="row.machine_type && row.machine_type.name">
                                {{ row.machine_type.name }}
                            </div>
                        </td>
                        <td>{{ row.brand }}</td>
                        <td>{{ row.model }}</td>
                        <td>{{ row.closing_force }}</td>
                        <td>
                            <button
                                class="btn waves-effect waves-light btn-xs btn-info"
                                type="button"
                                @click.prevent="clickCreate(row.id)">Editar
                            </button>

                        </td>

                    </tr>
                </data-table>
            </div>

            <!--
            <document-payments :showDialog.sync="showDialogPayments"
                               :expenseId="recordId"></document-payments>
            <expense-voided :showDialog.sync="showDialogVoided"
                            :expenseId="recordId"></expense-voided>

            <expense-payments
                :showDialog.sync="showDialogExpensePayments"
                :expenseId="recordId"
                :external="true"
            ></expense-payments>
            -->
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
// import DocumentPayments from './partials/payments.vue'
// import ExpenseVoided from './partials/voided.vue'
// import ExpensePayments from '@viewsModuleExpense/expense_payments/payments.vue'
import queryString from 'query-string'

export default {
    components: {
        // DocumentPayments,
        // ExpenseVoided,
        // ExpensePayments,
        DataTable

    },
    data() {
        return {
            showDialogVoided: false,
            resource: 'machine-production',
            showDialogPayments: false,
            showDialogExpensePayments: false,
            recordId: null,
            showDialogOptions: false
        }
    },
    created() {
    },
    methods: {
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
        clickDownload(download) {
            let data = this.$root.$refs.DataTable.getSearch();
            let query = queryString.stringify({
                'column': data.column,
                'value': data.value
            });

            window.open(`/${this.resource}/report/excel/?${query}`, '_blank');
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
