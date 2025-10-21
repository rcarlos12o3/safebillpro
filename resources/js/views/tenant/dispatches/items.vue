<template>
    <el-dialog :title="titleDialog" :visible="dialogVisible" @open="create" @close="close" top="8vh">
        <div class="form-body">
            <div class="row">
                <div class="col-12">
                    <el-checkbox
                        v-model="various_item"
                        @change="setVariousItem">Producto manual
                    </el-checkbox>
                </div>
                <div class="col-md-6">
                    <template v-if="various_item">
                        <div class="form-group">
                            <label class="control-label">Descripción del Producto/Servicio</label>
                            <el-input
                                v-model="form.item_description"
                                ref="inputItemDescription"
                                maxlength="500">
                            </el-input>
                        </div>
                    </template>
                    <template v-else>
                        <div class="form-group" :class="{'has-danger': errors.items}">
                            <label class="control-label">
                                Producto
                                <a href="#" @click.prevent="showDialogNewItem = true">[+ Nuevo]</a>
                            </label>
                            <el-select v-model="form.item"
                                        filterable
                                        @change="onChangeItem"
                                        remote
                                        :remote-method="searchRemoteItems"
                                        :loading="loading_search">
                                <el-option v-for="option in items" :key="option.id" :value="option.id" :label="option.full_description"></el-option>
                            </el-select>
                            <small class="form-control-feedback" v-if="errors.items" v-text="errors.items[0]"></small>
                        </div>
                    </template>
                </div>
                <div class="col-lg-6">
                    <div class="form-group" :class="{'has-danger': errors.quantity}">
                        <label class="control-label">Cantidad</label>
                        <el-input-number v-model="form.quantity" :precision="4" :step="1" :min="0.01" :max="99999999"></el-input-number>
                        <small class="form-control-feedback" v-if="errors.quantity" v-text="errors.quantity[0]"></small>
                    </div>
                </div>
                <template v-if="item">
                    <div class="col-12 mt-2" v-if="item.lots_enabled && item.lots_group.length > 0">
                        <a href="#"  class="text-center font-weight-bold text-info" @click.prevent="clickLotGroup">[&#10004; Seleccionar lote]</a>
                    </div>
                </template>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click.prevent="close">Cerrar</el-button>
            <el-button type="primary" @click="clickAddItem">Agregar</el-button>
        </span>

        <item-form :showDialog.sync="showDialogNewItem" :external="true"></item-form>

        <lots-group
            v-if="item"
            :quantity="form.quantity"
            :showDialog.sync="showDialogLots"
            :lotsGroup="item.lots_group"
            @addRowLotGroup="addRowLotGroup">
        </lots-group>
    </el-dialog>
</template>

<script>
    import itemForm from '../items/form.vue';
    import LotsGroup from '../documents/partials/lots_group.vue';

    export default {
        components: {itemForm, LotsGroup},
        props: ['dialogVisible'],
        data() {
            return {
                titleDialog: 'Agregar Producto',
                showDialogNewItem: false,
                all_items: [],
                resource: 'dispatches',
                errors: {},
                items: [],
                form: {},
                showDialogLots: false,
                item: null,
                loading_search: false,
                various_item: false,
                various_item_barcode: 'VARIOUS_ITEM',
                search_item_by_barcode: false,
            }
        },
        methods: {
            clickLotGroup() {
                this.showDialogLots = true
            },
            onChangeItem() {
                this.form.IdLoteSelected = null;
                this.item = this.items.find(it => it.id == this.form.item);
            },
            addRowLotGroup(id) {
                this.form.IdLoteSelected =  id;
            },
            create() {
                console.log('🔍 DEBUG: Modal dispatches items abierto', this.various_item);
                this.$http.post(`/${this.resource}/tables`).then(response => {
                    this.items = response.data.items;
                    this.all_items = this.items
                });

                this.form = {};
            },
            close() {
                this.$emit('update:dialogVisible', false);
            },
            clickAddItem() {
                this.errors = {};

                // Validación para producto manual
                if (this.various_item) {
                    if (!this.form.item_description || !this.form.item_description.trim()) {
                        return this.$message.error('La descripción es requerida');
                    }
                }

                if(this.item && this.item.lots_enabled){
                    if(! this.form.IdLoteSelected)
                        return this.$message.error('Debe seleccionar un lote.');
                }

                if ((this.form.item != null) && (this.form.quantity != null)) {
                    this.form.quantity = Math.abs(this.form.quantity)
                    if(isNaN(this.form.quantity))this.form.quantity = 0;
                    const item = this.items.find((item) => item.id == this.form.item)

                    // Si es producto manual, sobrescribir la descripción
                    if (this.various_item) {
                        item.description = this.form.item_description;
                    }

                    item.IdLoteSelected = this.form.IdLoteSelected;
                    item.unit_price = item.sale_unit_price;
                    item.total_value = item.sale_unit_price*this.form.quantity;
                    this.$emit('addItem', {
                        item,
                        quantity: this.form.quantity,
                    });

                    this.form = {};
                    this.item = null;
                    this.various_item = false;
                    return;
                }

                if (this.form.item == null) this.$set(this.errors, 'items', ['Seleccione el producto']);

                if (this.form.quantity == null) this.$set(this.errors, 'quantity', ['Digite la cantidad']);

                this.form.IdLoteSelected = null;
            },
            filterItems() {
                this.items = this.all_items
            },
            async searchRemoteItems(input) {
                if (input.length > 2) {
                    this.loading_search = true
                    const params = {
                        'input': input,
                        'search_by_barcode': this.search_item_by_barcode ? 1 : 0
                    }
                    await this.$http.get(`/documents/search-items`, { params })
                            .then(response => {
                                this.items = response.data.items
                                this.loading_search = false
                                // this.enabledSearchItemsBarcode()
                                if(this.items.length == 0){
                                    this.filterItems()
                                }
                            })
                } else {
                    await this.filterItems()
                }

            },
            async setVariousItem() {
                if (this.various_item) {
                    // Guardar valor original de search_item_by_barcode
                    let original_value = this.search_item_by_barcode;
                    this.search_item_by_barcode = true;

                    // Buscar el producto VARIOUS_ITEM
                    await this.searchRemoteItems(this.various_item_barcode);

                    // Restaurar valor original
                    this.search_item_by_barcode = original_value;

                    // Validar que exista el producto
                    let various_item_found = this.items.find(item =>
                        item.barcode === this.various_item_barcode
                    );

                    if (!various_item_found) {
                        this.$notify({
                            title: "Producto Manual",
                            message: `Debe registrar un producto con código de barras ${this.various_item_barcode}`,
                            type: "error",
                            duration: 3000
                        });
                        this.various_item = false;
                    } else {
                        // Configurar el item automáticamente
                        this.form.item = various_item_found.id;
                        this.item = various_item_found;
                        this.form.item_description = '';

                        // Focus en el input de descripción
                        this.$nextTick(() => {
                            if (this.$refs.inputItemDescription) {
                                this.$refs.inputItemDescription.$el
                                    .getElementsByTagName('input')[0].focus();
                            }
                        });
                    }
                } else {
                    // Resetear form
                    this.form = {};
                    this.item = null;
                }
            },
        }
    }
</script>
