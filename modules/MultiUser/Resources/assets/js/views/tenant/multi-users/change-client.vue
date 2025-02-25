<template>
    <div v-if="multi_users.length > 1" class=""  style="max-height: 100%; width: 100%;">
        <form autocomplete="off" :action="`/${resource}`" method="POST" ref="form" style="width: 100%;">

            <input type="hidden" name="_token" :value="csrf_token">

            <input type="hidden" name="is_destination" v-model="form.is_destination">

            <div class="col-md-12 pr-0 pl-0">
                <div class="form-group">
                    <label class="control-label mt-0">Cambiar empresa:</label>
                    <select 
                        class="form-control"
                        v-model="form.multi_user_id" 
                        name="multi_user_id"
                        @change="changeUser" 
                    >
                        <option 
                            v-for="option in multi_users"
                            :key="option.id"
                            :label="option.client_full_name"
                            :value="option.id">
                        </option>
                    </select>

                </div>
            </div>
        </form>
    </div>
</template>

<script>


export default {
    data() {
        return {
            title: null,
            showDialog: false,
            resource: 'multi-users',
            multi_users: [],
            form: {
                multi_user_id: null,
                is_destination: false,
            },
            csrf_token: document.head.querySelector('meta[name="csrf-token"]').content
        }
    },
    async created() 
    {
        await this.getRecords()
    },
    computed: 
    {
    },
    methods: 
    {
        async getRecords()
        {
            await this.$http.get(`/${this.resource}/records`)
                        .then(response => {
                            this.multi_users = response.data
                        })
        },
        sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },
        async changeUser() 
        {
            const row = _.find(this.multi_users, { id : this.form.multi_user_id})
            this.form.is_destination = row.is_destination
            await this.sleep(500);
            this.$refs.form.submit()
        },
    }
}
</script>
