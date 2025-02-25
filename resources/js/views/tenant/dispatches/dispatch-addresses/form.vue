<template>
    <el-dialog :close-on-click-modal="false"
               :title="titleDialog"
               :visible="showDialog"
               append-to-body
               @close="clickClose"
               @open="create">
        <form autocomplete="off" @submit.prevent="submit" v-loading="loading">
            <div class="form-body">
                <div class="row">

                    <div class="col-md-12">
                        <search-persons
                            @changePerson="changePerson"
                            :errors_person_id="errors.person_id"
                            :isDisabled="recordId != null"
                            ref="search_persons"
                        >
                        </search-persons>
                    </div>

                    <div class="col-md-12">
                        <div :class="{'has-danger': errors.address}"
                             class="form-group">
                            <label class="control-label">Dirección</label>
                            <el-input v-model="form.address">
                                <el-button type="primary"
                                           slot="append"
                                           v-if="form.person_id"
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
                <el-button @click.prevent="clickClose">Cancelar</el-button>
                <el-button :loading="loading_submit"
                           native-type="submit"
                           type="primary">Guardar
                </el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>

    import SearchPersons from "../../../../components/filters/SearchPersons.vue"

    export default {
        components: {SearchPersons},
        props: ['showDialog',  'recordId'],
        data()
        {
            return {
                loadingSearch: false,
                loading_submit: false,
                titleDialog: null,
                resource: 'dispatch_addresses',
                errors: {},
                form: {},
                locations: [],
                loading: false,
            }
        },
        async created()
        {
            await this.getTables()
            this.initForm()
        },
        methods:
        {
            changePerson(person_id)
            {
                this.form.person_id = person_id
            },
            async getTables()
            {
                await this.$http.get(`/${this.resource}/tables`)
                    .then(response => {
                        this.locations = response.data.locations
                    })
            },
            initForm()
            {
                this.errors = {}
                this.form = {
                    id: null,
                    person_id: null,
                    address: null,
                    location_id: [],
                    is_default: false,
                    is_active: true,
                    establishment_code: '0000'
                }
            },
            async create()
            {
                this.titleDialog = (this.recordId) ? 'Actualizar dirección' : 'Registrar dirección'
                this.initForm()
                await this.getRecord()
            },
            async getRecord()
            {
                if (this.recordId)
                {
                    this.loading =  true
                    await this.$http.get(`/${this.resource}/record/${this.recordId}`)
                        .then(response => {

                            this.form = response.data.data
                            this.setDataComponents()

                        })
                        .then(() => {
                            this.loading =  false
                        })
                }
            },
            setDataComponents()
            {
                this.$refs.search_persons.setValueFromUpdate(this.form.person_id)
            },
            async submit()
            {
                this.loading_submit = true
                await this.$http.post(`/${this.resource}`, this.form)
                    .then(response => {
                        if (response.data.success)
                        {
                            this.$emit('success', response.data.id)
                            this.clickClose()
                        }
                        else
                        {
                            this.$message.error(response.data.message)
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 422)
                        {
                            this.errors = error.response.data
                        }
                        else
                        {
                            console.log(error)
                        }
                    })
                    .then(() => {
                        this.loading_submit = false
                    })
            },
            async clickSearch()
            {
                this.loadingSearch = true
                await this.$http.get(`/dispatch_addresses/search/${this.form.person_id}`)
                    .then(response => {
                        let res = response.data
                        if (res.success)
                        {
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
            clickClose()
            {
                this.$emit('update:showDialog', false)
                this.cleanDataComponents()
            },
            cleanDataComponents()
            {
                if(this.$refs.search_persons) this.$refs.search_persons.cleanValue()
            },
        }
    }
</script>
