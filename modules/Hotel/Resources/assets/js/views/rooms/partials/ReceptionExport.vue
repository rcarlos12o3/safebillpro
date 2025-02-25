<template>
    <el-dialog :title="title" :visible="showDialog" @close="clickClose" @open="created">
        <div class="">
            <div class="row mt-2">
                <div class="col-lg-4 col-md-4 pb-4">
                    <div class="form-group">
                        <label class="control-label">Fecha inicio </label>

                        <el-date-picker
                            v-model="search.d_start"
                            type="date"
                            style="width: 100%;"
                            placeholder="Buscar"
                            value-format="yyyy-MM-dd"
                        >
                        </el-date-picker>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 pb-4">
                    <div class="form-group">
                        <label class="control-label">Fecha término</label>

                        <el-date-picker
                            v-model="search.d_end"
                            type="date"
                            style="width: 100%;"
                            placeholder="Buscar"
                            value-format="yyyy-MM-dd"
                            :picker-options="pickerOptionsDates"
                        >
                        </el-date-picker>
                    </div>
                </div>
                <div v-if="user==='admin'" class="col-lg-4 col-md-4 pb-4">
                    <div class="form-group">
                        <label class="control-label" for="establishment">Sucursal</label>
                        <el-select
                            type="text"
                            id="establishment"
                            v-model="search.establishment_id"
                            :class="{ 'is-invalid': errors.establishment_id }"
                        >
                            <el-option v-for="item in establishments" :key="item.id" :value="item.id" :label="item.description">
                            {{ item.description }}
                            </el-option>
                        </el-select>
                    </div>
                </div>
                 <div class="col-lg-2 col-md-2 pb-4">
                    <div class="form-group"  style="padding: 2.5%;"> <br>

                    </div>
                </div>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button type="warning" @click="clickClose">Cancelar</el-button>
            <el-button type="primary" @click="downloadReport">Descargar</el-button>
        </span>
    </el-dialog>
</template>

<script>
export default {
    props: [
        "showDialog",
        "userType",
        "establishmentId"
    ],
    data() {
        return {
            title: "Reporte recepción",
            resource: "hotels",
            search: {
                d_start: moment().startOf('month').format('YYYY-MM-DD'),
                d_end: moment().endOf('month').format('YYYY-MM-DD')
            },
            user: 'seller',
            establishments: {},
            pickerOptionsDates: {
                disabledDate: time => {
                    time = moment(time).format("YYYY-MM-DD");
                    return this.search.d_start > time;
                }
            },
            errors: {},
        };
    },
    methods: {
        clickClose() {
            this.$emit("update:showDialog", false);
        },
        downloadReport()
        {
            if(this.search.d_end && this.search.d_start && this.search.establishment_id){

                 window.open(`/${this.resource}/reception/report/${this.search.d_start}/${this.search.d_end}/${this.search.establishment_id}`, '_blank');
            } else {
                this.$message.error('Debe completar el formulario para generar un reporte');
            }
        },
        created() {
            this.user = this.userType;
            this.$http.get('/establishments/records').then((response) => {
                this.establishments = response.data.data;
            });
            this.search.establishment_id = this.establishmentId;
        },
    }
};
</script>
