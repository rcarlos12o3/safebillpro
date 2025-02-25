<template>
    <el-dialog :close-on-click-modal="false"
        :title="titleDialog"
        :visible="showDialog"
        append-to-body
        @close="clickClose"
        @open="create">
        <form autocomplete="off"
              @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12">
                        <div :class="{'has-danger': errors.address}"
                             class="form-group">
                            <label class="control-label">Dirección</label>
                            <el-input v-model="form.address">
                                <el-button type="primary"
                                           slot="append"
                                           :loading="loadingSearch"
                                           icon="el-icon-search"
                                           @click.prevent="clickSearch">Buscar
                                </el-button>
                            </el-input>
                            <small v-if="errors.address"
                                   class="invalid-feedback"
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
                                   class="invalid-feedback"
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
            <div class="form-actions text-end mt-4">
                <el-button class="second-buton" @click.prevent="clickClose">Cancelar</el-button>
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
    name: 'DispatchAddressForm',
    props: ['showDialog', 'personId',  'title'],
    data() {
        return {
            loadingSearch: false,
            loading_submit: false,
            titleDialog: null,
            resource: 'dispatch_addresses',
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
                person_id: this.personId,
                address: null,
                location_id: [],
                is_default: false,
                is_active: true,
                establishment_code: '0000'
            }
        },
        async create() {
            this.titleDialog = this.title;
            this.initForm();
        },
        async submit() {
            if(this.form.location_id.length!=3){
                    return this.$message.error('Debe ingresar ubigeo');
            }
            this.loading_submit = true
            await this.$http.post(`/${this.resource}/save`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.$emit('success', response.data.id)
                        this.clickClose()
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
        async clickSearch() {
            this.loadingSearch = true;
            await this.$http.get(`/dispatch_addresses/search/${this.personId}`)
                .then(response => {
                    let res = response.data;
                    if (res.success) {
                        this.form.address = res.data.address
                        this.form.location_id = res.data.location_id
                    } else {
                        this.$message.error(res.message)
                    }
                })
                .catch(error => {
                    console.log(error.response)
                })
                .finally(() => {
                    this.loadingSearch = false
                })
        },
        clickClose() {
            this.$emit('update:showDialog', false)
        },
    }
}
</script>
