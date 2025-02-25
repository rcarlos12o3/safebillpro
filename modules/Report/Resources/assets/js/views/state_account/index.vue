<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/list-reports">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-analytics"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17l0 -5" /><path d="M12 17l0 -1" /><path d="M15 17l0 -3" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Consulta de Estado de Cuenta por Cliente </span></li>
            </ol>
        </div>
        <div class="card mb-0 pt-2 pt-md-0 tab-content-default row-new">
            <div class="p-b-10">
                <!-- <h3 class="my-0">Consulta de Estado de Cuenta por Cliente</h3> -->
                <div class="data-table-visible-columns" style="top: 10px;">
                    <el-dropdown :hide-on-click="false">
                        <el-button type="primary">
                            Mostrar/Ocultar columnas<i class="el-icon-arrow-down el-icon--right"></i>
                        </el-button>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item v-for="(column, index) in columns" :key="index">
                                <el-checkbox v-model="column.visible">{{ column.title }}</el-checkbox>
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
            </div>
            <div class="card mb-0">
                    <div class="card-body">
                        <data-table :applyCustomer="true" :resource="resource" :visibleColumns="columns">
                            <tr slot="heading">
                                <th class="">#</th>
                                <th class="">Usuario/Vendedor</th>
                                <th class="">Tipo Documento</th>
                                <th class="">Comprobante</th>
                                <th class="">Fecha emisión</th>
                                <th class="">Fecha vencimiento</th>
                                <th v-if="columns.guides.visible" class="text-right">Guia</th>
                                <th v-if="columns.options.visible" class="text-right">Opciones</th>
                                <th>Cliente</th>
                                <th>Productos</th>
                                <th>Estado</th>
                                <th>Moneda</th>
                                <th v-if="columns.total_charge.visible">Total Cargos</th>
                                <th>Total Exonerado</th>
                                <th>Total Inafecto</th>
                                <th>Total Gratuito</th>
                                <th>Total Gravado</th>
    
                                <th class="">Total IGV</th>
                                <th class="" v-if="columns.total_isc.visible">Total ISC</th>
                                <th class="">Total</th>
                                <th class="">Por Pagar</th>
                            </tr>
                            <tr slot-scope="{ index, row }">
                                <td>{{ index }}</td>
                                <td>{{ row.user_name }}</td>
                                <td>{{ row.document_type_description }}</td>
                                <td>{{ row.number }}</td>
                                <td>{{ row.date_of_issue }}</td>
                                <td>{{ row.date_of_due }}</td>
                                <td v-if="columns.guides.visible" class="text-center">
                                    <span v-for="(item, i) in row.guides" :key="i">
                                        {{ item.number }} <br>
                                    </span>
                                </td>
                                <td v-if="columns.options.visible" class="text-center">
                                    <button class="btn waves-effect waves-light btn-xs btn-info m-1__2" type="button"
                                            @click.prevent="clickOptions(row.id)">Opciones
                                    </button>
                                </td>
                                <td>{{ row.customer_name }}<br/><small v-text="row.customer_number"></small></td>
                                <td class="text-center">
                                    <button
                                        class="btn waves-effect waves-light btn-xs btn-primary"
                                        type="button"
                                        @click.prevent="clickViewProducts(row.items)"
                                    >
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </td>
                                <td>{{ row.state_type_description }}</td>
    
                                <td>{{ row.currency_type_id }}</td>
                                <td v-if="columns.total_charge.visible">
                                    {{
                                        (row.document_type_id == '07') ? ((row.total_charge == 0) ? '0.00' : '-' + row.total_charge) : ((row.document_type_id != '07' && (row.state_type_id == '11' || row.state_type_id == '09')) ? '0.00' : row.total_charge)
                                    }}
                                </td>
    
    
                                <td>{{
                                        (row.document_type_id == '07') ? ((row.total_exonerated == 0) ? '0.00' : '-' + row.total_exonerated) : ((row.document_type_id != '07' && (row.state_type_id == '11' || row.state_type_id == '09')) ? '0.00' : row.total_exonerated)
                                    }}
                                </td>
    
                                <td>{{
                                        (row.document_type_id == '07') ? ((row.total_unaffected == 0) ? '0.00' : '-' + row.total_unaffected) : ((row.document_type_id != '07' && (row.state_type_id == '11' || row.state_type_id == '09')) ? '0.00' : row.total_unaffected)
                                    }}
                                </td>
                                <td>{{
                                        (row.document_type_id == '07') ? ((row.total_free == 0) ? '0.00' : '-' + row.total_free) : ((row.document_type_id != '07' && (row.state_type_id == '11' || row.state_type_id == '09')) ? '0.00' : row.total_free)
                                    }}
                                </td>
                                <td>{{
                                        (row.document_type_id == '07') ? ((row.total_taxed == 0) ? '0.00' : '-' + row.total_taxed) : ((row.document_type_id != '07' && (row.state_type_id == '11' || row.state_type_id == '09')) ? '0.00' : row.total_taxed)
                                    }}
                                </td>
                                <td>{{
                                        (row.document_type_id == '07') ? ((row.total_igv == 0) ? '0.00' : '-' + row.total_igv) : ((row.document_type_id != '07' && (row.state_type_id == '11' || row.state_type_id == '09')) ? '0.00' : row.total_igv)
                                    }}
                                </td>
                                <td v-if="columns.total_isc.visible">
                                    {{
                                        (row.document_type_id == '07') ? ((row.total_isc == 0) ? '0.00' : '-' + row.total_isc) : ((row.document_type_id != '07' && (row.state_type_id == '11' || row.state_type_id == '09')) ? '0.00' : row.total_isc)
                                    }}
                                </td>
                                <td>{{
                                        (row.document_type_id == '07') ? ((row.total == 0) ? '0.00' : '-' + row.total) : ((row.document_type_id != '07' && (row.state_type_id == '11' || row.state_type_id == '09')) ? '0.00' : row.total)
                                    }}
                                </td>
    
                                <td>{{
                                        row.payment_state
                                    }}
                                </td>
    
                                <!-- <td>{{ (row.document_type_id == '07') ? -row.total_unaffected : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_unaffected) }}</td>
                                <td>{{ (row.document_type_id == '07') ? -row.total_free : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_free) }}</td>
                                <td>{{ (row.document_type_id == '07') ? -row.total_taxed : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_taxed) }}</td>
                                <td>{{ (row.document_type_id == '07') ? -row.total_igv : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total_igv) }}</td>
                                <td>{{ (row.document_type_id == '07') ? -row.total : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total) }}</td>  -->
    
                            </tr>
    
                        </data-table>
    
    
                    </div>
            </div>
            <document-options :showDialog.sync="showDialogOptions"
                              :recordId="recordId"
                              :showClose="true"
                              :configuration="configuration"
            ></document-options>
            <product-sale :records="recordsItems" :showDialog.sync="showDialogProducts">
    
            </product-sale>
        </div>
    </div>
</template>

<script>

    import DataTable from '../../components/DataTableReports.vue'
    import DocumentOptions from '../../../../../../../resources/js/views/tenant/documents/partials/options'
    import ProductSale from './partials/product_sale.vue'

    export default {
        props: ['configuration'],
        components: {DataTable,DocumentOptions, ProductSale},
        data() {
            return {
                showDialogOptions: false,
                recordId: null,
                resource: 'reports/state-account',
                form: {},
                columns: {
                    guides: {
                        title: 'Guias',
                        visible: false
                    },
                    options: {
                        title: 'Opciones',
                        visible: false
                    },
                    total_isc: {
                        title: 'Total ISC',
                        visible: false
                    },
                    total_charge: {
                        title: 'Total Cargos',
                        visible: false
                    },
                },
                showDialogProducts: false,
                recordsItems:[]

            }
        },
        async created() {
        },
        methods: {
            clickOptions(recordId = null) {
                this.recordId = recordId
                this.showDialogOptions = true
            },
            clickViewProducts(items = []) {
                this.recordsItems = items;
                this.showDialogProducts = true;
            }

        }
    }
</script>
