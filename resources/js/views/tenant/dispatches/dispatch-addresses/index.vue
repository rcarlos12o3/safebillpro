<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>{{ title }}</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i
                    class="fa fa-plus-circle"></i> Nuevo
                </button>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <th>#</th>
                        <th class="text-left">Cliente</th>
                        <th class="text-left">Dirección</th>
                        <th class="text-left">Ubigeo</th>
                        <th class="text-left">Código</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                    <tr slot-scope="{ index, row }">
                        <td>{{ index }}</td>
                        <td class="text-left">{{ row.person_name }}<br/><small v-text="row.person_number"></small></td>
                        <td class="text-left">{{ row.address }}</td>
                        <td class="text-left">{{ row.location_name }}</td>
                        <td class="text-left">{{ row.establishment_code }}</td>
                        <td class="text-end">
                            <el-button type="primary" size="small"
                                    @click.prevent="clickCreate(row.id)">Editar
                            </el-button>
                            <template v-if="typeUser === 'admin'">
                                <el-button type="danger" size="small"
                                        @click.prevent="clickDelete(row.id)">Eliminar
                                </el-button>
                            </template>
                        </td>
                    </tr>
                </data-table>
            </div>

            <dispatch-address-form :showDialog.sync="showDialog"
                                    :recordId="recordId"
                                    @success="successCreate"></dispatch-address-form>
        </div>
    </div>
</template>

<script>

    import DispatchAddressForm from './form'
    import DataTable from '../../../../components/DataTable.vue'
    import {deletable} from '../../../../mixins/deletable'

    export default {
        mixins: [deletable],
        props: ['typeUser'],
        components: {DataTable, DispatchAddressForm},
        data() {
            return {
                title: null,
                showDialog: false,
                resource: 'dispatch_addresses',
                recordId: null,
            }
        },
        created() {
            this.title = 'Direcciones de llegada'
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
            }
        }
    }
</script>
