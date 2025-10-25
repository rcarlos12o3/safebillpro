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

                <!-- Pestañas -->
                <el-tabs v-model="activeTab" @tab-click="handleTabClick">
                    <el-tab-pane label="Nueva Carga" name="upload">
                        <template slot="label">
                            <i class="el-icon-upload"></i> Nueva Carga
                        </template>
                        <div class="tab-content-wrapper">

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
                                    <li><strong>DOCUMENTO_ID</strong> (opcional): Mismo ID para agrupar items en un documento</li>
                                    <li><strong>SERIE:</strong> Ej. B001, F001</li>
                                    <li><strong>TIPO_DOCUMENTO:</strong> 1 (DNI), 6 (RUC), etc.</li>
                                    <li><strong>NUMERO_DOCUMENTO:</strong> Número del cliente</li>
                                    <li><strong>NOMBRE_CLIENTE:</strong> Nombre completo o razón social</li>
                                    <li><strong>DIRECCION</strong> (opcional): Dirección del cliente</li>
                                    <li><strong>CANTIDAD:</strong> Unidades a vender</li>
                                </ul>
                            </li>
                            <li>Sube el archivo y <strong>revisa la tabla de verificación</strong></li>
                            <li>Confirma y procesa el lote</li>
                        </ol>

                        <div class="alert alert-info mt-3">
                            <strong>Límite:</strong> Máximo {{ maxRows }} filas por archivo.
                        </div>
                        <div class="alert alert-success mt-2">
                            <strong>✓ Validación automática de stock</strong><br>
                            <strong>✓ Creación automática de clientes nuevos</strong>
                        </div>
                    </div>
                </div>

                <!-- Paso 2: Tabla de Verificación -->
                <div v-if="showVerificationTable">
                    <div class="mb-3">
                        <el-row :gutter="20">
                            <el-col :span="8">
                                <el-card shadow="hover">
                                    <div class="text-center">
                                        <h2 class="text-primary mb-0">{{ documentCount }}</h2>
                                        <p class="text-muted mb-0">Documentos a crear</p>
                                    </div>
                                </el-card>
                            </el-col>
                            <el-col :span="8">
                                <el-card shadow="hover">
                                    <div class="text-center">
                                        <h2 class="text-success mb-0">{{ validCount }}</h2>
                                        <p class="text-muted mb-0">Items válidos</p>
                                    </div>
                                </el-card>
                            </el-col>
                            <el-col :span="8">
                                <el-card shadow="hover">
                                    <div class="text-center">
                                        <h2 class="text-danger mb-0">{{ errorCount }}</h2>
                                        <p class="text-muted mb-0">Items con errores</p>
                                    </div>
                                </el-card>
                            </el-col>
                        </el-row>

                        <el-alert
                            :title="`Fecha de emisión: ${dateOfIssue}`"
                            type="info"
                            class="mt-3"
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
                            label="Stock"
                            width="100">
                            <template slot-scope="scope">
                                <el-tag
                                    v-if="scope.row.stock_available !== undefined"
                                    :type="getStockTagType(scope.row)"
                                    size="small">
                                    {{ scope.row.stock_available }}
                                </el-tag>
                                <span v-else class="text-muted">-</span>
                            </template>
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

                    <div class="mt-3">
                        <el-button @click="cancelVerification">
                            Cancelar
                        </el-button>
                        <el-button
                            v-if="errorCount > 0"
                            type="warning"
                            icon="el-icon-download"
                            @click="exportErrors">
                            Exportar Errores ({{ errorCount }})
                        </el-button>
                        <el-button
                            type="success"
                            @click="processConfirmedBatch"
                            :disabled="validCount === 0"
                            :loading="processing"
                            style="float: right;">
                            <i class="el-icon-check"></i> Procesar Lote ({{ documentCount }} documentos)
                        </el-button>
                    </div>
                </div>

                        </div><!-- Fin tab-content-wrapper -->
                    </el-tab-pane>

                    <!-- Pestaña: Historial -->
                    <el-tab-pane label="Historial" name="history">
                        <template slot="label">
                            <i class="el-icon-time"></i> Historial
                        </template>

                        <div class="mb-3">
                            <el-button type="primary" icon="el-icon-refresh" @click="loadHistory" size="small">
                                Actualizar
                            </el-button>
                        </div>

                        <el-table
                            :data="historyData"
                            v-loading="loadingHistory"
                            style="width: 100%"
                            :default-sort="{prop: 'upload_date', order: 'descending'}">

                            <el-table-column
                                prop="upload_date"
                                label="Fecha de Carga"
                                width="160"
                                sortable>
                                <template slot-scope="scope">
                                    {{ formatDateTime(scope.row.upload_date) }}
                                </template>
                            </el-table-column>

                            <el-table-column
                                prop="date_of_issue"
                                label="Fecha Emisión"
                                width="120">
                                <template slot-scope="scope">
                                    {{ formatDate(scope.row.date_of_issue) }}
                                </template>
                            </el-table-column>

                            <el-table-column
                                prop="user.name"
                                label="Usuario"
                                width="150">
                            </el-table-column>

                            <el-table-column
                                label="Documentos"
                                width="100"
                                align="center">
                                <template slot-scope="scope">
                                    <el-tag type="primary" size="small">
                                        {{ scope.row.document_count }}
                                    </el-tag>
                                </template>
                            </el-table-column>

                            <el-table-column
                                label="Items"
                                width="100"
                                align="center">
                                <template slot-scope="scope">
                                    <span class="text-muted">{{ scope.row.total_rows }}</span>
                                </template>
                            </el-table-column>

                            <el-table-column
                                label="Estado"
                                width="180">
                                <template slot-scope="scope">
                                    <div>
                                        <el-tag type="success" size="mini">
                                            {{ scope.row.processed_rows }} procesados
                                        </el-tag>
                                        <el-tag v-if="scope.row.pending_rows > 0" type="warning" size="mini">
                                            {{ scope.row.pending_rows }} pendientes
                                        </el-tag>
                                        <el-tag v-if="scope.row.error_processing_rows > 0" type="danger" size="mini">
                                            {{ scope.row.error_processing_rows }} errores
                                        </el-tag>
                                    </div>
                                </template>
                            </el-table-column>

                            <el-table-column
                                label="Errores Val."
                                width="100"
                                align="center">
                                <template slot-scope="scope">
                                    <el-tag v-if="scope.row.error_rows > 0" type="danger" size="small">
                                        {{ scope.row.error_rows }}
                                    </el-tag>
                                    <span v-else class="text-success">-</span>
                                </template>
                            </el-table-column>

                            <el-table-column
                                label="Acciones"
                                width="200"
                                fixed="right">
                                <template slot-scope="scope">
                                    <el-button
                                        type="info"
                                        size="mini"
                                        icon="el-icon-view"
                                        @click="viewBatchDetails(scope.row)">
                                        Ver
                                    </el-button>
                                    <el-button
                                        v-if="scope.row.error_rows > 0 || scope.row.error_processing_rows > 0"
                                        type="warning"
                                        size="mini"
                                        icon="el-icon-download"
                                        @click="downloadBatchErrors(scope.row.batch_id)">
                                        Errores
                                    </el-button>
                                </template>
                            </el-table-column>
                        </el-table>

                        <div class="mt-3 text-right" v-if="historyPagination.total > historyPagination.per_page">
                            <el-pagination
                                @current-change="handleHistoryPageChange"
                                :current-page="historyPagination.current_page"
                                :page-size="historyPagination.per_page"
                                layout="total, prev, pager, next"
                                :total="historyPagination.total">
                            </el-pagination>
                        </div>
                    </el-tab-pane>

                </el-tabs>

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

        <!-- Modal: Detalle del Batch -->
        <el-dialog
            title="Detalle del Lote"
            :visible.sync="batchDetailVisible"
            width="80%"
            :close-on-click-modal="false">

            <div v-if="batchDetailData">
                <el-alert
                    :title="`Batch ID: ${batchDetailData.batch_id} | ${batchDetailData.total_documents} documento(s) | ${batchDetailData.total_items} item(s)`"
                    type="info"
                    :closable="false"
                    class="mb-3">
                </el-alert>

                <el-table
                    :data="batchDetailData.documents"
                    style="width: 100%"
                    max-height="500">

                    <el-table-column type="expand">
                        <template slot-scope="props">
                            <div class="p-3">
                                <h5>Items del Documento:</h5>
                                <el-table :data="props.row.items" size="small">
                                    <el-table-column prop="row_data.item_id" label="Item ID" width="80"></el-table-column>
                                    <el-table-column label="Producto" min-width="200">
                                        <template slot-scope="scope">
                                            {{ scope.row.row_data.producto || '-' }}
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Cantidad" width="100">
                                        <template slot-scope="scope">
                                            {{ scope.row.row_data.cantidad }}
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Estado" width="100">
                                        <template slot-scope="scope">
                                            <el-tag :type="scope.row.status === 'processed' ? 'success' : scope.row.status === 'error' ? 'danger' : 'warning'" size="mini">
                                                {{ scope.row.status }}
                                            </el-tag>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Errores" min-width="200">
                                        <template slot-scope="scope">
                                            <span v-if="scope.row.validation_errors" class="text-danger small">
                                                {{ scope.row.validation_errors }}
                                            </span>
                                            <span v-else-if="scope.row.process_error" class="text-danger small">
                                                {{ scope.row.process_error }}
                                            </span>
                                            <span v-else class="text-success">-</span>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column
                        prop="document_group_id"
                        label="Doc ID"
                        width="150">
                    </el-table-column>

                    <el-table-column
                        label="Items"
                        width="80"
                        align="center">
                        <template slot-scope="scope">
                            <el-tag size="small">{{ scope.row.items_count }}</el-tag>
                        </template>
                    </el-table-column>

                    <el-table-column
                        label="Estado"
                        width="100">
                        <template slot-scope="scope">
                            <el-tag :type="scope.row.status === 'processed' ? 'success' : scope.row.status === 'error' ? 'danger' : 'warning'" size="small">
                                {{ scope.row.status }}
                            </el-tag>
                        </template>
                    </el-table-column>

                    <el-table-column
                        label="Válido"
                        width="80"
                        align="center">
                        <template slot-scope="scope">
                            <i v-if="scope.row.is_valid" class="el-icon-check text-success" style="font-size: 18px;"></i>
                            <i v-else class="el-icon-close text-danger" style="font-size: 18px;"></i>
                        </template>
                    </el-table-column>

                    <el-table-column
                        prop="document_id"
                        label="Doc Creado"
                        width="100">
                        <template slot-scope="scope">
                            <a v-if="scope.row.document_id" :href="`/documents/${scope.row.document_id}`" target="_blank" class="text-primary">
                                #{{ scope.row.document_id }}
                            </a>
                            <span v-else class="text-muted">-</span>
                        </template>
                    </el-table-column>
                </el-table>
            </div>

            <span slot="footer" class="dialog-footer">
                <el-button @click="batchDetailVisible = false">Cerrar</el-button>
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
            documentCount: 0,
            batchId: null,
            processing: false,
            productSelectorVisible: false,
            allProducts: [],
            selectedProducts: [],
            loadingProducts: false,
            productSearch: '',
            maxRows: 500,
            // History tab
            activeTab: 'upload',
            historyData: [],
            loadingHistory: false,
            historyPagination: {
                current_page: 1,
                per_page: 50,
                total: 0
            },
            batchDetailVisible: false,
            batchDetailData: null
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
                this.documentCount = response.data.data.document_count || this.validCount;
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
        },
        getStockTagType(row) {
            if (!row.stock_available || row.stock_available === undefined) {
                return 'info';
            }

            const quantity = parseFloat(row.data.cantidad || 0);
            const stock = parseFloat(row.stock_available);

            if (quantity > stock) {
                return 'danger'; // Rojo: stock insuficiente
            } else if (stock < quantity * 2) {
                return 'warning'; // Amarillo: stock bajo
            } else {
                return 'success'; // Verde: stock OK
            }
        },
        exportErrors() {
            if (!this.batchId) {
                this.$message.error('No hay un lote activo');
                return;
            }

            this.$message.info('Generando reporte de errores...');
            window.location.href = `/bulk-upload/export-errors?batch_id=${this.batchId}`;
        },
        // History tab methods
        handleTabClick(tab) {
            if (tab.name === 'history') {
                this.loadHistory();
            }
        },
        loadHistory(page = 1) {
            this.loadingHistory = true;

            this.$http.get('/bulk-upload/history', {
                params: {
                    page: page,
                    per_page: this.historyPagination.per_page
                }
            })
            .then(response => {
                this.historyData = response.data.data || [];
                this.historyPagination = {
                    current_page: response.data.current_page || 1,
                    per_page: response.data.per_page || 50,
                    total: response.data.total || 0
                };
            })
            .catch(error => {
                this.$message.error('Error al cargar historial');
                console.error('Error:', error);
            })
            .finally(() => {
                this.loadingHistory = false;
            });
        },
        handleHistoryPageChange(page) {
            this.loadHistory(page);
        },
        viewBatchDetails(row) {
            const loading = this.$loading({
                lock: true,
                text: 'Cargando detalles...',
                spinner: 'el-icon-loading',
                background: 'rgba(0, 0, 0, 0.7)'
            });

            this.$http.get('/bulk-upload/batch-details', {
                params: {
                    batch_id: row.batch_id
                }
            })
            .then(response => {
                // El backend devuelve {success: true, data: {...}}
                if (response.data.success && response.data.data) {
                    this.batchDetailData = response.data.data;
                    this.batchDetailVisible = true;
                } else {
                    this.$message.error('No se pudieron cargar los detalles');
                }
            })
            .catch(error => {
                this.$message.error('Error al cargar detalles del lote');
                console.error('Error:', error);
            })
            .finally(() => {
                loading.close();
            });
        },
        downloadBatchErrors(batchId) {
            this.$message.info('Generando reporte de errores...');
            window.location.href = `/bulk-upload/export-errors?batch_id=${batchId}`;
        },
        formatDateTime(datetime) {
            if (!datetime) return '-';

            const date = new Date(datetime);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `${day}/${month}/${year} ${hours}:${minutes}`;
        },
        formatDate(date) {
            if (!date) return '-';

            // Si viene en formato YYYY-MM-DD, simplemente reformateamos
            if (typeof date === 'string' && date.match(/^\d{4}-\d{2}-\d{2}/)) {
                const parts = date.split(/[-\s]/);
                const [year, month, day] = parts;
                return `${day}/${month}/${year}`;
            }

            // Si es un objeto Date
            const d = new Date(date);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();

            return `${day}/${month}/${year}`;
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
