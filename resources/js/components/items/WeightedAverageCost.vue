<template> 
    <div v-loading="loading" class="form-group">
        <label class="control-label">Costo Ponderado</label>
        <el-input 
            v-model="weighted_cost"
            readonly
        >
            <!-- <span slot="prepend">S/</span> -->
        </el-input>
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
                weighted_cost: 0,
                resource: 'items',
                loading: false
            }
        },
        watch: {
            itemId(newValue) 
            {
                this.getWeightedCost()
            }
        },
        created()
        { 
            this.getWeightedCost()
        },
        methods: 
        {
            async getWeightedCost() 
            {
                if(!this.itemId) return

                this.loading = true
                await this.$http.get(`/${this.resource}/weighted-cost/${this.itemId}`)
                                .then((response) => {
                                    this.weighted_cost = response.data
                                })
                                .finally(()=>{
                                    this.loading = false
                                })
            },
        }
    }
</script>