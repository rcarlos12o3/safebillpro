<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/establishments">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-list-numbers"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11 6h9" /><path d="M11 12h9" /><path d="M12 18h8" /><path d="M4 16a2 2 0 1 1 4 0c0 .591 -.5 1 -1 1.5l-3 2.5h4" /><path d="M6 10v-6l-2 2" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Sucursales</span></li>
            </ol>
            <div class="right-wrapper pull-right">

                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" v-if="typeUser != 'integrator'" @click.prevent="clickCreate()"><i class="fa fa-plus-circle"></i> Nuevo</button>

                <!--<button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickImport()"><i class="fa fa-upload"></i> Importar</button>-->
            </div>
        </div>
        <div class="card tab-content-default row-new">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Listado de establecimientos</h3>
            </div> -->
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <!-- <th>#</th> -->
                            <th>Descripción</th>
                            <th class="text-left">Código</th>
                            <th class="text-center">Series</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(row, index) in records" :key="index">
                            <!-- <td>{{ index + 1 }}</td> -->
                            <td>{{ row.description }}</td>
                            <td class="text-left">{{ row.code }}</td>
                            <td class="text-center"><button type="button" class="btn waves-effect waves-light btn-xs btn-warning"
                                @click.prevent="clickSeries(row.id)">Series</button></td>
                            <td class="text-right">
                                <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickCreate(row.id)">Editar</button>
                                <button type="button" class="btn waves-effect waves-light btn-xs btn-danger" v-if="typeUser != 'integrator'" @click.prevent="clickDelete(row.id)">Eliminar</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!-- <div class="row">
                    <div class="col">
                        <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" v-if="typeUser != 'integrator'" @click.prevent="clickCreate()"><i class="fa fa-plus-circle"></i> Nuevo</button>
                    </div>
                </div> -->
            </div>
            <establishments-form :showDialog.sync="showDialog"
                                :recordId="recordId"></establishments-form>
            <establishment-series :showDialog.sync="showDialogSeries"
                                :establishmentId="recordId"></establishment-series>
        </div>
    </div>
</template>

<script>

    import EstablishmentsForm from './form1.vue'
    import {deletable} from '../../../mixins/deletable'
    import EstablishmentSeries from './partials/series.vue'

    export default {
        props:['typeUser'],
        mixins: [deletable],
        components: {EstablishmentsForm, EstablishmentSeries},
        data() {
            return {
                showDialog: false,
                resource: 'establishments',
                recordId: null,
                records: [],
                showDialogSeries: false,
                establishment: null,
            }
        },
        created() {
            this.$eventHub.$on('reloadData', () => {
                this.getData()
            })
            this.getData()
        },
        methods: {
            getData() {
                this.$http.get(`/${this.resource}/records`)
                    .then(response => {
                        this.records = response.data.data
                    })
            },
            clickCreate(recordId = null) {
                this.recordId = recordId
                this.showDialog = true
            },
            clickSeries(recordId = null) {
                this.recordId = recordId
                this.showDialogSeries = true
            },
            clickDelete(id) {
                this.destroy(`/${this.resource}/${id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                )
            }
        }
    }
</script>
