<template>
    <div class="tab-content-default row-new">
        <div class="page-header pr-0">
            <h2><a href="/account/format">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-chart-histogram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 3v18h18" /><path d="M20 18v3" /><path d="M16 16v5" /><path d="M12 13v8" /><path d="M8 16v5" /><path d="M3 11c6 0 5 -5 9 -5s3 5 9 5" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>{{ title }}</span></li>
            </ol>
        </div>

        <div v-loading="loading" class="card mb-0 pt-2 pt-md-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">{{ title }}</h3>
            </div> -->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-modern">
                        <label class="control-label">Periodo</label>
                        <el-date-picker
                            v-model="form.month"
                            :clearable="false"
                            format="MM/yyyy"
                            type="month"
                            value-format="yyyy-MM"></el-date-picker>
                    </div>
                    <!--
                    <div class="col-md-3">
                        <label>Moneda</label>

                        <el-select v-model="form.currency_type_id"
                                   :loading="loading_submit"
                                   filterable
                                   learable
                                   placeholder="Moneda del reporte"
                                   popper-class="el-select-currency"
                        >
                            <el-option v-for="option in currencies"
                                       :key="option.id"
                                       :label="option.description"
                                       :value="option.id"></el-option>
                        </el-select>

                    </div>
                    -->
                    <div class="col-md-3 form-modern">
                        <label class="control-label">Tipo</label>
                        <el-select v-model="form.type">
                            <el-option
                                key="sale"
                                label="Venta"
                                value="sale"></el-option>
                            <el-option
                                key="purchase"
                                label="Compra"
                                value="purchase"></el-option>
                            <el-option
                                key="garage-gll"
                                label="Venta (Grifo)"
                                value="garage-gll"></el-option>
                        </el-select>
                    </div>
                    
                    <div class="col-md-3 " v-if="form.type == 'sale'">
                        <el-checkbox class="checkbox mt-4" v-model="form.add_state_type">Agregar columna estado - CPE
                        </el-checkbox>
                    </div>

                </div>
            </div>
            <div class="form-actions text-right pt-2">
                <el-button :loading="loading_submit"
                           class="btn btn-primary btn-submit-default mr-3 mb-3"
                           type="primary"
                           @click.prevent="clickDownload">
                    <template v-if="loading_submit">
                        Generando...
                    </template>
                    <template v-else>
                        Generar
                    </template>
                </el-button>
            </div>
            <!--</div>-->
        </div>
    </div>
</template>

<script>
    import queryString from 'query-string'
import {mapActions, mapState} from "vuex";
 
    export default {

    props: [
        'currencies',
        'configuration',

    ],
    computed: {
        ...mapState([
            'config',
            'currencys'
        ])
    },
        data() {
            return {
                loading: false,
                loading_submit: false,
                title: null,
                resource: 'account',
                error: {},
                form: {},
            }
        },
    created() {
            this.title = 'Generar';
        this.$store.commit('setConfiguration', this.configuration);
        this.loadConfiguration()
    },
    mounted() {
        this.initForm();
        },
        methods: {

        ...mapActions([
            'loadConfiguration',
        ]),
            initForm() {
                this.errors = {};
                this.form = {
                    month: moment().format('YYYY-MM'),
                    type: 'sale',
                    add_state_type: false
                }
            },
            clickDownload() {
                this.loading_submit = true;
                let query = queryString.stringify({
                    ...this.form
                });
                window.open(`/${this.resource}/format/download?${query}`, '_blank');
                this.loading_submit = false;
            }
        }
    }
</script>
