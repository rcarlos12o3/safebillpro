<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/suscription/plans">
                    <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 21h-6a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4.5" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M19 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M22 22a2 2 0 0 0 -2 -2h-2a2 2 0 0 0 -2 2" /></svg>
                </a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active">
                    <span>
                        Planes
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
                    Listado de planes
                </h3>
            </div> -->
            <div class="card-body">
                <data-table>
                    <tr slot="heading">
                        <!-- <th>
                            #
                        </th> -->
                        <th class="text-left">
                            Periodo
                        </th>
                        <th class="text-center">
                            Cant. Veces
                        </th>
                        <th class="text-left">
                            Nombre
                        </th>
                        <th class="text-left">
                            Descripción
                        </th>
                        <!--

                         <th class="text-center">
                             Total
                         </th>
                          -->
                        <th class="text-right">
                            Acciones
                        </th>
                    <tr>
                    <tr slot-scope="{ index, row }">
                        <!-- <td>
                            {{ index }}
                        </td> -->
                        <td class="text-left">
                            {{ row.period }}
                        </td>
                        <td class="text-center">
                            {{ row.quantity_period }}
                        </td>
                        <td class="text-left">
                            {{ row.name }}
                        </td>
                        <td class="text-left">
                            {{ row.description }}
                        </td>
                        <!--
                            <td class="text-right">
                                {{ row.total }}
                            </td>
                        -->
                        <td class="text-right">
                            <button
                                class="btn waves-effect waves-light btn-xs btn-info"
                                type="button"
                                @click.prevent="clickShowPlan(row)">
                                Ver
                            </button>

                            <button
                                v-if="!row.hasSuscription"
                                class="btn waves-effect waves-light btn-xs btn-danger"
                                type="button"
                                @click.prevent="clickDelete(row.id)">
                                Eliminar
                            </button>

                        </td>
                    </tr>
                </data-table>
            </div>

            <plans-form
                :showDialog.sync="showDialog"
                @reload-data="sendReload"
            >
            </plans-form>
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

import PlansForm from './form.vue'
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
        PlansForm,
        DataTable
    },
    data() {
        return {
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
        ]),
    },
    created() {
        this.loadConfiguration()

        this.$store.commit('setItemSearchExtraParameters', {'only_service': 1});

        this.$store.commit('setConfiguration', this.configuration)
        this.$store.commit('setResource', 'plans')
        this.$store.commit('setFormData', {
            periods: 'M',
            quantity_period: 12,
        })
        this.searchExchangeRateByDate(this.date).then(response => {
            this.$store.commit('setExchangeRate', response)
            // this.form.exchange_rate_sale = this.exchange_rate

        });
        this.getCommonData();

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


        sendReload() {
            this.$eventHub.$emit('reloadData')
        },

        clickShowPlan(row) {
            this.clearFormData();
            if (row === undefined) row = {};
            if(row.quantity_period === undefined) row.quantity_period = 12;
            if(row.periods === undefined) row.periods = 'M';

            this.$store.commit('setFormData', row)
            this.showDialog = true

        },

        clickDelete(id) {
            this.destroy(`/suscription/${this.resource}/${id}`).then(() =>
                this.$eventHub.$emit('reloadData')
            )
        }
    }
}
</script>
