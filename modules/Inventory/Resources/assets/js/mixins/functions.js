/*

Busqueda por codigo de barras en movimientos

Usado en:
\modules\Inventory\Resources\assets\js\inventory\form.vue
\modules\Inventory\Resources\assets\js\inventory\form_output.vue
*/

export const inventory_search_item_barcode = {
    data() {
        return {
            search_item_by_barcode: false,
            input_search_barcode: null,
        }
    },
    methods: 
    {
        async enabledSearchItemsBarcode() 
        {
            if (this.search_item_by_barcode) 
            {
                if (this.items.length == 1) 
                {
                    this.form.item_id = this.items[0].id

                    await this.changeItem()
                    this.form.quantity = parseInt(this.form.quantity) + 1

                    this.setInputFocus()
                }
                else
                {
                    this.$message.info('No se encontró el producto.')
                }

                this.cleanInputSearch()
            }
        },
        cleanInputSearch()
        {
            this.input_search_barcode = null
        },
        setInputFocus()
        {
            this.$refs.input_search_barcode.$el.getElementsByTagName('input')[0].focus()
        },
    }
}