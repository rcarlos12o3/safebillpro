<template>
    <el-dialog width="50%" :title="title" :visible="showDialog" @close="close" @open="getData">
        <div class="form-body">
            <div class="row">
                <div class="col-md-12">
                    <h5><b>Crear una copia de esta cuenta:</b></h5>
                    <span>Crea una réplica exacta de tu base de datos actual para fines de demostración.</span>
                  </div>
                <div class="col-md-12">
                    <div class="row ">
                        <div class="col-md-8 form-group" :class="{'has-danger': errors.name}">
                            <label class="control-label">Nombre </label>
                            <el-input v-model="form.name"></el-input>
                            <small class="form-control-feedback" v-if="errors.name" v-text="errors.name[0]"></small>
                        </div>
                        <div class="col-md-4 form-group d-flex align-items-center">
                            <br>
                            <el-button type="primary" :loading="loading_create" :disabled="loading_create" @click.prevent="saveBackupDemo()" class="mt-3">
                                Crear Demo
                            </el-button>
                        </div>
                    </div>
                    
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h5><b>Restaurar demo:</b></h5>
                    <span>Selecciona una demo de la lista para restaurar todos los datos originales de tal sistema.</span>
                    <span>Util para crear entornos de pruebas rápidas para rubros específicos o personalizados.</span>
                </div>
                <div class="col-md-12">
                    <div class="row ">
                        <div class="col-md-8 form-group" :class="{'has-danger': errors.restore_dbname}">
                            <label class="control-label">Demos</label>
                            <el-select v-model="form.restore_dbname_bkdemo">
                                <el-option v-for="option in restore_databases" :key="option.name" :value="option.name" :label="option.name + ' (' + option.type + ')' "></el-option>
                            </el-select>
                            <small class="form-control-feedback" v-if="errors.restore_dbname" v-text="errors.restore_dbname[0]"></small>
                        </div>
                        <div class="col-md-4 form-group d-flex align-items-center">
                            <br>
                            <el-button type="primary" :loading="loading_restore" :disabled="loading_restore" @click.prevent="restoreBackupDemo()" class="mt-2">
                                Restaurar Demo
                            </el-button>
                        </div>
                    </div>
                    
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h5><b>Habilitar cron para resetear esta cuenta:</b></h5>
                    <span>Cuando esta opción esta habilitada el sistema restaurará automáticamente los datos originales de la demo seleccionada.</span>
                </div>
                <div class="col-md-12">
                    <label class="control-label">
                      Habilitar cron (Se ejecuta durante la madrugada todos los días)
                    </label>
                    <div :class="{'has-danger': errors.enabled_cron_restore_bkdemo}"
                          class="form-group">
                      <el-switch v-model="form.enabled_cron_restore_bkdemo"
                                  active-text="Si"
                                  inactive-text="No"
                                  @change="enabledCronDemo()"></el-switch>
                      <small v-if="errors.enabled_cron_restore_bkdemo"
                              class="form-control-feedback"
                              v-text="errors.enabled_cron_restore_bkdemo[0]"></small>
                    </div>
                    
                </div>
            </div>

        </div>
    </el-dialog>

</template>

<script>

    import {deletable} from '../../../../mixins/deletable'

    export default {
        props: ['showDialog', 'clientId'],
        mixins: [deletable],

        data() {
            return {
                title: 'Configuración de entornos para demos y pruebas',
                recordId: null,
                resource: 'demo_environments',
                records: [],
                payment_method_types: [],
                card_brands: [],
                showAddButton: true,
                has_card: false,
                client: {},
                error: {},
                form: {},
                loading_create: false,
                loading_restore: false,
                restore_databases: {},
            }
        },
        async created() {
            await this.initForm();
        },
        methods: {
            initForm() {
                this.errors = {}
                this.form = {
                    id: null,
                    client_id: this.clientId,
                    name: null,
                    restore_dbname_bkdemo: null,
                    restore_type_bkdemo: null,
                    enabled_cron_restore_bkdemo: false,
                }
            },
            async getData() {
                this.initForm();
                await this.$http.get(`/${this.resource}/client/${this.clientId}`)
                    .then(response => {
                        this.form.restore_dbname_bkdemo = response.data.data.restore_dbname_bkdemo
                        this.form.restore_type_bkdemo = response.data.data.restore_type_bkdemo
                        this.form.enabled_cron_restore_bkdemo = response.data.data.enabled_cron_restore_bkdemo
                    });
                this.getFiles();
                
            },
            close() {
                this.$emit('update:showDialog', false);
            },
            async getFiles() {
                await this.$http.get(`/${this.resource}/files`)
                    .then(response => {
                        this.restore_databases = response.data.data
                    });
            },
            saveBackupDemo() {
                this.form.client_id = this.clientId
                this.loading_create = true

                this.$http.post(`/${this.resource}/backup-create`, this.form)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message);
                            this.getData()
                        } else {
                            this.$message.error(response.data.message);
                        }
                    }).catch(error => {
                        console.log(error)
                        this.$message.error("Error al crear demo");
                    }).finally(() => this.loading_create = false);
            },
            restoreBackupDemo() {
                this.form.client_id = this.clientId
                if(!this.form.restore_dbname_bkdemo){
                    this.$message.error('Seleccionar una demo para restaurar');
                    return true;
                }
                
                const foundElement = this.restore_databases.find(element => element.name === this.form.restore_dbname_bkdemo);
                this.form.restore_type_bkdemo = (foundElement)?foundElement.type:null;

                this.loading_restore = true

                this.$http.post(`/${this.resource}/backup-restore`, this.form)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message);
                            this.getData()
                        } else {
                            this.$message.error(response.data.message);
                        }
                    }).catch(error => {
                        console.log(error)
                        this.$message.error("Error al restaurar demo");
                    }).finally(() => this.loading_restore = false);
            },
            enabledCronDemo() {
                this.form.client_id = this.clientId

                if((!this.form.restore_dbname_bkdemo) && this.form.enabled_cron_restore_bkdemo){
                    this.$message.error('Seleccionar una demo para restaurar');
                    this.form.enabled_cron_restore_bkdemo = false;
                    return true;
                }

                this.$http.post(`/${this.resource}/enable-cron`, this.form)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message);
                        } else {
                            this.$message.error(response.data.message);
                        }
                    }).catch(error => {
                        console.log(error)
                        this.$message.error("Error al actualizar cron");
                    });
            },


        }
    }
</script>