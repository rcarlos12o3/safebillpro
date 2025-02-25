<template> 
    <el-dialog :title="titleDialog" :visible="showDialog" @open="create"  @close="close"   append-to-body top="7vh" :close-on-click-modal="false">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ubicación</th>
                                    <th class="text-right">Stock</th>
                                </tr>
                            </thead>
                            <tbody>    
                                <tr v-for="(row,index) in warehouses" :key="index">
                                    <th>{{ row.warehouse_description }}</th>
                                    <th class="text-right">{{ row.stock }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right pt-2">
                <el-button @click.prevent="close()">Cerrar</el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>
    export default { 
        props:['showDialog', 'warehouses', 'itemName'],
        data() {
            return {
                titleDialog: null,
            }
        },
        created() 
        {
        },
        methods: {
            async create()
            {
                this.titleDialog = `Producto: ${this.itemName}`
            },
            close() 
            {
                this.$emit('update:showDialog', false)
            },
        }
    }
</script>
