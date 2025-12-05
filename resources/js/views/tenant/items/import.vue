<template>
    <el-dialog
        :title="titleDialog"
        :visible="showDialog"
        @close="close"
        @open="create"
        class="dialog-import"
        :width="showPreview ? '90%' : '600px'">

        <!-- Step 1: File Upload Form -->
        <form v-if="!showPreview" autocomplete="off" @submit.prevent="validateFile">
            <div class="form-body">
                <div class="row">
                    <div class="col-12 form-group" :class="{'has-danger': errors.warehouse_id}">
                        <label for="warehouse">Almacén</label>
                        <el-select v-model="form.warehouse_id">
                            <el-option v-for="w in warehouses" :key="w.id" :label="w.description" :value="w.id"></el-option>
                        </el-select>
                        <small class="form-control-feedback" v-if="errors.warehouse_id" v-text="errors.warehouse_id[0]"></small>
                    </div>
                    <div class="col-12 form-group" :class="{'has-danger': errors.file}">
                        <el-upload
                                ref="upload"
                                :headers="headers"
                                action=""
                                :show-file-list="true"
                                :auto-upload="false"
                                :multiple="false"
                                :limit="1"
                                :on-change="handleFileChange"
                                :on-remove="handleFileRemove"
                                accept=".xlsx,.xls">
                            <el-button slot="trigger" type="primary">Seleccione un archivo (xlsx)</el-button>
                        </el-upload>
                        <small class="form-control-feedback" v-if="errors.file" v-text="errors.file[0]"></small>
                    </div>
                    <div class="col-12 mt-4 mb-2">
                        <a class="text-dark mr-auto" href="/formats/items.xlsx" target="_new">
                            <span class="mr-2">Descargar formato de ejemplo para importar</span>
                            <i class="fa fa-download"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right mt-5">
                <el-button class="second-buton" @click.prevent="close()">Cancelar</el-button>
                <el-button
                    type="primary"
                    native-type="submit"
                    :loading="loading_submit"
                    :disabled="!selectedFile">
                    Validar y Previsualizar
                </el-button>
            </div>
        </form>

        <!-- Step 2: Preview Table -->
        <div v-if="showPreview">
            <!-- Statistics Cards -->
            <el-row :gutter="20" class="mb-4">
                <el-col :span="6">
                    <div class="stat-card">
                        <div class="stat-number">{{ stats.total }}</div>
                        <div class="stat-label">Total de Filas</div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="stat-card">
                        <div class="stat-number text-success">{{ stats.valid }}</div>
                        <div class="stat-label">Filas Válidas</div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="stat-card">
                        <div class="stat-number text-danger">{{ stats.invalid }}</div>
                        <div class="stat-label">Filas con Errores</div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="stat-card">
                        <div class="stat-number text-info">
                            <span class="text-primary">{{ stats.to_create }}</span> /
                            <span class="text-warning">{{ stats.to_update }}</span>
                        </div>
                        <div class="stat-label">Crear / Actualizar</div>
                    </div>
                </el-col>
            </el-row>

            <!-- Preview Table -->
            <el-table
                :data="previewData"
                style="width: 100%"
                max-height="400"
                stripe
                :row-class-name="tableRowClassName">

                <el-table-column
                    prop="row_number"
                    label="Fila"
                    width="60">
                </el-table-column>

                <el-table-column
                    label="Estado"
                    width="80"
                    align="center">
                    <template slot-scope="scope">
                        <i v-if="scope.row.is_valid"
                           class="el-icon-circle-check"
                           style="color: #67C23A; font-size: 18px;"></i>
                        <i v-else
                           class="el-icon-circle-close"
                           style="color: #F56C6C; font-size: 18px;"></i>
                    </template>
                </el-table-column>

                <el-table-column
                    prop="internal_id"
                    label="Código Interno"
                    width="120">
                </el-table-column>

                <el-table-column
                    prop="description"
                    label="Descripción"
                    min-width="200">
                </el-table-column>

                <el-table-column
                    label="Categoría"
                    width="150">
                    <template slot-scope="scope">
                        <div>{{ scope.row.category || '-' }}</div>
                        <small v-if="scope.row.category_action" class="text-info">
                            {{ scope.row.category_action }}
                        </small>
                    </template>
                </el-table-column>

                <el-table-column
                    label="Marca"
                    width="120">
                    <template slot-scope="scope">
                        <div>{{ scope.row.brand || '-' }}</div>
                        <small v-if="scope.row.brand_action" class="text-info">
                            {{ scope.row.brand_action }}
                        </small>
                    </template>
                </el-table-column>

                <el-table-column
                    label="Precio Venta"
                    width="120">
                    <template slot-scope="scope">
                        <div v-if="scope.row.action === 'update' && scope.row.existing_price">
                            <div class="text-muted">
                                <small>Actual: {{ formatNumber(scope.row.existing_price) }}</small>
                            </div>
                            <div class="text-primary">
                                Nuevo: {{ formatNumber(scope.row.sale_price) }}
                            </div>
                        </div>
                        <div v-else>
                            {{ formatNumber(scope.row.sale_price) }}
                        </div>
                    </template>
                </el-table-column>

                <el-table-column
                    label="Stock"
                    width="180">
                    <template slot-scope="scope">
                        <div v-if="scope.row.action === 'update' && scope.row.preview && scope.row.preview.stock_action === 'sum'">
                            <div class="text-muted">
                                <small>Actual: {{ scope.row.preview.stock_current }}</small>
                            </div>
                            <div class="text-warning" style="font-weight: 600;">
                                + {{ scope.row.preview.stock_to_add }} (se sumará)
                            </div>
                            <div class="text-success" style="font-weight: bold; border-top: 1px solid #ddd; padding-top: 2px; margin-top: 2px;">
                                = {{ scope.row.preview.stock_final }}
                            </div>
                        </div>
                        <div v-else>
                            <span class="text-success" style="font-weight: 600;">{{ scope.row.stock }}</span>
                            <small class="text-muted" style="display: block;">(stock inicial)</small>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column
                    label="Acción"
                    width="100">
                    <template slot-scope="scope">
                        <el-tag
                            :type="scope.row.action === 'create' ? 'success' : 'warning'"
                            size="small">
                            {{ scope.row.action === 'create' ? 'CREAR' : 'ACTUALIZAR' }}
                        </el-tag>
                    </template>
                </el-table-column>

                <el-table-column
                    label="Errores"
                    min-width="200">
                    <template slot-scope="scope">
                        <div v-if="scope.row.errors && scope.row.errors.length">
                            <el-popover
                                placement="top"
                                width="400"
                                trigger="hover">
                                <div v-for="(error, index) in scope.row.errors" :key="index" class="mb-1">
                                    • {{ error }}
                                </div>
                                <el-button
                                    slot="reference"
                                    type="text"
                                    size="small"
                                    class="text-danger">
                                    Ver {{ scope.row.errors.length }} error(es)
                                </el-button>
                            </el-popover>
                        </div>
                        <span v-else class="text-success">
                            <i class="el-icon-check"></i> OK
                        </span>
                    </template>
                </el-table-column>
            </el-table>

            <!-- Actions -->
            <div class="form-actions text-right mt-5">
                <el-button
                    class="second-buton"
                    @click="cancelImport"
                    :loading="loading_cancel">
                    Cancelar Importación
                </el-button>

                <el-button
                    type="primary"
                    @click="processImport"
                    :loading="loading_process"
                    :disabled="stats.valid === 0">
                    <i class="el-icon-upload2"></i>
                    Procesar {{ stats.valid }} Item(s) Válidos
                </el-button>
            </div>

            <!-- Info Alerts -->
            <el-alert
                v-if="stats.invalid > 0"
                title="Atención"
                type="warning"
                :closable="false"
                class="mt-3">
                <span>Se encontraron {{ stats.invalid }} filas con errores. Solo se procesarán las filas válidas.</span>
            </el-alert>

            <el-alert
                v-if="stats.to_update > 0"
                title="Productos con Stock Existente en este Almacén"
                type="info"
                :closable="false"
                class="mt-3">
                <div>
                    <strong>{{ stats.to_update }}</strong> producto(s) ya tiene(n) stock en este almacén.
                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                        <li>Los <strong>precios y datos</strong> se actualizarán</li>
                        <li>El <strong>stock se SUMARÁ</strong> al stock actual de este almacén</li>
                    </ul>
                </div>
            </el-alert>
        </div>
    </el-dialog>
</template>

<script>
    export default {
        props: ['showDialog'],
        data() {
            return {
                loading_submit: false,
                loading_process: false,
                loading_cancel: false,
                headers: headers_token,
                titleDialog: null,
                resource: 'items',
                errors: {},
                form: {},
                warehouses: [],
                selectedFile: null,
                showPreview: false,
                previewData: [],
                stats: {
                    total: 0,
                    valid: 0,
                    invalid: 0,
                    to_create: 0,
                    to_update: 0
                },
                batchId: null
            }
        },
        async created() {
            this.initForm();
            await this.onFetchTables();
        },
        methods: {
            async onFetchTables() {
                this.loading_submit = true;
                await this.$http.get('/items/import/tables').then(response => {
                    this.warehouses = response.data.warehouses;
                    // Set default warehouse if only one exists
                    if (this.warehouses.length === 1) {
                        this.form.warehouse_id = this.warehouses[0].id;
                    }
                }).finally(() => this.loading_submit = false);
            },

            initForm() {
                this.errors = {}
                this.form = {
                    warehouse_id: null
                }
                this.selectedFile = null
                this.showPreview = false
                this.previewData = []
                this.batchId = null
                this.stats = {
                    total: 0,
                    valid: 0,
                    invalid: 0,
                    to_create: 0,
                    to_update: 0
                }
            },

            create() {
                this.titleDialog = 'Importar Productos'
            },

            handleFileChange(file) {
                this.selectedFile = file.raw
            },

            handleFileRemove() {
                this.selectedFile = null
            },

            async validateFile() {
                if (!this.form.warehouse_id) {
                    this.$message.warning('Seleccione un almacén para poder continuar');
                    return;
                }

                if (!this.selectedFile) {
                    this.$message.warning('Seleccione un archivo para importar');
                    return;
                }

                this.loading_submit = true;
                const formData = new FormData();
                formData.append('file', this.selectedFile);
                formData.append('warehouse_id', this.form.warehouse_id);

                try {
                    const response = await this.$http.post('/items/import/validate', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    if (response.data.success) {
                        this.previewData = response.data.data.preview;
                        this.stats = response.data.data.stats;
                        this.batchId = response.data.data.batch_id;
                        this.showPreview = true;

                        if (this.stats.valid === 0) {
                            this.$message.error('No hay filas válidas para procesar. Por favor, revise los errores.');
                        } else {
                            this.$message.success(`Archivo validado: ${this.stats.valid} filas válidas de ${this.stats.total}`);
                        }
                    } else {
                        this.$message.error(response.data.message);
                    }
                } catch (error) {
                    console.error(error);
                    if (error.response && error.response.data) {
                        this.$message.error(error.response.data.message || 'Error al validar el archivo');
                    } else {
                        this.$message.error('Error al validar el archivo');
                    }
                } finally {
                    this.loading_submit = false;
                }
            },

            async processImport() {
                if (!this.batchId) {
                    this.$message.error('No se encontró información del batch');
                    return;
                }

                this.$confirm(
                    `¿Está seguro de procesar ${this.stats.valid} items válidos?`,
                    'Confirmar Importación',
                    {
                        confirmButtonText: 'Sí, Procesar',
                        cancelButtonText: 'Cancelar',
                        type: 'warning'
                    }
                ).then(async () => {
                    this.loading_process = true;

                    try {
                        const response = await this.$http.post('/items/import/process-batch', {
                            batch_id: this.batchId
                        });

                        if (response.data.success) {
                            this.$message.success(response.data.message);
                            this.$eventHub.$emit('reloadData');
                            this.$eventHub.$emit('reloadTables');
                            this.close();
                        } else {
                            this.$message.error(response.data.message);
                        }
                    } catch (error) {
                        console.error(error);
                        this.$message.error('Error al procesar la importación');
                    } finally {
                        this.loading_process = false;
                    }
                }).catch(() => {
                    // User cancelled
                });
            },

            async cancelImport() {
                if (!this.batchId) {
                    this.showPreview = false;
                    // Esperar a que el DOM se actualice antes de limpiar archivos
                    this.$nextTick(() => {
                        if (this.$refs.upload) {
                            this.$refs.upload.clearFiles();
                        }
                    });
                    return;
                }

                this.$confirm(
                    'Se cancelará la importación y se eliminarán los datos temporales. ¿Desea continuar?',
                    'Cancelar Importación',
                    {
                        confirmButtonText: 'Sí, Cancelar',
                        cancelButtonText: 'No',
                        type: 'warning'
                    }
                ).then(async () => {
                    this.loading_cancel = true;

                    try {
                        const response = await this.$http.delete('/items/import/delete-batch', {
                            data: { batch_id: this.batchId }
                        });

                        if (response.data.success) {
                            this.$message.info('Importación cancelada');
                            this.showPreview = false;
                            this.initForm();

                            // Esperar a que el DOM se actualice antes de limpiar archivos
                            this.$nextTick(() => {
                                if (this.$refs.upload) {
                                    this.$refs.upload.clearFiles();
                                }
                            });
                        }
                    } catch (error) {
                        console.error(error);
                        this.$message.error('Error al cancelar la importación');
                    } finally {
                        this.loading_cancel = false;
                    }
                }).catch(() => {
                    // User cancelled the cancellation
                });
            },

            close() {
                if (this.batchId && this.showPreview) {
                    // Clean up batch when closing
                    this.$http.delete('/items/import/delete-batch', {
                        data: { batch_id: this.batchId }
                    }).catch(() => {});
                }

                this.$emit('update:showDialog', false);
                this.initForm();

                // Esperar a que el DOM se actualice antes de limpiar archivos
                this.$nextTick(() => {
                    if (this.$refs.upload) {
                        this.$refs.upload.clearFiles();
                    }
                });
            },

            tableRowClassName({row}) {
                if (!row.is_valid) {
                    return 'error-row';
                }
                return '';
            },

            formatNumber(value) {
                if (value === null || value === undefined) return '0.00';
                return parseFloat(value).toFixed(2);
            }
        }
    }
</script>

<style scoped>
.dialog-import >>> .el-dialog__body {
    padding: 20px;
}

.stat-card {
    background: white;
    border: 1px solid #e8e8e8;
    border-radius: 4px;
    padding: 15px;
    text-align: center;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.el-table >>> .error-row {
    background-color: #fef0f0;
}

.el-table >>> .error-row:hover > td {
    background-color: #fef0f0 !important;
}

.text-success { color: #67C23A; }
.text-danger { color: #F56C6C; }
.text-warning { color: #E6A23C; }
.text-info { color: #909399; }
.text-primary { color: #409EFF; }
.text-muted { color: #909399; }

.mb-1 { margin-bottom: 4px; }
.mb-3 { margin-bottom: 12px; }
.mb-4 { margin-bottom: 16px; }
.mt-3 { margin-top: 12px; }
.mt-5 { margin-top: 20px; }
</style>