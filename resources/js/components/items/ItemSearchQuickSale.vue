<template>
    <div class="m-0 p-0 content-products">
        <el-select
            ref="selectBarcode"
            v-model="item_id"
            :loading="loading_search"
            :remote-method="searchRemoteItems"
            remote
            filterable
            placeholder="Buscar/Agregar producto"
            @change="changeItem"
            @keyup.native="changeInputEnter"
            @keyup.enter.native="searchRemoteItemsWithEnter"
            :disabled="!is_mounted"
            popper-class="list-result"
            :reserve-keyword="false">
            <el-option
                class="item-result"
                v-for="row in items"
                :key="row.id"
                :label="itemOptionDescriptionView(row)"
                :value="row.id">
                <div class="row">
                    <div class="col-1 p-1 align-self-center">
                        <img
                            class="custom-image"
                            :src="row.image_url"
                            :alt="row.description"
                        >
                    </div>
                    <div class="col full-description align-self-center">
                            {{ itemOptionDescriptionView(row) }}<br>
                            <b class="custom-price pt-1 pb-1 text-primary">{{ row.currency_type_symbol }} {{ itemSetSaleUnitPrice(row) }}</b>
                    </div>
                    <div class="col-4 text-right">
                        <b :class="{'text-danger': row.stock <= 0, 'text-success': row.stock > 0}" class="py-1 mx-3">
                                <i class="fas fa-cube"></i> {{ parseStock(row.stock) }}
                            </b>
                        <div class="">
                            <button v-if="showDetailButton" type="button" class="el-button el-button--default el-button--mini m-1 btn-warning" @click.stop.prevent="clickDetail(row.id)">
                                <span>Ver Detalle</span>
                            </button>
                            <button type="button" class="el-button el-button--default el-button--mini m-1 btn-primary" @click.stop.prevent="clickStock(row)">
                                <span>Ver stock</span>
                            </button>
                        </div>
                    </div>
                </div>
            </el-option>
        </el-select>

        <div class="w-50 pl-0 pt-2">
            <el-checkbox v-model="searchOnEnter" @change="changeSearchOnEnter" >Buscar solo al presionar Enter</el-checkbox>
            <el-checkbox ref="searchItemCheckbox" v-model="search_item_by_barcode" @change="focusInputSearch()">Buscar por código de barras</el-checkbox>
        </div>
        

        <warehouses-stock
            :showDialog.sync="showDialogStock"
            :warehouses="warehouses"
            :itemName="itemName">
        </warehouses-stock>

        <!-- <item-detail-form
            :recordId="dialogItemId"
            :showDialog.sync="showDialogItem"
            :onlyShowAllDetails="showDetailButton"
        >
        </item-detail-form> -->

    </div>
</template>

<style>
.list-result {
    margin: 0px !important;
    margin-top: 10px !important;
    margin-bottom: 10px;
}


</style>

<script>

    import WarehousesStock from './partials/WarehousesStock.vue'
    import { ItemOptionDescription } from '@helpers/modal_item'
    import ItemDetailForm from '@views/items/form.vue'

    export default {
        props: {
            resource: {
                type: String,
                required: true,
            },
            showDetailButton: {
                type: Boolean,
                required: false,
                default: false,
            },
            selectedOptionPrice: {
                type: Number|String,
                required: false,
                default: false,
            },
            configuration: {
                type: Object,
                required: true,
                default: false,
            }
        },
        components: {
            WarehousesStock,
            // ItemDetailForm
        },
        data() {
            return {
                item_id: null,
                loading_search: false,
                all_items: [],
                items: [],
                is_mounted: false,
                showDialogStock: false,
                warehouses: [],
                showDialogItem: false,
                itemName: null,
                dialogItemId: null,
                searchOnEnter: false,
                search_item_by_barcode:false,
                inputEnter: '',
            }
        },
        async created()
        {
            await this.initialItems()
        },
        mounted()
        {
            this.is_mounted = true
        },
        methods:
        {
            focusInputSearch() {
                if (this.search_item_by_barcode) {
                    this.$refs.selectBarcode.focus()
                }
            },
            blurSelect() {
                this.search_item_by_barcode = false;
            },
            parseStock(stock)
            {
                return parseFloat(stock)
            },
            cleanValue()
            {
                this.item_id = null
            },
            clickDetail(id)
            {
                // this.dialogItemId = id
                // this.showDialogItem = true
                window.open(`/items/show-item-detail/${id}`)

            },
            clickStock(row)
            {
                this.warehouses = row.warehouses
                this.itemName = row.full_description
                this.showDialogStock = true
            },
            changeItem()
            {
                const item = { ..._.find(this.items, { id : this.item_id}) }
                this.$emit('changeItem', item)
                if(this.search_item_by_barcode){
                    this.items= [];
                    setTimeout(() => {
                        this.$refs.selectBarcode.$el.getElementsByTagName('input')[0].focus();
                    }, 200);
                }
                if(this.searchOnEnter) {
                    this.items= [];
                    this.$refs.selectBarcode.$el.getElementsByTagName('input')
                    setTimeout(() => {
                        this.$refs.selectBarcode.$el.getElementsByTagName('input')[0].blur()
                        this.$refs.selectBarcode.blur();
                    }, 200);
                    setTimeout(() => {
                        this.$refs.selectBarcode.$el.getElementsByTagName('input')[0].focus();
                    }, 200);

                }
            },
            async initialItems()
            {
                await this.$http.get(`/${this.resource}/table/items`).then((response) => {
                    this.all_items = response.data
                    this.filterItems()
                })
            },
            itemOptionDescriptionView(row)
            {
                return ItemOptionDescription(row)
            },
            async searchRemoteItems(input)
            {
                if (this.searchOnEnter) {
                    return;
                }

                if (input.length > 2)
                {
                    this.loading_search = true

                    const params = {
                        input: input,
                        search_by_barcode: (this.search_item_by_barcode)?1:0,
                        search_item_by_barcode_presentation: 0,
                        search_factory_code_items: 0
                    }
                    await this.$http.get(`/${this.resource}/search-items`, { params })
                            .then(response => {
                                this.items = response.data.items
                                this.loading_search = false
                                this.enabledSearchItemsBarcode(input)
                                if (this.items.length == 0){
                                    this.filterItems();
                                    this.items=[];
                                }
                            })

                    return
                }
                await this.filterItems()
            },
            async searchRemoteItemsWithEnter(){
                if(this.inputEnter.length > 2 && this.searchOnEnter){
                    this.loading_search = true
                    const params = {
                        input: this.inputEnter,
                        search_by_barcode: (this.search_item_by_barcode)?1:0,
                        search_item_by_barcode_presentation: 0,
                        search_factory_code_items: 0
                    }
                    await this.$http.get(`/${this.resource}/search-items`, { params })
                            .then(response => {
                                this.items = response.data.items
                                this.loading_search = false
                                this.enabledSearchItemsBarcode(this.inputEnter)
                                if (this.items.length == 0){
                                    this.filterItems();
                                    this.items=[];
                                }
                            })
                    return
                }
            },
            enabledSearchItemsBarcode(input) {
                if (this.search_item_by_barcode) {

                    if (this.items.length == 0){

                        this.$refs.selectBarcode.$el.getElementsByTagName('input')[0].blur()
                        this.$refs.selectBarcode.blur();
                        setTimeout(() => {
                            this.$refs.selectBarcode.$el.getElementsByTagName('input')[0].focus();
                        }, 200);
                    }

                    if (this.items.length == 1) {
                        this.item_id = this.items[0].id;
                        if (this.$refs.selectBarcode) {
                            //this.$refs.selectBarcode.$el
                            //.getElementsByTagName("input")[0]
                            //.focus();
                            this.$refs.selectBarcode.$el.getElementsByTagName('input')[0].blur()
                            this.$refs.selectBarcode.blur();
                        }
                        this.changeItem();
                    }
                }
            },
            filterItems()
            {
                if(!this.searchOnEnter) {
                    this.items = this.all_items
                }
            },
            changeSearchOnEnter() {
                if(this.searchOnEnter) {
                    this.items = []
                }
            },
            itemSetSaleUnitPrice(row)
            {
                if(!this.configuration.enable_list_product && this.selectedOptionPrice !== 1) {
                    if(row.item_unit_types.length) {
                        let first_list = row.item_unit_types[0];
                        let priceSelected = first_list[this.selectedOptionPrice];
                        return row.unit_price_value = priceSelected;
                    } else {
                        return row.unit_price_value = "0";
                    }
                }
                return  parseFloat(row.sale_unit_price).toFixed(2);
            },
            changeInputEnter() {
                let value = this.$refs.selectBarcode.$el.getElementsByTagName('input')[0].value
                this.inputEnter = value 
            }
        }
    }
</script>

<style scoped>

    .custom-image {
        width: 100%;
        max-width: 64px;
        object-fit: contain;
    }

    .full-description {
        font-size: 0.85rem;
        white-space: normal;
        line-height: 1.5rem;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .el-select-dropdown__item{
        height: 72px !important
    }

li.el-select-dropdown__item.item-result {
    border-bottom: 1px solid #e0e6f8;
}

</style>
<style>

    .list-result .el-select-dropdown__wrap {
        max-height: 360px !important;
    }

    .list-result .el-select-dropdown__list{
        padding: 0;
    }
</style>