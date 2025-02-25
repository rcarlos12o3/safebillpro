<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/machine-production">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-building-factory-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21h18" /><path d="M5 21v-12l5 4v-4l5 4h4" /><path d="M19 21v-8l-1.436 -9.574a.5 .5 0 0 0 -.495 -.426h-1.145a.5 .5 0 0 0 -.494 .418l-1.43 8.582" /><path d="M9 17h1" /><path d="M14 17h1" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> {{ title }} </span></li>
            </ol>
        </div>
        <div class="card tab-content-default row-new mb-0 pt-2 pt-md-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">
                    {{title}}
                </h3>
            </div> -->
            <div class="tab-content tab-content-default">
                <form autocomplete="off"
                      @submit.prevent="submit">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-3">
                                <div :class="{'has-danger': errors.machine_type_id}"
                                     class="form-group">
                                    <label class="control-label">
                                        Tipo de maquina
                                    </label>
                                    <el-select v-model="form.machine_type_id"  >
                                        <el-option
                                            v-for="option in machine_types"
                                            :key="option.id"
                                            :value="option.id"
                                            :label="option.name"></el-option>
                                    </el-select>
    
                                    <small v-if="errors.machine_type_id"
                                           class="form-control-feedback"
                                           v-text="errors.machine_type_id[0]"></small>
                                </div>
                            </div>
    
    
                            <div class="col-sm-6 col-md-3">
                                <div :class="{'has-danger': errors.name}"
                                     class="form-group">
                                    <label class="control-label">
                                        Nombre
                                    </label>
                                    <el-input v-model="form.name"></el-input>
                                    <small v-if="errors.name"
                                           class="form-control-feedback"
                                           v-text="errors.name[0]"></small>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div :class="{'has-danger': errors.brand}"
                                     class="form-group">
                                    <label class="control-label">
                                        Marca
                                    </label>
                                    <el-input v-model="form.brand"></el-input>
                                    <small v-if="errors.brand"
                                           class="form-control-feedback"
                                           v-text="errors.brand[0]"></small>
                                </div>
                            </div>
    
                            <div class="col-sm-6 col-md-3">
                                <div :class="{'has-danger': errors.model}"
                                     class="form-group">
                                    <label class="control-label">
                                        Modelo
                                    </label>
                                    <el-input v-model="form.model"></el-input>
                                    <small v-if="errors.model"
                                           class="form-control-feedback"
                                           v-text="errors.model[0]"></small>
                                </div>
                            </div> <div class="col-sm-6 col-md-3">
                                <div :class="{'has-danger': errors.closing_force}"
                                     class="form-group">
                                    <label class="control-label">
                                        Fuerza de cierre
                                    </label>
                                    <el-input v-model="form.closing_force"></el-input>
                                    <small v-if="errors.closing_force"
                                           class="form-control-feedback"
                                           v-text="errors.closing_force[0]"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions text-right mt-4">
                        <el-button
                            class="second-buton btn btn-default second-buton-default"
                            @click.prevent="close()">
                            Cancelar
                        </el-button>
                        <el-button
                            class="btn btn-primary btn-submit-default"
                            :loading="loading_submit"
                            native-type="submit"
                            type="primary">
                            {{ (id) ? 'Actualizar' : 'Guardar' }}
                        </el-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>


export default {
    props: {
        id: {
            type: Number,
            required: false,
        },
    },
    components: {},
    data() {
        return {
            resource: 'machine-production',
            title: 'Nueva maquina',

            showDialog: false,
            showDialogNewPerson: false,
            showDialogOptions: false,
            loading_submit: false,
            errors: {},
            form: {
                id:null,
                machine_type_id:null,
                brand:'',
                model:'',
                closing_force:'',
            },
            aux_supplier_id: null,
            machine_types: [],
            unit_types: [],
            currency_types: [],
            suppliers: [],
            establishment: {},
            currency_type: {},
            mill_method_types: [],
            payment_destinations: [],
            mill_reasons: [],
            millNewId: null
        }
    },
    mounted() {
        this.getTable();
        this.initForm()
    },
    methods: {
        showAddItemModal() {
            this.showDialog = true;
        },
        isUpdate() {
            this.title= 'Nueva maquina';
            if (this.id) {
                this.$http.get(`/${this.resource}/record/${this.id}`)
                    .then(response => {
                        this.title= 'Editar maquina';
                        this.form = response.data
                    })
            }

        },
        getTable(){
            this.$http.get(`/${this.resource}/tables`)
                .then(response => {
                    this.machine_types = response.data.machine_types
                })

        },
        initForm() {
            this.errors = {}
            this.form = {
                id: this.id,
                machine_type_id:null,
                brand:'',
                model:'',
                closing_force:'',
            }
            this.isUpdate()

        },
        submit() {

            this.loading_submit = true
            this.$http.post(`/${this.resource}`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.initForm()
                        this.$message.success(response.data.message)
                        this.onClose()
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        this.$message.error(error.response.data.message)
                    }
                })
                .finally(() => {
                    this.loading_submit = false
                })
        },
        onClose() {
            window.location.href = '/machine-production'
            this.$emit("update:visible", false);
        },
    }
}
</script>
