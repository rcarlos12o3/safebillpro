<template> 
    <div v-loading="loading" class="mt-2">
        <template v-for="(row, index) in sets">
            <span class="d-block">
                {{ row.quantity }} - {{ row.description }}
            </span>
        </template>
    </div>
</template>

<script>

    export default {
        props: {
            itemId: {
                type: Number,
                required: true,
            },
        },
        data() {
            return {
                sets: [],
                resource: 'items',
                loading: false
            }
        },
        async created()
        { 
            await this.getSets() 
        }, 
        mounted()
        {
        },
        methods: 
        {
            async getSets() 
            {
                this.loading = true
                await this.$http.get(`/${this.resource}/sets-description/${this.itemId}`)
                                .then((response) => {
                                    this.sets = response.data
                                })
                                .finally(()=>{
                                    this.loading = false
                                })
            },
        }
    }
</script>