<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="#"><i class="fas fa-cogs"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Configuración</span></li>
                <li><span class="text-muted">Plantilla Ticket PDF</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <button class="btn btn-custom btn-sm  mt-2 mr-2"
                        type="button"
                        @click="refreshTickets"><i class="el-icon-refresh"></i>
                    Actualizar listado
                </button>
            </div>
        </div>
        <div class="card tab-content-default row-new">
            <div class="card-body pb-5">
                <h5> <i class="fa fa-info-circle"></i>  Seleccione un sucursal para ver su plantilla</h5>
                <div class="row">
                    <div class="col-3 form-modern">
                        <label class="control-label">Sucursal</label>
                        <el-select v-model="form.establishment_id"
                                   @change="changeEstablishment()">
                            <el-option v-for="option in establishments"
                                       :key="option.id"
                                       :label="option.description"
                                       :value="option.id"></el-option>
                        </el-select>
                    </div>
                    <div class="col-3 form-modern">
                        <label class="control-label">Plantilla actual</label>
                        <el-input v-model="form.current_format"
                                  readonly></el-input>
                        <small v-if="form.current_format"
                               style="cursor:pointer">
                            <!-- <a @click="viewModalImage(form.current_format)">
                                Ver plantilla
                            </a> -->
                        </small>
                    </div>
                </div>
                <div class="row justify-content-center" v-if="form.establishment_id">
                    <div v-for="(template, index) in formatos" class="pdf-template-content m-2 mt-4 my-2 col-sm-12 col-md-4 col-lg-3 col-xl-2" :key="template+index">
                        <el-card class="el-card-template h-100" :id="template.id"
                                 :class="['pdf-template-border', {'active-border': form.current_format === template.name}]"
                                 :body-style="{ padding: '0px' }">
                            <div style="padding: 14px; border-bottom: 2px solid #f7f8fa;">
                                <span class="text-bold">Plantilla: </span>
                                <span class="text-center">{{ template.name }}</span>
                                <div v-if="form.establishment_id"
                                     class="bottom clearfix text-right m-0" style="margin-top: auto !important;">
                                    <el-radio v-model="form.current_format"
                                              :label="template.name"
                                              @change="changeFormat(template.name)"
                                              :class="['radio-button', { 'active-button': form.current_format === template.name }]">
                                        <span v-if="form.current_format == template.name">Plantilla activa</span>
                                        <span v-else>Activar plantilla</span>
                                    </el-radio>
                                </div>
                            </div>
                            
                            <i class="fas fa-search-plus icon-overlay" @click="viewImage(template)"></i>
                    
                            <a class="image-direccion" @click="viewImage(template)">
                                <img :src="path.origin+'/templates/pdf/'+template.name+'/ticket.png'"
                                     class="image"
                                     style="width: 100%">
                            </a>
                        </el-card>
                    </div>
                </div>
            </div>
        </div>
        <el-dialog
            :visible.sync="modalImage"
            width="100">
            <div class="d-flex align-items-center justify-content-start" style="margin-top: -30px !important;">
                <h4 class="text-bold">Plantilla:</h4>
                <span style="font-size: 16px;" class="text-center ml-2">{{ template.name }}</span>
            </div>
            <span>
                <div class="block">
                    <el-carousel arrow="always" :interval="10000" height="550px">
                        <div style="overflow: auto;" class="img-scroll bg-light text-center">
                            <img :src="path.origin+'/templates/pdf/'+template.name+'/ticket.png'"
                                 class="image"
                                 style="width: 50%; height: auto;">
                        </div>
                    </el-carousel>
                </div>
            </span>
            <span slot="footer"
                  class="dialog-footer">
                <el-button class="second-buton" @click="modalImage = false">Cerrar</el-button>
                <el-button v-if="form.establishment_id"
                           type="primary"
                           @click="changeFormat(template.name)">Activar</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<style scoped>
.el-carousel__item:nth-child(2n) {
background-color: #99a9bf;
}
.el-carousel__item:nth-child(2n+1) {
background-color: #d3dce6;
}
.pdf-template-border {
    border: 2px solid transparent;
}
.pdf-template-border.active-border {
    border: 2px solid #409eff;
}
.radio-button {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px 20px;
    border: 1px solid #DCDFE6;
    border-radius: 4px;
    background-color: #fff;
    color: #606266;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s;
}
.radio-button:hover {
    border-color: #c6e2ff;
    background-color: #ecf5ff;
    color: #409EFF;
} 
.radio-button.el-radio--checked {
    background-color: #409eff;
    color: #fff;
} 
.radio-button span {
    font-weight: normal;
}
.radio-button.el-radio--disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.radio-button.active-button {
    background-color: #0074ff;
    border-color: #0074ff;
} 
.el-radio__input.is-checked + .el-radio__label {
    color: #fff !important;
}
.pdf-template-content {
    transition: transform 0.3s, opacity 0.3s;
}
.el-card-template {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s ease-in-out, opacity 0.2s ease-in-out;
}
.el-card-template::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(12, 114, 134, 0);
    transition: background-color 0.3s ease-in-out;
    pointer-events: none;
}
.pdf-template-content:hover > .el-card-template::before {
    background-color: rgba(0, 115, 255, 0.385);
}

.pdf-template-content:hover > .el-card-template {
    transform: scale(1.02);
}
.icon-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
    z-index: 10;
    cursor: pointer;
    font-size: 30px;
}
.pdf-template-content:hover .icon-overlay {
    opacity: 1;
}
.img-scroll{
    overlay: auto;
    height: 550px;
}
.img-scroll::-webkit-scrollbar {
    width: 8px;
}
.img-scroll::-webkit-scrollbar-track {
    background: #f0f9ff;
    border-radius: 8px;
}
.img-scroll::-webkit-scrollbar-thumb {
    background: #5fa7fe;
    border-radius: 8px;
}
.img-scroll::-webkit-scrollbar-thumb:hover {
    background: #0074ff;
}
.image-direccion{
    cursor: pointer;
}
</style>

<script>

export default {
    props: [
        'path_image',
        'typeUser',
        'establishments'
    ],

    data() {
        return {
            loading_submit: false,
            resource: 'configurations',
            errors: {},
            form: {},
            formatos: [],
            path: location,
            modalImage: false,
            template: {
                name: '',
                urls: {}
            }
        }
    },
    async created() {
        await this.$http.get(`/${this.resource}/templates/ticket/records`).then(response => {
            if (response.data !== '') this.formatos = response.data.formats
        });

        console.log(this.establishments[0]);
    },
    methods: {
        changeFormat(value) {
            this.modalImage = false
            this.form = {
                formats: value,
                establishment: this.form.establishment_id,
            }

            this.$http.post(`/${this.resource}/templates/ticket/update`, this.form).then(response => {
                this.$message.success(response.data.message);
                location.reload()
            })

        },
        changeEstablishment() {
            var establishment = this.form.establishment_id;
            var selected = _.filter(this.establishments, {'id': establishment})[0];
            this.form.current_format = selected.template_ticket_pdf;
        },
        refreshTickets() {
            this.$http.all([
                this.$http.get(`/${this.resource}/templates/ticket/refresh`),
                this.$http.get(`/${this.resource}/addSeeder`)
            ]).then(
                this.$http.spread((...responses) => {
                    this.$message.success(responses[0].data.message);
                    this.$message.success(responses[1].data.message);
                    location.reload()
                })
            )
        },
        viewImage(template) {
            this.template = template
            this.modalImage = true
        },
        viewModalImage(name) {
            this.template = this.formatos.filter(template => template.name == name)[0]
            this.modalImage = true
        }
    }
}
</script>
