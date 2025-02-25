<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/contracts"><svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Contratos</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <a :href="`/${resource}/create`" class="btn btn-custom btn-sm  mt-2 mr-2"><i class="fa fa-plus-circle"></i> Nuevo</a>
                <a :href="`./production-orders`" class="btn btn-custom btn-sm  mt-2 mr-2" title="Muestra los que tienen fecha de entrega">Listar Ordenes de Produccion</a>
            </div>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <div class="data-table-visible-columns">
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
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <!-- <th>#</th> -->
                        <th class="text-left">Fecha Emisión</th>
                        <th class="text-center" v-if="columns.delivery_date.visible">Fecha Entrega</th>
                        <th>Vendedor</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Contrato</th>
                        <th>Cotización</th>
                        <th class="text-center">Moneda</th>
                        <th class="text-right" v-if="columns.total_exportation.visible">T.Exportación</th>
                        <th class="text-right" v-if="columns.total_free.visible">T.Gratuito</th>
                        <th class="text-right" v-if="columns.total_unaffected.visible">T.Inafecta</th>
                        <th class="text-right" v-if="columns.total_exonerated.visible">T.Exonerado</th>
                        <th class="text-right">T.Gravado</th>
                        <th class="text-right">T.Igv</th>
                        <th class="text-right">Total</th>
                        <!-- <th class="text-center">PDF</th> -->
                        <th class="text-right">Acciones</th>
                    <tr>
                    <tr slot-scope="{ index, row }" :class="{ anulate_color : row.state_type_id == '11' }">
                        <!-- <td>{{ index }}</td> -->
                        <td class="text-left">{{ row.date_of_issue }}</td>
                        <td class="text-center" v-if="columns.delivery_date.visible">{{ row.delivery_date }}</td>
                        <td>{{ row.user_name }}</td>
                        <td>{{ row.customer_name }}<br/><small v-text="row.customer_number"></small></td>
                        <td>
                            <template v-if="row.state_type_id == '11'">
                                {{row.state_type_description}}
                            </template>
                            <template v-else>
                                <el-select v-model="row.state_type_id" @change="changeStateType(row)" style="width:120px !important">
                                    <el-option v-for="option in state_types" :key="option.id" :value="option.id" :label="option.description"></el-option>
                                </el-select>
                            </template>
                        </td>
                        <td>{{ row.number_full }} </td>
                        <td>{{ row.quotation_number_full }}</td>
                        <td class="text-center">{{ row.currency_type_id }}</td>
                        <td class="text-right"  v-if="columns.total_exportation.visible" >{{ row.total_exportation }}</td>
                        <td class="text-right" v-if="columns.total_free.visible">{{ row.total_free }}</td>
                        <td class="text-right" v-if="columns.total_unaffected.visible">{{ row.total_unaffected }}</td>
                        <td class="text-right" v-if="columns.total_exonerated.visible">{{ row.total_exonerated }}</td>
                        <td class="text-right">{{ row.total_taxed }}</td>
                        <td class="text-right">{{ row.total_igv }}</td>
                        <td class="text-right">{{ row.total }}</td>
                        <!-- <td class="text-right">

                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickOptionsPdf(row.id)">PDF</button>
                        </td> -->

                        <td class="text-right">
                            <!-- <button v-if="row.state_type_id != '11' && row.btn_generate && typeUser == 'admin'"  type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickOptions(row.id)" >Generar comprobante</button> -->

                            <a v-if="row.state_type_id != '11'" :href="`/${resource}/create/${row.id}`" type="button" class="btn waves-effect waves-light btn-xs btn-info">Editar</a>
                            <button v-if="row.state_type_id != '11'" type="button" class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickVoided(row.id)">Anular</button>

                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickOptionsPdf(row.id)">Opciones</button>
                        </td>

                    </tr>
                </data-table>
            </div>


            <quotation-options-pdf :showDialog.sync="showDialogOptionsPdf"
                              :contractNewId="recordId"
                              :showClose="true"></quotation-options-pdf>
        </div>
    </div>
</template>
<style scoped>
    .anulate_color{
        color:red;
    }
</style>
<script>

    import QuotationOptionsPdf from './partials/options_pdf.vue'
    import DataTable from '@components/DataTable.vue'
    import {deletable} from '@mixins/deletable'

    export default {
        props:['typeUser'],
        mixins: [deletable],
        components: {DataTable, QuotationOptionsPdf},
        data() {
            return {
                resource: 'contracts',
                recordId: null,
                showDialogOptions: false,
                showDialogOptionsPdf: false,
                state_types: [],
                columns: {
                    total_exportation: {
                        title: 'T.Exportación',
                        visible: false
                    },
                    total_unaffected: {
                        title: 'T.Inafecto',
                        visible: false
                    },
                    total_exonerated: {
                        title: 'T.Exonerado',
                        visible: false
                    },
                    total_free: {
                        title: 'T.Gratuito',
                        visible: false
                    },
                    delivery_date: {
                        title: 'F.Entrega',
                        visible: false
                    }
                }
            }
        },
        async created() {
            await this.filter()
        },
        methods: {
            async changeStateType(row){

                await this.updateStateType(`/${this.resource}/state-type/${row.state_type_id}/${row.id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                ) 

            },
            filter(){
                this.$http.get(`/${this.resource}/filter`)
                            .then(response => { 
                                this.state_types = response.data.state_types 
                            })
            },
            clickEdit(id)
            {
                this.recordId = id
                this.showDialogFormEdit = true
            }, 
            clickOptionsPdf(recordId = null) {
                this.recordId = recordId
                this.showDialogOptionsPdf = true
            },
            clickVoided(id)
            {
                this.voided(`/${this.resource}/voided/${id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                )
            }
        }
    }
</script>
