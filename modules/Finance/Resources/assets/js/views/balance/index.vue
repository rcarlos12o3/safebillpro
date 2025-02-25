<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/finances/balance">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calculator"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 3m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 7m0 1a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-6a1 1 0 0 1 -1 -1z" /><path d="M8 14l0 .01" /><path d="M12 14l0 .01" /><path d="M16 14l0 .01" /><path d="M8 17l0 .01" /><path d="M12 17l0 .01" /><path d="M16 17l0 .01" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Balance </span></li>
            </ol>
        </div>
        <div class="card tab-content-default row-new mb-0 pt-2 pt-md-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Balance</h3>
            </div> -->
            <div class="card mb-0">
                <div class="card-body">
                    <data-table :resource="resource" @changeCurrency="changeCurrency" >
                        <tr slot="heading">
                            <th class="">#</th>
                            <th class=""><strong>Nombre de la cuenta / Total pagos</strong></th>
                            <th class="text-center"><strong>CPE</strong></th>
                            <th class="text-center"><strong>N. Venta</strong></th>
                            <th class="text-center"><strong>Cotización</strong></th>
                            <th class="text-center"><strong>Contrato</strong></th>
                            <th class="text-center"><strong>S. Técnico</strong></th>
                            <th class="text-center"><strong>Ingresos</strong></th>
                            <th class="text-center"><strong>Compras</strong></th>
                            <th class="text-center"><strong>Gastos</strong></th>
                            <th class="text-center"><strong>Prestamos Bancarios</strong></th>
                            <th class="text-center"><strong>Pago Prestamos Bancarios</strong></th>
                            <th v-show="seller_can_view_balance"
                                class="text-center"><strong>Saldo</strong></th>
                        </tr>
                        <tr slot-scope="{ index, row }">
                            <td>{{ index }}</td>
                            <td>{{ row.description }}</td>
                            <td class="text-center">{{cuurencySymbol}} {{ row.document_payment }}</td>
                            <td class="text-center">{{cuurencySymbol}} {{ row.sale_note_payment }}</td>
                            <td class="text-center">{{cuurencySymbol}} {{ row.quotation_payment }}</td>
                            <td class="text-center">{{cuurencySymbol}} {{ row.contract_payment }}</td>
                            <td class="text-center">{{cuurencySymbol}} {{ row.technical_service_payment }}</td>
                            <td class="text-center">{{cuurencySymbol}} {{ row.income_payment }}</td>
                            <td class="text-center">{{cuurencySymbol}} {{ row.purchase_payment }}</td>
                            <td class="text-center">{{cuurencySymbol}} {{ row.expense_payment }}</td>
                            <td class="text-center">{{cuurencySymbol}} {{ row.bank_loan }}</td>
                            <td class="text-center">{{cuurencySymbol}} {{ row.bank_loan_payment }}</td>
                            <td v-show="seller_can_view_balance"
                                class="text-center">{{cuurencySymbol}} {{ row.balance }}
                            </td>
                        </tr>
                    </data-table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import DataTable from './partial/Table.vue'
// import DataTable from '../../components/DataTableWithoutPaging.vue'
import {mapActions, mapState} from "vuex/dist/vuex.mjs";
import {exchangeRate, functions} from "../../../../../../../resources/js/mixins/functions";
import moment from "moment";

export default {
    props: [
        'configuration',
        'user'
    ],
    mixins: [functions, exchangeRate],
    components: {DataTable},
    data() {
        return {
            resource: 'finances/balance',
            form: {},
            seller_can_view_balance: false,
            currency: 'PEN'
        }
    },

    created() {
        this.loadConfiguration()
        this.$store.commit('setConfiguration', this.configuration)
        this.getExchangeRate()
        this.CanViewBalance()
    },
    computed: {

        ...mapState([
            'exchange_rate_sale',
            'config',
        ]),
        cuurencySymbol() {
            return this.currency == 'PEN' ? 'S/': '$'
        }
    },
    methods: {
        ...mapActions([
            'loadConfiguration',
        ]),
        CanViewBalance() {
            if (this.user.type === 'admin') {
                this.seller_can_view_balance = true;
            } else {
                this.seller_can_view_balance = this.config.seller_can_view_balance;
            }
            return this.seller_can_view_balance;
        },
        getExchangeRate() {
            let date = moment().format('YYYY-MM-DD');
            this.searchExchangeRateByDate(date)
                .then(response => {
                    this.$store.commit('setExchangeRateSale', response)
                });

        },
        changeCurrency(value) {
            this.currency = value
        }

    }
}
</script>
