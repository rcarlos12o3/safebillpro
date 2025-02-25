<template>
    <el-dialog :visible="showDialog"
               title="Generación de TXT para Bartender"
               append-to-body
               class="pt-0"
               top="7vh"
               width="80%"
               @close="close"
               >
        <form autocomplete="off"
              @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-12">
                        <item-search-quick-sale
                            @changeItem="changeItemQuickSale"
                            :resource="resource"
                            :showDetailButton="show_all_item_details"
                            :selectedOptionPrice="selected_option_price"
                            :configuration="configuration"
                            ref="item_search_quick_sale"
                        >
                        </item-search-quick-sale>
                    </div>
                    <div class="col-12">
                        <template>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th width="5%"><b>#</b></th>
                                        <th width="5%"><b>ID</b></th>
                                        <th width="15%"><b>Cod Interno</b></th>
                                        <th width="8%"><b>Unidad</b></th>
                                        <th width="35%"><b>Nombre</b></th>
                                        <th width="15%"><b>Imprimir</b></th>
                                        <th width="18%" class="text-right"><b>P.Unitario(Venta)</b></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(row, index) in form.items" :key="index">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ row.id }}</td>
                                        <td>{{ row.internal_id }}</td>
                                        <td>{{ row.unit_type_id }}</td>
                                        <td>{{ row.description }}</td>
                                        <td>
                                            <el-input-number v-model="row.quantity_printer" :min="1"></el-input-number>
                                        </td>
                                        <td class="text-right">{{row.currency_type_symbol}}{{ row.sale_unit_price }}</td>
                                        <td>
                                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickDelete(index)">X</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>
                    <div class="col-12">
                        <p>Campos a Exportar</p>
                        <el-select
                            v-model="form.columns"
                            multiple
                            placeholder="Select"
                            style="width:100%"
                        >
                        <el-option
                            v-for="item in options"
                            :key="item.id"
                            :label="item.label"
                            :value="item.value"
                        />
                        </el-select>
                    </div>
                </div>
                <div class="form-actions text-right mt-4">
                    <el-button class="second-buton" @click.prevent="close()">Cancelar</el-button>
                    <el-button :loading="loading_submit"
                               native-type="submit"
                               type="primary">Generar archivo TXT
                    </el-button>
                </div>
            </div>
        </form>
    </el-dialog>
</template>

<script>
import queryString from 'query-string'
import ItemSearchQuickSale from '@components/items/ItemSearchQuickSale.vue'

export default {
    props: [
        'showDialog',
        'pharmacy',
        'configuration'
    ],
    components: {
        ItemSearchQuickSale
    },
    data() {
        return {
            loading_submit: false,
            headers: headers_token,
            resource: 'items',
            errors: {},
            form: {
                columns: [],
                items: []
            },
            selected_option_price:true,
            show_all_item_details:false,
            options:[
                { id: 1, value: 'internal_id', label: 'Código Interno'},
                { id: 2, value: 'description', label: 'Nombre'},
                { id: 3, value: 'barcode', label: 'Código de barras'},
                { id: 4, value: 'category', label: 'Categoría'},
                { id: 5, value: 'unit_type_id', label: 'Unidad'},
                { id: 6, value: 'brand', label: 'Marca'},
                { id: 7, value: 'sale_unit_price', label: 'Precio'},
                { id: 8, value: 'size', label: 'Talla'},
                { id: 9, value: 'color', label: 'Colores'},
                { id: 10, value: 'status', label: 'Status'},
            ]
        }
    },
    created() {
        if (this.pharmacy !== undefined && this.pharmacy === true) {
            this.fromPharmacy = true;
        }
        this.initForm()
    },
    methods: {
        initForm() {
            this.errors = {}
            this.form.items = []
            this.form.columns = []
        },
        close() {
            this.$emit('update:showDialog', false)
            this.initForm()
        },
        submit() {

            if(this.form.columns.length < 1){
                return this.$message.error("Seleccionar al menos un campo a exportar")
            }

            if(this.form.items.length < 1){
                return this.$message.error("Agregar al menos un producto para exportar")
            }

            this.loading_submit = true

            this.$http.post(`/${this.resource}/export/bartender`, this.form)
                .then(response => {
                    console.log(response);
                    const contentDisposition = response.headers['content-disposition'];
                    let fileName = 'bartender';
                    if (contentDisposition) {
                    const matches = contentDisposition.match(/filename="(.+)"/);
                    if (matches && matches[1]) {
                        fileName = matches[1];
                    }
                    }
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `${fileName}.txt`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    this.$emit('update:showDialog', false)
                    this.initForm()
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        console.log(error)
                        this.$message.error(error.response.data.message)
                    }
                })
                .then(() => {
                    this.loading_submit = false
                })


            
        },
        changeItemQuickSale(item){

            let itemfound = this.form.items.find((row) => row.id === item.id);
            if(itemfound){
                itemfound.quantity_printer+=1
            }else{
                let newItem ={
                    id: item.id,
                    internal_id: item.internal_id,
                    unit_type_id: item.unit_type_id,
                    description: item.description,
                    quantity_printer: 1,
                    currency_type_symbol: item.currency_type_symbol,
                    sale_unit_price: _.round(item.sale_unit_price, 2),
                    barcode: item.barcode,
                    category: item.category,
                    brand: item.brand,
                    size: item.CatItemSize,
                    color: item.colors,
                    status: item.CatItemStatus,
                }
                this.form.items.push(newItem)
            }

        },
        clickDelete(index) {
            this.form.items.splice(index, 1)
        }
    }
}
</script>
