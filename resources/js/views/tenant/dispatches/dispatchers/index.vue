<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dispatchers">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-truck"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5" /></svg>
            </a></h2>
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
                <h3 class="my-0">Listado de {{ title }}</h3>
            </div> -->
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <!-- <th>#</th> -->
                        <th>Nombre</th>
                        <th class="text-left">Tipo de documento</th>
                        <th class="text-left">Número</th>
                        <th class="text-left">MTC</th>
                        <th class="text-center">Predeterminado</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                    <tr slot-scope="{ index, row }">
                        <!-- <td>{{ index }}</td> -->
                        <td>{{ row.name }}</td>
                        <td class="text-left">{{ row.document_type }}</td>
                        <td class="text-left">{{ row.number }}</td>
                        <td class="text-left">{{ row.number_mtc }}</td>
                        <td class="text-center">{{ row.is_default }}</td>
                        <td class="text-center">
                            <el-switch v-model="row.is_active" @change="toggleActiveDispatchers(row)" />
                        </td>
                        <td class="text-right">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickCreate(row.id)">Editar
                            </button>

                            <template v-if="typeUser === 'admin'">
                                <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                        @click.prevent="clickDelete(row.id)">Eliminar
                                </button>
                            </template>

                        </td>
                    </tr>
                </data-table>
            </div>

            <dispatchers-form :showDialog.sync="showDialog"
                              :recordId="recordId"
                              @success="successCreate"></dispatchers-form>
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

import DispatchersForm from './form.vue'
import DataTable from '@components/DataTable.vue'
import {deletable} from '@mixins/deletable'

export default {
    name: 'DispatchDispatcherIndex',
    mixins: [deletable],
    props: ['typeUser'],
    components: {DispatchersForm, DataTable},
    data() {
        return {
            title: null,
            showDialog: false,
            resource: 'dispatchers',
            recordId: null,
        }
    },
    created() {
        this.title = 'Transportistas'
    },
    methods: {
        clickCreate(recordId = null) {
            this.recordId = recordId
            this.showDialog = true
        },
        clickDelete(id) {
            this.destroy(`/${this.resource}/${id}`).then(() =>
                this.$eventHub.$emit('reloadData')
            )
        },
        successCreate() {
            this.$eventHub.$emit('reloadData')
        },
        toggleActiveDispatchers(row) {
            this.$http
                .post(`/dispatchers/${row.id}/toggle`, { is_active: row.is_active })
                .then(response => {
                    if (response.data.success) {
                        this.$eventHub.$emit('reloadData');
                    } else {
                        row.is_active = !row.is_active;
                        console.error('Error al actualizar el estado');
                    }
                })
                .catch(error => {
                    row.is_active = !row.is_active;
                    this.axiosError(error);
                });
        }
    }
}
</script>
