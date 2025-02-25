<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/reports/kardex">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-building-warehouse"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21v-13l9 -4l9 4v13" /><path d="M13 13h4v8h-10v-6h6" /><path d="M13 21v-9a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v3" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Consulta kardex </span></li>
            </ol>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Consulta kardex</h3>
            </div> -->
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12  ">
                        <el-tabs v-model="activeName">
                            <el-tab-pane label="General" name="first">
                                <tenant-report-kardex-index></tenant-report-kardex-index>
                            </el-tab-pane>
                            <el-tab-pane label="Por Lotes" name="second">
                                <tenant-report-kardex-lots></tenant-report-kardex-lots>
                            </el-tab-pane>
                            <el-tab-pane label="Por series" name="third">
                                <tenant-report-kardex-series></tenant-report-kardex-series>
                            </el-tab-pane>
                        </el-tabs>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {mapActions, mapState} from "vuex/dist/vuex.mjs";

export default {
    props:['configuration'],
    components: {},
    data() {
        return {
            activeName: "first"
        };
    },
    created() {
        this.loadConfiguration()
        this.$store.commit('setConfiguration', this.configuration)
    },
    methods:{
        ...mapActions([
            'loadConfiguration',
        ]),
    },
};
</script>
