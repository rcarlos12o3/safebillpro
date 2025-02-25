<template>
    <div>
        <div class="page-header pr-0">
            <h2><a :href="direccionUrl">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calculator"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 3m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 7m0 1a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-6a1 1 0 0 1 -1 -1z" /><path d="M8 14l0 .01" /><path d="M12 14l0 .01" /><path d="M16 14l0 .01" /><path d="M8 17l0 .01" /><path d="M12 17l0 .01" /><path d="M16 17l0 .01" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> {{ currentTitle }} </span></li>
            </ol>
        </div>
        <div class="cardtab-content-default tab-content-default row-new mb-0 pt-2 pt-md-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">
                    {{ currentTitle}}
                </h3>
            </div> -->
            <div class="card mb-0">
                <div class="card-body">
                    <data-table
                        :configuration="config"
                        :filter="filter"
                        :ismovements="ismovements"
                        :resource="resource">
                        <tr slot="heading">
                            <th class="">#</th>
                            <th :class="(filter.column === 'date_of_payment')?'text-info':''"
                                @click="ChangeOrder('date_of_payment')">
                                Fecha
                            </th>
                            <th :class="(filter.column === 'person_name')?'text-info':''"
                                @click="ChangeOrder('person_name')">
                                Adquiriente
                            </th>
                            <th :class="(filter.column === 'number_full')?'text-info':''"
                                @click="ChangeOrder('number_full')">
                                Documento/Transacción
                            </th>
                            <th :class="(filter.column === 'items')?'text-info':''"
                                @click="ChangeOrder('items')">
                                Detalle
                                <el-tooltip
                                    class="item"
                                    content="Aplica a Ingresos/Gastos"
                                    effect="dark"
                                    placement="top-start"
                                >
                                    <i class="fa fa-info-circle"></i>
                                </el-tooltip>
                            </th>
                            <th :class="(filter.column === 'currency_type_id')?'text-info':''"
                                @click="ChangeOrder('currency_type_id')">
                                Moneda
                            </th>
                            <th
                                :class="(filter.column === 'destination_name')?'text-info':''"
                                v-if="showDestination!== true"
                            >
                                Destino
                            </th>
                            <th :class="(filter.column === 'instance_type_description')?'text-info':''"
                                @click="ChangeOrder('instance_type_description')"
                                v-else
                            >
                                Tipo *
                            </th>
                            <th class="">
                                Ingresos
                            </th>
                            <th class="">
                                Gastos
                            </th>
                            <th class="">
                                Saldo
                            </th>
                        </tr>
                        <tr slot-scope="{ index, row }">
                            <!-- # -->
                            <td>{{ row.index }}</td>
                            <!-- Fecha -->
                            <td>{{ row.date_of_payment }}</td>
                            <!-- Adquiriente -->
                            <td>
                                {{ row.person_name }}<br/><small
                                v-text="row.person_number"
                            ></small>
                            </td>
                            <!-- Documento/Transacción -->
                            <td>
                                {{ row.number_full }}<br/>
                                <small v-text="row.document_type_description"></small>
                            </td>
                            <!-- Detalle -->
                            <td>
                                <template v-for="(item, index) in row.items">
                                    <label :key="index">- {{ item.description }}<br/></label>
                                </template>
                            </td>
                            <!-- Moneda -->
                            <td>{{ row.currency_type_id }}</td>
                            <!-- Destino -->
                            <td
                                v-if="showDestination!== true"
                            >
                                {{row.destination_name}}
                            </td>
                            <!-- Tipo -->
                            <td
                                v-else
                            >{{ row.instance_type_description }}</td>
    
                            <!-- Ingresos -->
                            <td>
                                <label v-show="row.input > 0 || row.input != '-'">S/ </label
                                >{{ row.input }}
                            </td>
                            <!-- Gastos -->
                            <td>
                                <label v-show="row.output > 0 || row.output != '-'">S/ </label
                                >{{ row.output }}
                            </td>
                            <!-- Saldo -->
                            <td>
                                <label v-show="row.balance > 0 || row.balance != '-'">S/ </label
                                >{{ row.balance }}
                            </td>
                        </tr>
                    </data-table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import DataTable from "../../components/DataTableMovement.vue";
import {mapActions, mapState} from "vuex/dist/vuex.mjs";

export default {
    components: {
        DataTable
    },
    props: [
        'configuration',
        'ismovements',
    ],
    data() {
        return {
            title:'Movimientos de ingresos y egresos',
            resource: "finances/movements",
            form: {},
            filter: {
                column: '',
                order: ''
            }
        };
    },computed:{
        direccionUrl() {
            return this.showDestination ? "/finances/movements" : "/finances/transactions";
        },
        ...mapState([
            'config',
        ]),
        showDestination:function(){
            return !(this.ismovements !== undefined && this.ismovements === 0);
        },
        currentTitle:function(){
            if(this.showDestination!== true) {
                this.title = "Reporte de transacciones";
                this.resource = 'finances/transactions'
            }else {
                this.title = 'Movimientos de ingresos y egresos';
            }
            return this.title
        }
    },
    created() {
        if(this.ismovements === undefined) this.ismovements = 1
        this.ismovements = parseInt(this.ismovements)
        this.$store.commit('setConfiguration', this.configuration);
        this.loadConfiguration()
        this.currentTitle

    },
    methods: {
        ...mapActions([
            'loadConfiguration',
        ]),
        ChangeOrder(col) {
            if (this.filter.order !== 'DESC') {
                this.filter.order = 'DESC'
            } else {
                this.filter.order = 'ASC'
            }
            this.filter.column = col
            this.$eventHub.$emit('filtrado', this.filter)
            console.log('sale')
        }
    }
};
</script>
