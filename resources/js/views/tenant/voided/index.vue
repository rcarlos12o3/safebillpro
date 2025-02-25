<template>
    <div class="voided">
        <header class="page-header pr-0">
            <h2><a href="/voided">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-unknown"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M12 17v.01" /><path d="M12 14a1.5 1.5 0 1 0 -1.14 -2.474" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Anulaciones</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <form autocomplete="off" @submit.prevent="consultVoided">
                    <el-button class="btn btn-custom btn-sm  mt-2 mr-2" native-type="submit" :loading="loading_submit_voided">Consultar documentos</el-button>
                </form>
            </div>
        </header>
        <div class="card tab-content-default row-new mb-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Listado de anulaciones</h3>
            </div> -->
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <!-- <th>#</th> -->
                        <th class="text-left">F.Emisión</th>
                        <th class="text-center">F.E.Comprobante</th>
                        <th>Identificador</th>
                        <th>Ticket</th>
                        <th>Estado</th>
                        <th class="text-center">Descargas</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                    <tr slot-scope="{ index, row }" :class="{'text-danger': (row.state_type_id === '05'), 'text-warning': (row.state_type_id === '03')}">
                        <!-- <td>{{ index }}</td> -->
                        <td class="text-left">{{ row.date_of_issue }}</td>
                        <td class="text-center">{{ row.date_of_reference }}</td>
                        <td>{{ row.identifier }}</td>
                        <td>{{ row.ticket }}</td>
                        <td>{{ row.state_type_description }}</td>
                        <td class="text-center">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickDownload(row.download_xml)"
                                    v-if="row.has_xml">XML</button>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickDownload(row.download_cdr)"
                                    v-if="row.has_cdr">CDR</button>
                        </td>
                        <td class="text-right">
                            <el-tooltip content="Completar anulación" placement="top">
                                <button type="button"
                                        class="btn waves-effect waves-light btn-xs btn-warning"
                                        @click.prevent="clickTicket(row.type, row.id)"
                                        dusk="consult-voided"
                                        v-if="row.btn_ticket">
                                    Enviar Baja
                                </button>
                            </el-tooltip>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                    @click.prevent="clickDelete(row.type, row.id)"
                                    v-if="row.btn_ticket">Eliminar</button>
                        </td>
                    </tr>
                </data-table>
            </div>
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

    import DataTable from '../../../components/DataTable.vue'
    import {deletable} from '../../../mixins/deletable'

    export default {
        mixins: [deletable],
        components: {DataTable},
        data () {
            return {
                resource: 'voided',
                showDialog: false,
                records: [],
                loading_submit_voided: false,
            }
        },
        created() {
        },
        methods: {
            clickTicket(type, id) {
                this.$http.get(`/${type}/status/${id}`)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                            this.$eventHub.$emit('reloadData')
                        } else {
                            this.$message.error(response.data.message)
                        }
                    })
                    .catch(error => {
                        this.$message.error(error.response.data.message)
                    })
            },
            clickDelete(type, id) {
                this.destroy(`/${type}/${id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                )
            },
            clickDownload(download) {
                window.open(download, '_blank');
            },
            consultVoided()
            {
                this.loading_submit_voided = true
                this.$http.get(`/voided/status_masive`)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                            this.$eventHub.$emit('reloadData')
                        } else {
                            this.$message.error('Sucedio un error')
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.errors = error.response.data.errors
                        } else {
                            console.log(error)
                        }
                    })
                    .then(() => {
                        this.loading_submit_voided = false
                    })
            }
        }
    }
</script>
