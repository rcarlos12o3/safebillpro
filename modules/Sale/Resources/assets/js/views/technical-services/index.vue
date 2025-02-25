<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/technical-services"><svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>{{ title }}</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i
                    class="fa fa-plus-circle"></i> Nuevo
                </button>
            </div>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">{{ title }}</h3>
            </div> -->
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <!-- <th>#</th> -->
                        <th>Cliente</th>
                        <th>Celular</th>
                        <th>Número</th>
                        <th>F. Emisión</th>
                        <th>N° Serie</th>
                        <th>Costo S.</th>
                        <th>Costo P.</th>
                        <th>Total</th>
                        <th class="text-center">Documento</th>
                        <!-- <th>Pago adelantado</th> -->
                        <th></th>
                        <th>Saldo</th>
                        <th class="text-center">Ver</th>
                        <th class="text-right">Acciones</th>
                    <tr>
                    <tr slot-scope="{ index, row }">
                        <!-- <td>{{ index }}</td> -->
                        <td>{{ row.customer_name }}<br/><small v-text="row.customer_number"></small></td>
                        <td class="text-center">{{ row.cellphone }}</td>
                        <td class="text-center">{{ row.id }}</td>
                        <td class="text-center">{{ row.date_of_issue }}</td>
                        <td class="text-center">{{ row.serial_number }}</td>
                        <td class="text-center">{{ row.cost }}</td>
                        <td class="text-center">{{ row.total }}</td>
                        <td class="text-center">{{ row.sum_total }}</td>
                        <td class="text-center">{{ row.number_document_sale_note }}</td>
                        <!-- <td class="text-center">{{ row.prepayment }}</td> -->
                        <td class="text-right">
                            <button
                                type="button"
                                style="min-width: 41px"
                                class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                @click.prevent="clickPayment(row.id)"
                            >Pagos
                            </button>
                        </td>

                        <td class="text-center">{{ row.balance }}</td>

                        <td class="text-center">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickPrint(row.id)">PDF
                            </button>
                        </td>

                        <td class="text-right">
                            <template v-if="!row.has_document_sale_note">
                                <button type="button"
                                        class="btn waves-effect waves-light btn-xs btn-info"
                                        @click.prevent="clickOptions(row.id)" >
                                    Generar comprobante
                                </button>
                                <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                        @click.prevent="clickCreate(row.id)">Editar
                                </button>
                            </template>

                            <template v-if="typeUser === 'admin'">
                                <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                        @click.prevent="clickDelete(row.id)">Eliminar
                                </button>
                            </template>
                        </td>
                    </tr>
                </data-table>
            </div>

            <technical-service-options :showDialog.sync="showDialogOptions"
                                       :recordId="recordId"
                                       :showGenerate="true"
                                       :showClose="true"></technical-service-options>

            <technical-services-form :showDialog.sync="showDialog"
                                     :recordId="recordId"></technical-services-form>

            <technical-service-payments
                :showDialog.sync="showDialogPayments"
                :recordId="recordId"
                :external="true"
            ></technical-service-payments>

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

import TechnicalServicesForm from './form.vue'
import DataTable from '@components/DataTable.vue'
import {deletable} from '@mixins/deletable'
import TechnicalServicePayments from './partials/payments.vue'
import TechnicalServiceOptions from './partials/options'
import {mapActions, mapState} from "vuex/dist/vuex.mjs";


export default {
    mixins: [
        deletable
    ],
    props: [
        'typeUser',
        'configuration',
    ],
    computed: {
        ...mapState([
            'exchange_rate',
            'config',
            'currency_types',
        ]),
    },
    components: {
        TechnicalServicesForm,
        DataTable,
        TechnicalServicePayments,
        TechnicalServiceOptions
    },
    data() {
        return {
            title: null,
            showDialog: false,
            showDialogOptions: false,
            resource: 'technical-services',
            recordId: null,
            showDialogPayments: false,
        }
    },
    created() {
        this.loadConfiguration();
        this.$store.commit('setConfiguration', this.configuration)
        this.loadCurrencyTypes();
        this.loadExchangeRate();
        this.title = 'Servicios de soporte técnico'
    },
    methods: {
        ...mapActions([
            'loadConfiguration',
            'loadExchangeRate',
            'loadCurrencyTypes',
        ]),
        clickPayment(recordId) {
            this.recordId = recordId;
            this.showDialogPayments = true
        },
        clickPrint(recordId) {
            window.open(`/${this.resource}/print/${recordId}/a4`, '_blank');
        },
        clickCreate(recordId = null) {
            this.recordId = recordId
            this.showDialog = true
        },
        clickDelete(id) {
            this.destroy(`/${this.resource}/${id}`).then(() =>
                this.$eventHub.$emit('reloadData')
            )
        },
        clickOptions(recordId = null) {
            this.recordId = recordId
            this.showDialogOptions = true
        },
    }
}
</script>
