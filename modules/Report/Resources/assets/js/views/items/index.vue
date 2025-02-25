<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/list-reports">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-analytics"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17l0 -5" /><path d="M12 17l0 -1" /><path d="M15 17l0 -3" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Consulta de documentos por producto </span></li>
            </ol>
        </div>
        <div class="card mb-0 pt-2 pt-md-0 tab-content-default row-new">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Consulta de documentos por producto</h3>
            </div> -->
            <div class="card mb-0">
                    <div class="card-body">
                        <data-table
                            :defaultType="defaultType"
                            :resource="resource">
                            <tr slot="heading">
                                <th class="">#</th>
                                <th class="">Fecha</th>
                                <th class="">Tipo Documento</th>
                                <th class="">Serie</th>
                                <th class="">Número</th>
                                <th class="">N° Documento</th>
                                <th class="">Cliente</th>
                                <th class="" v-if="defaultType!== 'purchase'">Plataforma</th>
                                <th class="">Cantidad</th>
                                <th class="">Monto</th>
                            <tr>
                            <tr slot-scope="{ index, row }">
                                <td>{{ index }}</td>
                                <td>{{row.date_of_issue}}</td>
                                <td>{{row.document_type_description}}</td>
                                <td>{{row.series}}</td>
                                <td>{{row.alone_number}}</td>
                                <td>{{row.customer_number}}</td>
                                <td>{{row.customer_name}}</td>
                                <td v-if="defaultType!== 'purchase'">{{row.web_platform_name}}</td>
                                <td>{{row.quantity}}</td>
                                <td>{{ (row.document_type_id == '07') ? ( (row.total == 0) ? '0.00': '-'+row.total) : ((row.document_type_id!='07' && (row.state_type_id =='11'||row.state_type_id =='09')) ? '0.00':row.total) }}</td>
                            </tr>
    
                        </data-table>
    
    
                    </div>
            </div>
    
        </div>
    </div>
</template>

<script>

    import DataTable from '../../components/DataTableItems.vue'

    export default {
        components: {DataTable},
        props:[
            'defaultType',
            'configuration',
        ],
        data() {
            return {
                resource: 'reports/items',
                form: {},
                config:{},
                title: 'Consulta de documentos por producto',
            }
        },
        async created() {

            if(this.defaultType === 'purchase'){
                this.title = 'Compra - ' + this.title;
            }
            if(this.configuration !== undefined && this.configuration !== null && this.configuration.length > 0){
                this.$setStorage('configuration',this.configuration)
            }
            this.config = this.$getStorage('configuration');
        },
        methods: {


        }
    }
</script>
