<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/list-reports">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-analytics"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17l0 -5" /><path d="M12 17l0 -1" /><path d="M15 17l0 -3" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Consulta de documentos por cliente </span></li>
            </ol>
        </div>
        <div class="card mb-0 pt-2 pt-md-0 tab-content-default row-new">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Consulta de documentos por cliente</h3>
            </div> -->
            <div class="card mb-0">
                    <div class="card-body">
                        <data-table :resource="resource">
                            <tr slot="heading">
                                <th class="">#</th>
                                <th class="">Fecha</th>
                                <th class="">Tipo Documento</th>
                                <th class="">Moneda</th>
                                <th class="">Número de Placa</th>
                                <th class="">Serie</th>
                                <th class="">Número</th>
                                <th class="">Monto</th>
                            <tr>
                            <tr slot-scope="{ index, row }">
                                <td>{{ index }}</td>
                                <td>{{row.date_of_issue}}</td>
                                <td>{{row.document_type_description}}</td>
                                <td>{{row.currency_type_id}}</td>
                                <td>{{row.plate_number}}</td>
                                <td>{{row.series}}</td>
                                <td>{{row.alone_number}}</td>
                                <td>{{ (row.document_type_id == '07') ? ( (row.total == 0) ? '0.00': '-'+row.total) : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total) }}</td>
                            </tr>
    
                        </data-table>
    
    
                    </div>
            </div>
    
        </div>
    </div>
</template>

<script>

    import DataTable from '../../components/DataTableCustomers.vue'

    export default {
        components: {DataTable},
        data() {
            return {
                resource: 'reports/customers',
                form: {},

            }
        },
        async created() {
        },
        methods: {


        }
    }
</script>
