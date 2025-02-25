<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/full_suscription/payments">
                    <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-month"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M7 14h.013" /><path d="M10.01 14h.005" /><path d="M13.01 14h.005" /><path d="M16.015 14h.005" /><path d="M13.015 17h.005" /><path d="M7.01 17h.005" /><path d="M10.01 17h.005" /></svg>
                </a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active">
                    <span>
                        Suscripciones
                    </span>
                </li>
            </ol>
            <div class="right-wrapper pull-right">
                <button class="btn btn-custom btn-sm  mt-2 mr-2"
                        type="button"
                        @click.prevent="clickShowPlan()">
                    <i class="fa fa-plus-circle">
                    </i>
                    Nuevo
                </button>
            </div>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">
                    Listado de suscriptores
                </h3>
            </div> -->
            <div class="card-body">
                <data-table>
                    <tr slot="heading">
                        <!-- <th>
                            #
                        </th> -->
                        <th class="text-left">
                            Cliente
                        </th>
                        <th class="text-center">
                            Plan
                        </th>
                        <th class="text-center">
                            Cant.Periodo/Ciclo
                        </th>
                        <th class="text-center">
                            Total (cant * total)
                        </th>

                        <th class="text-right">
                            Acciones
                        </th>
                    </tr>
                    <tr slot-scope="{ index, row }">
                        <!-- <td>
                            {{ index }}
                        </td> -->
                        <td class="text-left">
                            {{ row.parent_customer.description }}
                        </td>
                        <td class="text-center">
                            {{ row.plan.name }}
                        </td>
                        <td class="text-center">
                            {{ row.quantity_period }}
                        </td>
                        <td class="text-center">
                            {{ row.quantity_period * row.total }}
                        </td>
                        <td class="text-right">
                            <button
                                class="btn waves-effect waves-light btn-xs btn-info"
                                type="button"
                                @click.prevent="clickShowPlan(row)">
                                Ver
                            </button>
                        </td>
                    </tr>
                </data-table>
            </div>
            <customers-form
                :showDialog.sync="showDialog"
                :suscriptionId="suscriptionId"
                @clearSuscriptionId="clearsuscriptionid">
            </customers-form>
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
import {mapActions, mapState} from "vuex/dist/vuex.mjs";

import CustomersForm from './form.vue'
import DataTable from '../components/SuscriptionsDataTable.vue'
import {deletable} from '../../../../../../resources/js/mixins/deletable'
import {exchangeRate} from "../../../../../../resources/js/mixins/functions";

export default {
    props: [
        'configuration',
        'date'
    ],
    mixins: [
        deletable,
        exchangeRate
    ],
    components: {
        CustomersForm,
        DataTable
    },
    data() {
        return {
            suscriptionId: null,
            showDialog: false,
        }
    },
    computed: {
        ...mapState([
            'config',
            'resource',
            'form_data',
            'exchange_rate',
            'periods',
            'affectation_igv_types',
            'item_search_extra_parameters',
            'unit_types',
            'payment_method_types',
            'customers',
            'customer_addresses',
            'all_customers',
        ]),
    },
    created() {
        this.loadConfiguration()

        this.$store.commit('setItemSearchExtraParameters', {'only_service': 1});
        this.$store.commit('setConfiguration', this.configuration)
        this.$store.commit('setResource', 'payments')
        this.$store.commit('setFormData', {})
        this.$store.commit('setCustomers', [])
        this.getCommonData();
        this.getSuscriptionData();
        this.searchExchangeRateByDate(this.date).then(response => {
            this.$store.commit('setExchangeRate', response)
            // this.form.exchange_rate_sale = this.exchange_rate

        });


    },
    methods: {
        ...mapActions([
            'loadConfiguration',
            'clearFormData',
        ]),
        getCommonData() {
            this.$http.post('CommonData', {})
                .then((response) => {
                    this.$store.commit('setCurrencyTypes', response.data.currency_types)
                    this.$store.commit('setAffectationIgvTypes', response.data.affectation_igv_types)
                    this.$store.commit('setUnitTypes', response.data.unit_types)
                    this.$store.commit('setPaymentMethodTypes', response.data.payments_credit)
                })
        },

        getSuscriptionData() {
            this.$http
                .post(`/full_suscription/${this.resource}/tables`, {})
                .then(response => {
                    let customers = response.data.customers;
                    this.$store.commit('setPeriods', response.data.periods)
                    this.$store.commit('setCustomers', customers)
                    this.$store.commit('setAllCustomers', customers)
                    this.$store.commit('setPlans', response.data.plans)
                })
        },


        clickShowPlan(row) {

            this.clearFormData();
            this.suscriptionId = null
            if (row !== undefined && row.id !== null) {
                this.suscriptionId = row.id
            }

            this.$store.commit('setFormData', row)
            this.showDialog = true

        },

        clickDelete(id) {
            console.log('no debe hacer nada')
            this.destroy(`/${this.resource}/${id}`).then(() =>
                this.$eventHub.$emit('reloadData')
            )
        },
        clearsuscriptionid(data) {
            this.suscriptionId = null;
        }
    }
}
</script>
