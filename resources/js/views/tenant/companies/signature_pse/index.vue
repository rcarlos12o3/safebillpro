<template>
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="my-0">Servicio PSE
                <el-tooltip
                    class="item"
                    content="Solicitar datos al PSE - Disponible en facturas, boletas, resúmenes, anulaciones, guías"
                    effect="dark"
                    placement="top-start">
                    <i class="fa fa-info-circle"></i>
                </el-tooltip>
            </h3>
        </div>
        <div class="card-body">
            <form autocomplete="off" @submit.prevent="submit">
                <div class="row pt-1">
                    <div class="col-md-12">
                        <div :class="{'has-danger': errors.send_document_to_pse}"
                            class="form-group">
                            <label class="control-label">Habilitar </label>
                            <div class="transfer-data-table pt-3 pl-3 pb-2">
                                <el-switch v-model="form.send_document_to_pse"
                                    active-text="Si"
                                    inactive-text="No"></el-switch>
                                <small v-if="errors.send_document_to_pse"
                                    class="form-control-feedback"
                                    v-text="errors.send_document_to_pse[0]"></small>
                            </div>
                        </div>
                    </div>

                    <template v-if="form.send_document_to_pse">

                        <!-- Selector de proveedor -->
                        <div class="col-md-12 mt-2 mb-3">
                            <label class="control-label d-block mb-2">Proveedor PSE</label>
                            <el-radio-group v-model="pse_type">
                                <el-radio label="gior">ValidaPSE (GiorTechnology)</el-radio>
                                <el-radio label="nuevo">Nuevo Proveedor</el-radio>
                            </el-radio-group>
                        </div>

                        <!-- Campos ValidaPSE -->
                        <template v-if="pse_type === 'gior'">
                            <div class="col-md-6 mt-3">
                                <div class="form-group" :class="{'has-danger': errors.user_pse}">
                                    <label class="control-label">Usuario autenticación <span class="text-danger">*</span></label>
                                    <el-input v-model="form.user_pse"></el-input>
                                    <small class="form-control-feedback" v-if="errors.user_pse" v-text="errors.user_pse[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-6 mt-3">
                                <div class="form-group" :class="{'has-danger': errors.password_pse}">
                                    <label class="control-label">Contraseña autenticación <span class="text-danger">*</span></label>
                                    <el-input v-model="form.password_pse" show-password></el-input>
                                    <small class="form-control-feedback" v-if="errors.password_pse" v-text="errors.password_pse[0]"></small>
                                </div>
                            </div>
                        </template>

                        <!-- Campos Nuevo Proveedor -->
                        <template v-if="pse_type === 'nuevo'">
                            <div class="col-md-12 mt-3">
                                <div class="form-group" :class="{'has-danger': errors.url_login_pse}">
                                    <label class="control-label">URL base del proveedor <span class="text-danger">*</span></label>
                                    <el-input v-model="form.url_login_pse" placeholder="https://api-efact.papeat.com"></el-input>
                                    <small class="text-muted">Sin barra al final. Ejemplo: https://api-efact.papeat.com</small>
                                    <small class="form-control-feedback" v-if="errors.url_login_pse" v-text="errors.url_login_pse[0]"></small>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <div class="form-group" :class="{'has-danger': errors.password_pse}">
                                    <label class="control-label">Token de acceso <span class="text-danger">*</span></label>
                                    <el-input v-model="form.password_pse" show-password
                                        placeholder="eyJ0eXAiOiJKV1Qi..."></el-input>
                                    <small class="text-muted">Token generado en el panel del proveedor. Se usa directamente como Bearer.</small>
                                    <small class="form-control-feedback" v-if="errors.password_pse" v-text="errors.password_pse[0]"></small>
                                </div>
                            </div>
                        </template>

                    </template>
                </div>
                <div class="form-actions text-right pt-2">
                    <el-button type="primary" native-type="submit" :loading="loading_submit">Guardar</el-button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>

export default {
    data() {
        return {
            resource: 'companies',
            recordId: null,
            form: {},
            errors: {},
            loading_submit: false,
            pse_type: 'gior',
        }
    },
    created() {
        this.initForm()
        this.getData()
    },
    watch: {
        pse_type(val) {
            if (val === 'gior') {
                // Limpiar campos del nuevo proveedor para que Facturalo use GiorService
                this.form.url_login_pse = null
                this.form.client_id_pse = null
                this.form.user_pse      = null
            } else {
                // Limpiar campos de ValidaPSE
                this.form.user_pse = null
            }
        }
    },
    methods: {
        submit(){
            this.loading_submit = true
            this.$http.post(`/${this.resource}/store-send-pse`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        console.log(error)
                    }
                })
                .then(() => {
                    this.loading_submit = false
                })
        },
        initForm(){
            this.form = {
                send_document_to_pse : false,
                url_signature_pse    : null,
                url_send_cdr_pse     : null,
                client_id_pse        : null,
                url_login_pse        : null,
                password_pse         : null,
                user_pse             : null,
            }
            this.errors = {}
        },
        getData() {
            this.$http.get(`/${this.resource}/record-send-pse`)
                .then(response => {
                    this.form = response.data
                    // Determinar qué proveedor está activo según url_login_pse
                    this.pse_type = this.form.url_login_pse ? 'nuevo' : 'gior'
                })
        },
    }
}
</script>
