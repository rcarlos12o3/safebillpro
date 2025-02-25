<template>
    <el-dialog :title="titleDialog"
               :visible="showDialog"
               :append-to-body="true"
               @close="close"
               @open="create">

        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12">
                        <div :class="{'has-danger': errors.composed_id}"
                                class="form-group">
                            <label class="control-label">Usuario</label>
                            <el-select 
                                v-model="form.composed_id" 
                                filterable
                                @change="changeUser">

                                <template v-for="option in users">
                                    <el-tooltip
                                        :key="option.composed_id"
                                        :content="`Empresa origen: ${option.client_full_name}`"
                                        placement="left">

                                        <el-option 
                                            :key="option.composed_id"
                                            :label="option.full_name"
                                            :value="option.composed_id"></el-option>

                                    </el-tooltip>
                                </template>

                            </el-select>
                            <small v-if="errors.composed_id" class="form-control-feedback" v-text="errors.composed_id[0]"></small>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div :class="{'has-danger': errors.destination_client_id}"
                                class="form-group">
                            <label class="control-label">Empresa destino</label>
                            <el-select v-model="form.destination_client_id"
                                        filterable>
                                <el-option v-for="option in clients"
                                            :key="option.id"
                                            :label="option.full_name"
                                            :value="option.id"></el-option>
                            </el-select>
                            <small v-if="errors.destination_client_id" class="form-control-feedback" v-text="errors.destination_client_id[0]"></small>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="form-actions text-right mt-4">
                <el-button class="second-buton" @click.prevent="close()">Cancelar</el-button>
                <el-button :loading="loading_submit"
                           native-type="submit"
                           type="primary">Agregar
                </el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>

export default {
    props: [
        'showDialog',
        'recordId',
    ],
    data() {
        return {
            loading_submit: false,
            titleDialog: null,
            resource: 'multi-users',
            errors: {},
            form: {},
            clients: [],
            users: [],
        }
    },
    async created() 
    {
        await this.initForm()
        await this.getTables()
    },
    methods: 
    {
        changeUser()
        {
            this.form.user = { ..._.find(this.users, {composed_id : this.form.composed_id}) }
        },
        async getTables()
        {
            await this.$http.get(`/${this.resource}/tables`)
                        .then(response => {
                            this.clients = response.data.clients
                            this.users = response.data.users
                        })
        },
        initForm() {
            this.errors = {}
            this.form = {
                destination_client_id: null,
                composed_id: null,
                user: {}
            }

        },
        create() 
        {
            this.titleDialog = 'Nuevo acceso'
        },
        async submit() 
        {
            this.loading_submit = true

            await this.$http.post(`/${this.resource}`, this.form)
                .then(response => {

                    if (response.data.success) 
                    {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                        this.getTables()
                        this.close()
                    }
                    else
                    {
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
                .finally(() => {
                    this.loading_submit = false
                })
        },
        close() 
        {
            this.$emit('update:showDialog', false)
            this.initForm()
        },
    }
}
</script>
