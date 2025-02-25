<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/accounting_ledger">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-chart-histogram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 3v18h18" /><path d="M20 18v3" /><path d="M16 16v5" /><path d="M12 13v8" /><path d="M8 16v5" /><path d="M3 11c6 0 5 -5 9 -5s3 5 9 5" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Libro Mayor </span></li>
            </ol>
        </div>
        <div class="card tab-content-default row-new">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Libro Mayor</h3>
            </div> -->
            <div class="card-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-12 col-xs-12 col-md-6 col-lg-4 col-xl-3">
                            <div :class="{'has-danger': errors.currency_type_id}"
                                 class="form-group">
                                <label class="control-label">Consultar Registro</label>
                                <el-select v-model="month_end">
                                    <el-option v-for="option in months"
                                               :key="option.id"
                                               :label="option.description"
                                               :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.currency_type_id"
                                       class="form-control-feedback"
                                       v-text="errors.currency_type_id[0]"></small>
                            </div>
                        </div>
    
                    </div>
                    <div class="form-actions  pt-2">
    
                        <el-button class="submit"
                                   type="success"
                                   @click.prevent="clickDownload('excel')"><i class="fa fa-file-excel"></i> Exporta Excel
                        </el-button>
    
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {

    data() {
        return {
            loading_submit: false,
            resource: 'accounting_ledger',
            errors: {},
            form: {},
            months: [],
            month_end: null,

        }
    },
    async created() {
        await this.initForm();

        await this.$http.post(`/${this.resource}/record`).then(response => {
            this.months=response.data.months;
        });

    },
    methods: {
        initForm() {
            this.errors = {};
            this.month_end = null
        },
        clickDownload() {

            window.open(`${this.resource}/excel/?month_end=${this.month_end}`, '_blank');
        },
    }
}
</script>
