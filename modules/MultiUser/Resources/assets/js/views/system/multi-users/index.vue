<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>{{ title }}</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <button class="btn btn-custom btn-sm  mt-2 mr-2" type="button" @click.prevent="clickCreate()"><i
                    class="fa fa-plus-circle"></i> Agregar
                </button>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <th>#</th>
                        <th>Empresa Origen</th>
                        <th>Empresa Destino</th>
                        <!-- <th>Subdominio </th> -->
                        <th class="text-center">Usuario</th>
                        <th class="text-center">Perfil</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                    <tr slot-scope="{ index, row }">
                        <td>{{ index }}</td>
                        <td>
                            {{ row.client_origin_full_name }}
                            <br/>
                            <small><b> {{ row.origin_hostname }} </b> </small>
                        </td>
                        <td>
                            {{ row.client_destination_full_name }}
                            <br/>
                            <small><b> {{ row.destination_hostname }} </b> </small>
                        </td>
                        <!-- <td>{{ row.hostname }}</td> -->
                        <td class="text-center">{{ row.user_full_name }}</td>
                        <td class="text-center">{{ row.description_type }}</td>

                        <td class="text-right">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickDelete(row.id)">Eliminar</button>
                        </td>
                    </tr>
                </data-table>
            </div>

            <multi-user-form :recordId="recordId"
                          :showDialog.sync="showDialog"></multi-user-form>


        </div>
    </div>
</template>

<script>

import MultiUserForm from './form.vue'
import DataTable from '@components/DataTable.vue'
import { deletable } from '@mixins/deletable'

export default {
    mixins: [deletable],
    components: {
        MultiUserForm,
        DataTable
    },
    data() {
        return {
            title: null,
            showDialog: false,
            resource: 'multi-users',
            recordId: null,
        }
    },
    created()
    {
        this.title = 'Multi Usuarios'
    },
    computed:
    {
    },
    methods:
    {
        clickCreate(recordId = null)
        {
            this.recordId = recordId
            this.showDialog = true
        },
        clickDelete(id) {
            this.destroy(`/${this.resource}/${id}`).then(() =>
                    // this.$eventHub.$emit('reloadData')
                    console.log(id)
                ).finally(() =>
                    this.$eventHub.$emit('reloadData')
                )
        },
    }
}
</script>
