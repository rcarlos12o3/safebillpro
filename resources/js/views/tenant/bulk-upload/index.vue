<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active">
                    <span>Ventas</span>
                </li>
                <li>
                    <span class="ti ti-zip"></span> Carga Masiva
                </li>
            </ol>
        </div>

        <div class="card mb-0">
            <div class="card-header bg-info">
                <h3 class="card-title text-white">Carga Masiva de Documentos</h3>
            </div>
            <div class="card-body">

                <!-- Paso 1: Upload y Fecha -->
                <div v-if="!showVerificationTable" class="row">
                    <div class="col-md-6">
                        <h4>1. Seleccionar Fecha de Emisión</h4>
                        <el-date-picker
                            v-model="dateOfIssue"
                            type="date"
                            placeholder="Fecha de emisión"
                            format="dd/MM/yyyy"
                            value-format="yyyy-MM-dd"
                            style="width: 100%">
                        </el-date-picker>

                        <h4 class="mt-4">2. Subir Archivo Excel</h4>
                        <p class="text-muted">Formatos soportados: .xlsx, .xls, .csv (Máx. 10MB)</p>

                        <el-upload
                            class="upload-demo"
                            drag
                            action=""
                            :http-request="uploadFile"
                            :before-upload="beforeUpload"
                            :show-file-list="true"
                            :limit="1"
                            :disabled="!dateOfIssue"
                            accept=".xlsx,.xls,.csv">
                            <i class="el-icon-upload"></i>
                            <div class="el-upload__text">Arrastra el archivo aquí o <em>haz clic para subir</em></div>
                            <div class="el-upload__tip" slot="tip">
                                {{ dateOfIssue ? 'Archivos Excel (.xlsx, .xls, .csv) máximo 10MB' : 'Primero selecciona una fecha de emisión' }}
                            </div>
                        </el-upload>

                        <div class="mt-3">
                            <el-button type="primary" icon="el-icon-download" @click="showProductSelector">
                                Generar Plantilla Personalizada
                            </el-button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4>Instrucciones</h4>
                        <ol>
                            <li><strong>Selecciona la fecha de emisión</strong> para todos los documentos del lote</li>
                            <li>Haz clic en "Generar Plantilla Personalizada"</li>
                            <li>Selecciona los productos que venderás</li>
                            <li>Descarga la plantilla generada</li>
                            <li>Completa los datos requeridos:
                                <ul>
                                    <li><strong>SERIE:</strong> Ej. B001, F001</li>
                                    <li><strong>CUSTOMER_ID:</strong> ID del cliente (ver en <a href="/persons/customers" target="_blank">Clientes</a>)</li>
                                    <li><strong>CANTIDAD:</strong> Unidades a vender</li>
                                    <li><strong>TOTAL:</strong> Para validar (PRECIO × CANT)</li>
                                </ul>
                            </li>
                            <li>Sube el archivo y <strong>revisa la tabla de verificación</strong></li>
                            <li>Confirma y procesa el lote</li>
                        </ol>

                        <div class="alert alert-info mt-3">
                            <strong>Nota:</strong> Todos los documentos tendrán la misma fecha de emisión seleccionada.
                        </div>
                    </div>
                </div>

                <!-- Paso 2: Tabla de Verificación -->
                <div v-if="showVerificationTable">
                    <div class="mb-3">
                        <el-alert
                            :title="`Fecha de emisión: ${dateOfIssue} | Registros válidos: ${validCount} | Con errores: ${errorCount}`"
                            type="info"
                            :closable="false">
                        </el-alert>
                    </div>

                    <el-table
                        :data="verificationData"
                        style="width: 100%"
                        max-height="500"
                        :row-class-name="tableRowClassName">
                        <el-table-column
                            prop="row_number"
                            label="Fila"
                            width="70">
                        </el-table-column>
                        <el-table-column
                            label="Estado"
                            width="90">
                            <template slot-scope="scope">
                                <el-tag :type="scope.row.is_valid ? 'success' : 'danger'" size="small">
                                    {{ scope.row.is_valid ? 'OK' : 'Error' }}
                                </el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column
                            prop="data.serie"
                            label="Serie"
                            width="90">
                        </el-table-column>
                        <el-table-column
                            label="Cliente"
                            min-width="200">
                            <template slot-scope="scope">
                                <span v-if="scope.row.customer_name">
                                    {{ scope.row.customer_name }} ({{ scope.row.customer_number }})
                                </span>
                                <span v-else class="text-muted">ID: {{ scope.row.data.customer_id }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column
                            label="Producto"
                            min-width="200">
                            <template slot-scope="scope">
                                <span v-if="scope.row.item_description">
                                    {{ scope.row.item_description }}
                                </span>
                                <span v-else class="text-muted">ID: {{ scope.row.data.item_id }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column
                            prop="data.cantidad"
                            label="Cantidad"
                            width="90">
                        </el-table-column>
                        <el-table-column
                            label="Total"
                            width="100">
                            <template slot-scope="scope">
                                S/ {{ scope.row.calculated_total || scope.row.data.total || '-' }}
                            </template>
                        </el-table-column>
                        <el-table-column
                            label="Errores"
                            min-width="200">
                            <template slot-scope="scope">
                                <div v-if="scope.row.errors && scope.row.errors.length > 0">
                                    <div v-for="(error, index) in scope.row.errors" :key="index" class="text-danger small">
                                        • {{ error }}
                                    </div>
                                </div>
                                <span v-else class="text-success">-</span>
                            </template>
                        </el-table-column>
                    </el-table>

                    <div class="mt-3 text-right">
                        <el-button @click="cancelVerification">
                            Cancelar
                        </el-button>
                        <el-button
                            type="success"
                            @click="processConfirmedBatch"
                            :disabled="validCount === 0"
                            :loading="processing">
                            <i class="el-icon-check"></i> Procesar Lote ({{ validCount }} documentos)
                        </el-button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal: Selector de Productos -->
        <el-dialog
            title="Seleccionar Productos para Plantilla"
            :visible.sync="productSelectorVisible"
            width="70%"
            :close-on-click-modal="false">

            <div class="mb-3">
                <el-input
                    v-model="productSearch"
                    placeholder="Buscar producto..."
                    prefix-icon="el-icon-search"
                    clearable>
                </el-input>
            </div>

            <el-table
                ref="productTable"
                :data="filteredProducts"
                @selection-change="handleProductSelection"
                style="width: 100%"
                max-height="400"
                v-loading="loadingProducts">
                <el-table-column
                    type="selection"
                    width="55">
                </el-table-column>
                <el-table-column
                    prop="id"
                    label="ID"
                    width="80">
                </el-table-column>
                <el-table-column
                    prop="internal_id"
                    label="Código"
                    width="120">
                </el-table-column>
                <el-table-column
                    prop="description"
                    label="Producto"
                    min-width="300">
                </el-table-column>
                <el-table-column
                    label="Precio Unit."
                    width="120">
                    <template slot-scope="scope">
                        {{ scope.row.sale_unit_price }}
                    </template>
                </el-table-column>
            </el-table>

            <div class="mt-3">
                <el-alert
                    :title="`${selectedProducts.length} producto(s) seleccionado(s)`"
                    type="info"
                    :closable="false">
                </el-alert>
            </div>

            <span slot="footer" class="dialog-footer">
                <el-button @click="productSelectorVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="generateTemplate" :disabled="selectedProducts.length === 0">
                    Generar Plantilla ({{ selectedProducts.length }})
                </el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
export default {
    data() {
        return {
            dateOfIssue: null,
            showVerificationTable: false,
            verificationData: [],
            validCount: 0,
            errorCount: 0,
            batchId: null,
            processing: false,
            productSelectorVisible: false,
            allProducts: [],
            selectedProducts: [],
            loadingProducts: false,
            productSearch: ''
        }
    },
    computed: {
        filteredProducts() {
            if (!this.productSearch) {
                return this.allProducts;
            }
            const search = this.productSearch.toLowerCase();
            return this.allProducts.filter(product => {
                return (
                    product.description.toLowerCase().includes(search) ||
                    product.internal_id.toLowerCase().includes(search) ||
                    product.id.toString().includes(search)
                );
            });
        }
    },
    mounted() {
        // Sin fecha por defecto, el usuario debe seleccionar
    },
    methods: {
        beforeUpload(file) {
            if (!this.dateOfIssue) {
                this.$message.error('Primero selecciona una fecha de emisión');
                return false;
            }

            const isExcel = file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
                           file.type === 'application/vnd.ms-excel' ||
                           file.type === 'text/csv';
            const isLt10M = file.size / 1024 / 1024 < 10;

            if (!isExcel) {
                this.$message.error('El archivo debe ser de tipo Excel (.xlsx, .xls) o CSV (.csv)');
                return false;
            }
            if (!isLt10M) {
                this.$message.error('El archivo no puede pesar más de 10MB');
                return false;
            }
            return true;
        },
        uploadFile(params) {
            const formData = new FormData();
            formData.append('file', params.file);
            formData.append('type', 'documents');
            formData.append('date_of_issue', this.dateOfIssue);

            const loading = this.$loading({
                lock: true,
                text: 'Validando archivo...',
                spinner: 'el-icon-loading',
                background: 'rgba(0, 0, 0, 0.7)'
            });

            this.$http.post('/bulk-upload/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(response => {
                loading.close();

                this.$message.success(response.data.message);

                // Mostrar tabla de verificación
                this.verificationData = response.data.data.rows;
                this.validCount = response.data.data.valid_count;
                this.errorCount = response.data.data.error_count;
                this.batchId = response.data.data.batch_id;
                this.showVerificationTable = true;
            })
            .catch(error => {
                loading.close();
                const errorMessage = error.response && error.response.data && error.response.data.message
                    ? error.response.data.message
                    : 'Error al cargar el archivo';
                this.$message.error(errorMessage);
            });
        },
        tableRowClassName({row, rowIndex}) {
            return row.is_valid ? '' : 'row-error';
        },
        cancelVerification() {
            this.$confirm('¿Estás seguro de cancelar? Se perderán los datos validados.', 'Confirmar', {
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No',
                type: 'warning'
            }).then(() => {
                // Eliminar batch temporal
                this.$http.delete('/bulk-upload/delete-batch', {
                    data: { batch_id: this.batchId }
                }).then(() => {
                    this.resetForm();
                    this.$message.info('Datos descartados');
                });
            }).catch(() => {});
        },
        processConfirmedBatch() {
            if (this.validCount === 0) {
                this.$message.warning('No hay registros válidos para procesar');
                return;
            }

            this.$confirm(`Se crearán ${this.validCount} documentos electrónicos. ¿Continuar?`, 'Confirmar Procesamiento', {
                confirmButtonText: 'Sí, procesar',
                cancelButtonText: 'Cancelar',
                type: 'warning'
            }).then(() => {
                this.processing = true;

                const loading = this.$loading({
                    lock: true,
                    text: `Procesando ${this.validCount} documentos...`,
                    spinner: 'el-icon-loading',
                    background: 'rgba(0, 0, 0, 0.7)'
                });

                this.$http.post('/bulk-upload/process-batch', {
                    batch_id: this.batchId
                })
                .then(response => {
                    loading.close();
                    this.processing = false;

                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.resetForm();
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch(error => {
                    loading.close();
                    this.processing = false;

                    const errorMessage = error.response && error.response.data && error.response.data.message
                        ? error.response.data.message
                        : 'Error al procesar el lote';
                    this.$message.error(errorMessage);
                });
            }).catch(() => {});
        },
        resetForm() {
            this.showVerificationTable = false;
            this.verificationData = [];
            this.validCount = 0;
            this.errorCount = 0;
            this.batchId = null;
            this.processing = false;
        },
        showProductSelector() {
            this.productSelectorVisible = true;
            if (this.allProducts.length === 0) {
                this.loadProducts();
            }
        },
        loadProducts() {
            this.loadingProducts = true;
            this.$http.get('/items/records')
                .then(response => {
                    this.allProducts = response.data.data || [];
                })
                .catch(error => {
                    this.$message.error('Error al cargar productos');
                    console.error('Error:', error);
                })
                .finally(() => {
                    this.loadingProducts = false;
                });
        },
        handleProductSelection(selection) {
            this.selectedProducts = selection;
        },
        generateTemplate() {
            if (this.selectedProducts.length === 0) {
                this.$message.warning('Debes seleccionar al menos un producto');
                return;
            }

            const productIds = this.selectedProducts.map(p => p.id).join(',');

            this.$message.success('Generando plantilla...');

            window.location.href = `/bulk-upload/download-template?type=documents&items=${productIds}`;

            this.productSelectorVisible = false;
        }
    }
}
</script>

<style scoped>
.upload-demo {
    margin: 20px 0;
}

.row-error {
    background-color: #fef0f0 !important;
}
</style>
