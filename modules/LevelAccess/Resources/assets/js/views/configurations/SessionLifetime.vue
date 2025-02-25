<template>
    <div class="row" v-if="form.is_tenant_session_lifetime_enabled">
        <div class="col-md-4 mt-4">
            <label class="control-label">
                Duración de la sesión 
                <el-tooltip class="item"
                    content="Cerrar sesión del usuario por inactividad (1 - 24 horas)"
                    effect="dark"
                    placement="top-start">
                    <i class="fa fa-info-circle"></i>
                </el-tooltip>
            </label>
            
            <div :class="{'has-danger': errors.session_lifetime}" class="form-group">
                <el-input-number 
                    v-model="form.session_lifetime"
                    :min="1"
                    :max="24"
                    :step="1"
                    :precision="2"
                    @change="submit">
                </el-input-number>
                <small v-if="errors.session_lifetime" class="form-control-feedback" v-text="errors.session_lifetime[0]"></small>
            </div>
        </div>
    </div>
</template>

<script>


export default {
    props: [],
    data() {
        return {
            errors: {},
            form: {
                session_lifetime: 0,
                is_tenant_session_lifetime_enabled: false
            },
            resource: 'configurations-session-lifetime'
        }
    },
    created() 
    {
        this.getData()
    },
    methods: 
    {
        getData() 
        {
            this.$http.get(`/${this.resource}/data`)
                    .then(response => {
                        this.form = response.data
                    })
        },
        submit() 
        {
            this.$http.post(`/${this.resource}`, this.form)
                    .then(async (response) => {
                        const data = response.data

                        if (data.success) 
                        {
                            this.$message.success(data.message)
                            await this.sleep(800)
                            location.reload()
                        } 
                        else 
                        {
                            this.$message.error(data.message)
                        }

                    }).catch(error => {

                        if (error.response.status === 422) 
                        {
                            this.errors = error.response.data
                        } 
                        else 
                        {
                            console.log(error)
                        }
                    })
        },
        sleep(ms) 
        {
            return new Promise(resolve => setTimeout(resolve, ms))
        },
    }
}
</script>
