<template>
    <div class="summaries">
        <header class="page-header pr-0">
            <h2><a href="/summaries">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-unknown"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M12 17v.01" /><path d="M12 14a1.5 1.5 0 1 0 -1.14 -2.474" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Resúmenes</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i class="fa fa-plus-circle"></i> Nuevo</button>
            </div>
        </header>
        <div class="card tab-content-default row-new">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Listado de resúmenes</h3>
            </div> -->

             <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <!-- <th>#</th> -->
                        <th class="text-left">Fecha Emisión</th>
                        <th class="text-center">Fecha Referencia</th>
                        <th class="text-center" v-if="show_summary_status_type">Tipo de estado</th>
                        <th>Identificador</th>
                        <th>Estado</th>
                        <th>Ticket</th>
                        <th class="text-center">Descargas</th>
                        <th class="text-right">Acciones</th>
                    <tr>
                    <tr slot-scope="{ index, row }" >
                        <!-- <td>{{ index  }}</td> -->
                        <td class="text-left">{{ row.date_of_issue }}</td>
                        <td class="text-center">{{ row.date_of_reference }}</td>
                        <td class="text-center"  v-if="show_summary_status_type">{{ row.summary_status_type_description }}</td>
                        <td>{{ row.identifier }}</td>
                        <td>
                            <!-- {{ row.state_type_description }} -->
                            <span class="badge bg-secondary text-white" :class="{'bg-secondary': (row.state_type_id === '01'), 'bg-info': (row.state_type_id === '03'), 'bg-success': (row.state_type_id === '05'), 'bg-secondary': (row.state_type_id === '07'), 'bg-dark': (row.state_type_id === '09')}">{{row.state_type_description}}</span>
                        </td>
                        <td>{{ row.ticket }}</td>
                        <td class="text-center">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickDownload(row.download_xml)"
                                    v-if="row.has_xml">XML</button> 
                            <!-- <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickDownload(row.download_cdr)"
                                    v-if="row.has_cdr">CDR</button> -->
                                    
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickOptions(row.id)"
                                    v-if="row.has_cdr">CDR</button>
                        </td>
                        <td class="text-right">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-warning"
                                    @click.prevent="clickTicket(row.id)"
                                    dusk="consult-ticket"
                                    v-if="row.btn_ticket">Consultar</button>
                                    
                            <template v-if="row.unknown_error_status_response">
                                <!-- <el-tooltip class="item" effect="dark" content="Regularizar comprobantes" placement="top-start"> -->
                                    <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                            @click.prevent="clickValidateSummary(row)"
                                            v-if="row.btn_ticket">Regularizar</button>
                                <!-- </el-tooltip> -->
                            </template>
                            <!-- <template v-else> -->
                                <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                        @click.prevent="clickDelete(row.id)"
                                        v-if="row.btn_ticket">Eliminar</button>
                            <!-- </template> -->
                        
                        </td>
                    </tr>
                </data-table>
            </div>
            
            <summary-form :showDialog.sync="showDialog"
                        :external="false"></summary-form>

            <summary-options :showDialog.sync="showDialogOptions"
                              :recordId="recordId"></summary-options>

            <summary-regularize :showDialog.sync="showDialogRegularize"
                              :summary="summary"></summary-regularize>
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

    import SummaryOptions from './partials/options.vue'
    import SummaryRegularize from './partials/regularize.vue'
    import SummaryForm from './form.vue'
    import DataTable from '../../../components/DataTable.vue'
    import {deletable} from '../../../mixins/deletable'

    export default {
        props: ['show_summary_status_type'],
        mixins: [deletable],
        components: {DataTable, SummaryForm, SummaryOptions, SummaryRegularize},
        data () {
            return {
                resource: 'summaries',
                showDialog: false,
                showDialogOptions: false,
                showDialogRegularize: false,
                recordId: null,
                records: [],
                summary: null
            }
        },
        created() {

        },
        methods: { 
            clickOptions(recordId){
                this.recordId = recordId
                this.showDialogOptions = true
            },
            clickCreate() {
                this.showDialog = true
            },
            clickTicket(id) {
                this.$http.get(`/${this.resource}/status/${id}`)
                    .then(response => {
                        this.$eventHub.$emit('reloadData') 
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                        } else {
                            this.$message.error(response.data.message)
                        }
                    })
                    .catch(error => {
                        this.$message.error(error.response.data.message)
                    })
            },
            clickDelete(id) {
                this.destroy(`/${this.resource}/${id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                )
            },
            clickDownload(download) {
                window.open(download, '_blank');
            },
            clickValidateSummary(row){
                
                this.summary = row
                this.showDialogRegularize = true

            },
        }
    }
</script>
