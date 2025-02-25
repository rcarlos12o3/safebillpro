<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/finances/payment-method-types">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calculator"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 3m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 7m0 1a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-6a1 1 0 0 1 -1 -1z" /><path d="M8 14l0 .01" /><path d="M12 14l0 .01" /><path d="M16 14l0 .01" /><path d="M8 17l0 .01" /><path d="M12 17l0 .01" /><path d="M16 17l0 .01" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Ingresos y egresos por métodos de pago </span></li>
            </ol>
        </div>
        <div class="card tab-content-default row-new mb-0 pt-2 pt-md-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Ingresos y egresos por métodos de pago</h3>
            </div> -->
            <div class="card mb-0">
                    <div class="card-body">
                        <data-table :resource="resource">
                            <tr slot="heading">
                                <th class="">#</th>
                                <th class=""><strong>CPE</strong></th>
                                <th class="text-center"><strong>Efectivo</strong></th>
                                <th class="text-center"><strong>N. Venta</strong></th>
                                <th v-if="configuration.cotizaction_finance" class="text-center"><strong>Cotización</strong></th>
                                <th class="text-center"><strong>Contrato</strong></th>
                                <th class="text-center"><strong>S. Técnico</strong></th>
                                <th class="text-center"><strong>Ingresos</strong></th>
                                <th class="text-center"><strong>Compras</strong></th>
                                <th class="text-center"><strong>Gastos</strong></th>
                                <!-- <th class="text-center"> <strong>Saldo</strong></th> -->
                            </tr>
                            <tr slot-scope="{ index, row }">
                                <td>{{ index }}</td> 
                                <td>{{row.description}}</td>
                                <td class="text-center">{{ (row.document_payment != '-') ? 'S/ '+row.document_payment : row.document_payment}}</td>
                                <td class="text-center">{{ (row.sale_note_payment != '-') ? 'S/ '+row.sale_note_payment : row.sale_note_payment}}</td>
                                <td v-if="configuration.cotizaction_finance" class="text-center">{{ (row.quotation_payment != '-') ? 'S/ '+row.quotation_payment : row.quotation_payment}}</td>
                                <td class="text-center">{{ (row.contract_payment != '-') ? 'S/ '+row.contract_payment : row.contract_payment}}</td>
                                <td class="text-center">{{ (row.technical_service_payment != '-') ? 'S/ '+row.technical_service_payment : row.technical_service_payment}}</td>
                                <td class="text-center">{{ (row.income_payment != '-') ? 'S/ '+row.income_payment : row.income_payment}}</td>
                                <td class="text-center">{{ (row.purchase_payment != '-') ? 'S/ '+row.purchase_payment : row.purchase_payment}}</td>
                                <td class="text-center">{{ (row.expense_payment != '-') ? 'S/ '+row.expense_payment : row.expense_payment}}</td>
                                <!-- <td class="text-center">{{row.balance}}</td>  -->
                            </tr>
                        </data-table>
                    </div>
            </div>
    
        </div>
    </div>
</template>

<script>

    import DataTable from '../../components/DataTableWithoutPaging.vue'

    export default {
        components: {DataTable},
        props: ['configuration'],
        data() {
            return {
                resource: 'finances/payment-method-types',
                form: {},

            }
        },
        async created() {
        },
        methods: {


        }
    }
</script>
