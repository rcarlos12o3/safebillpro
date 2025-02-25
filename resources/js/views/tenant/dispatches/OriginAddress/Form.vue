<template>
    <el-dialog :close-on-click-modal="false"
               :title="titleDialog"
               :visible="showDialog"
               append-to-body
               @close="close"
               @open="create">
        <form autocomplete="off"
              @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12">
                        <div :class="{'has-danger': errors.address}"
                             class="form-group">
                            <label class="control-label">Dirección</label>
                            <el-input v-model="form.address"></el-input>
                            <small v-if="errors.address"
                                   class="form-control-feedback"
                                   v-text="errors.address[0]"></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div :class="{'has-danger': errors.location_id}"
                             class="form-group">
                            <label class="control-label">Ubigeo</label>
                            <el-cascader v-model="form.location_id"
                                         :options="locations"
                                         filterable></el-cascader>
                            <small v-if="errors.location_id"
                                   class="form-control-feedback"
                                   v-text="errors.location_id[0]"></small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div :class="{'has-danger': errors.establishment_code}"
                             class="form-group">
                            <label class="control-label">Código de sucursal</label>
                            <el-input v-model="form.establishment_code"></el-input>
                            <small v-if="errors.establishment_code"
                                   class="invalid-feedback"
                                   v-text="errors.establishment_code[0]"></small>
                        </div>
                    </div>

                </div>
            </div>
            <div class="form-actions text-right mt-4">
                <el-button class="second-buton" @click.prevent="close()">Cancelar</el-button>
                <el-button :loading="loading_submit"
                           native-type="submit"
                           type="primary">Guardar
                </el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>

export default {
    name: 'DispatchOriginAddressForm',
    props: ['showDialog', 'recordId', 'external','establishmentId'],
    data() {
        return {
            loading_submit: false,
            titleDialog: null,
            resource: 'origin_addresses',
            errors: {},
            form: {},
            locations: []
        }
    },
    async created() {
        await this.getTables();
        this.initForm()
    },
    methods: {
        async getTables() {
            await this.$http.get(`/${this.resource}/tables`)
                .then(response => {
                    this.locations = response.data.locations
                })
        },
        initForm() {
            this.errors = {}
            this.form = {
                id: null,
                country_id: 'PE',
                location_id: [],
                address: null,
                is_default: 0,
                is_active: 1,
                establishment_code:'0000',
                establishment_id: this.establishmentId,
            }
        },
        async create() {
            this.initForm();
            this.titleDialog = (this.recordId) ? 'Editar dirección de partida' : 'Nueva dirección de partida'
            if (this.recordId) {
                await this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        this.form = response.data.data
                    })
            }
        },
        async submit() {
            this.loading_submit = true
            await this.$http.post(`/${this.resource}`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.$emit('success', response.data.id)
                        this.close()
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
        close() {
            this.$emit('update:showDialog', false)
        },
    }
}
</script>
