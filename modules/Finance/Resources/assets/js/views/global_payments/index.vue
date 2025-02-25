<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/finances/global-payments">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calculator"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 3m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 7m0 1a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-6a1 1 0 0 1 -1 -1z" /><path d="M8 14l0 .01" /><path d="M12 14l0 .01" /><path d="M16 14l0 .01" /><path d="M8 17l0 .01" /><path d="M12 17l0 .01" /><path d="M16 17l0 .01" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Pagos </span></li>
            </ol>
        </div>
        <div class="card tab-content-default row-new mb-0 pt-2 pt-md-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Pagos</h3>
            </div> -->
            <div class="card mb-0">
                    <div class="card-body">
                        <data-table :resource="resource" :isAsynchronous="true">
                            <tr slot="heading">
                                <!-- <th class="">#</th> -->
                                <th class="">Adquiriente</th>
                                <th class="">Documento/Transacción</th>
                                <th class="">Moneda</th>
                                <th class="">Tipo</th>
                                <th class="">Destino</th>
                                <th class="">Cuenta/Caja</th>
                                <th class="">F. Pago</th>
                                <th class="">Método</th>
                                <th class="">Referencia</th>
                                <th class="">Responsable</th>
                                <th class="">Pago</th>
    
                            </tr>
                            <tr slot-scope="{ index, row }">
                                <!-- <td>{{ index }}</td> -->
                                <td>{{ row.person_name }}<br/><small v-text="row.person_number"></small></td>
                                <td>
                                    {{row.number_full}}<br/>
                                    <small v-text="row.document_type_description"></small>
                                </td>
                                <td>{{row.currency_type_id}}</td>
                                <td>{{row.instance_type_description}}</td>
                                <td>{{row.destination_description}}</td>
                                <td>{{row.cci}}</td>
                                <td>{{row.date_of_payment}}</td>
                                <td>{{row.payment_method_type_description}}</td>
                                <td>
                                    {{row.reference}}
    
                                    <template v-if="row.file_url">
                                        <div style="min-width: 125px !important">
                                            <br>
                                            <a href="#" class="control-label font-weight-bold text-info"  @click.prevent="clickDownloadFile(row.file_url)">
                                                [<i class="fas fa-fw fa-file-download"></i> Descargar]
                                            </a>
                                        </div>
                                    </template>
                                </td>
                                <td>{{row.user_name}}</td>
                                <td>{{row.total}}</td>
                            </tr>
                        </data-table>
    
    
                    </div>
            </div>
    
        </div>
    </div>
</template>

<script>

    import DataTable from '../../components/DataTableGlobalPayment.vue'

    export default {
        components: {DataTable},
        data() {
            return {
                resource: 'finances/global-payments',
                form: {},

            }
        },
        async created() {
        },
        methods: {
            clickDownloadFile(file_url) 
            {
                window.open(file_url, "_blank")
            },

        }
    }
</script>
