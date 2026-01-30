<template>
    <el-dialog :title="titleDialog" width="80%" :visible="showDialog" :close-on-click-modal="false" @close="close" @open="create">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <!-- Toggle modo individual/masivo -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <el-radio-group v-model="mode" @change="changeMode">
                            <el-radio-button label="individual">Individual (1 día)</el-radio-button>
                            <el-radio-button label="massive">Masivo (rango de fechas)</el-radio-button>
                        </el-radio-group>
                    </div>
                </div>

                <!-- MODO INDIVIDUAL -->
                <template v-if="mode === 'individual'">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group" :class="{'has-danger': errors.date_of_reference}">
                                <label class="control-label white-space">Fecha de emisión de comprobantes</label>
                                <el-date-picker v-model="form.date_of_reference" type="date" :clearable="false" value-format="yyyy-MM-dd" @change="changeDateOfReference"></el-date-picker>
                                <small class="form-control-feedback" v-if="errors.date_of_reference" v-text="errors.date_of_reference[0]"></small>
                            </div>
                        </div>

                        <div class="col-md-4" v-if="show_summary_status_type">
                            <div class="form-group" :class="{'has-danger': errors.summary_status_type_id}">
                                <label class="control-label">Tipo de estado</label>
                                <el-select v-model="form.summary_status_type_id"
                                            filterable>
                                    <el-option v-for="option in summary_status_types"
                                                :key="option.id"
                                                :label="option.description"
                                                :value="option.id"></el-option>
                                </el-select>
                                <small class="form-control-feedback" v-if="errors.summary_status_type_id" v-text="errors.summary_status_type_id[0]"></small>
                            </div>
                        </div>

                        <div class="col-md-4 d-flex align-items-end justify-content-end pt-2">
                            <div class="form-group">
                                <el-button  type="primary"
                                            @click.prevent="clickSearchDocuments"
                                            dusk="search-documents"
                                >
                                    Buscar comprobantes
                                </el-button>
                            </div>
                        </div>

                        <div class="col-md-4 d-flex align-items-end justify-content-end pt-2"
                             v-if="form.documents.length > 0"
                        >
                            <el-dropdown :hide-on-click="false" slot="showhide">
                                <el-button type="primary">
                                    Mostrar/Ocultar columnas<i class="el-icon-arrow-down el-icon--right"></i>
                                </el-button>
                                <el-dropdown-menu slot="dropdown">
                                    <el-dropdown-item v-for="(column, index) in columns"
                                                      :key="index">
                                        <el-checkbox
                                            v-model="column.visible">{{ column.title }}</el-checkbox>
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </el-dropdown>
                        </div>
                    </div>
                    <div class="row" v-if="form.documents.length > 0">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Número</th>
                                        <th class="text-center">Moneda</th>
                                        <th class="text-right" v-if="columns.total_exportation.visible">T.Exportación</th>
                                        <th class="text-right" v-if="columns.total_free.visible">T.Gratuita</th>
                                        <th class="text-right" v-if="columns.total_unaffected.visible">T.Inafecta</th>
                                        <th class="text-right" v-if="columns.total_exonerated.visible">T.Exonerado</th>
                                        <th class="text-right" v-if="columns.total_charge.visible">T.Cargos</th>
                                        <th class="text-right">T.Gravado</th>
                                        <th class="text-right">T.Igv</th>
                                        <th class="text-right">Total</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(row, index) in form.documents" :key="index">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ row.number }}<br/>
                                            <small v-text="row.document_type_description"></small><br/>
                                            <small v-if="row.affected_document" v-text="row.affected_document"></small>
                                        </td>
                                        <td class="text-center">{{ row.currency_type_id }}</td>
                                        <td
                                            class="text-right"
                                            v-if="columns.total_exportation.visible"
                                        >
                                            {{ row.total_exportation }}
                                        </td>
                                        <td
                                            class="text-right"
                                            v-if="columns.total_free.visible"
                                        >
                                            {{ row.total_free }}
                                        </td>
                                        <td
                                            class="text-right"
                                            v-if="columns.total_unaffected.visible"
                                        >
                                            {{ row.total_unaffected }}
                                        </td>
                                        <td
                                            class="text-right"
                                            v-if="columns.total_exonerated.visible"
                                        >
                                            {{ row.total_exonerated }}
                                        </td>
                                        <td
                                            class="text-right"
                                            v-if="columns.total_charge.visible"
                                        >
                                            {{ row.total_charge }}
                                        </td>
                                        <td class="text-right">{{ row.total_taxed }}</td>
                                        <td class="text-right">{{ row.total_igv }}</td>
                                        <td class="text-right white-space">{{ row.total }}</td>
                                        <td class="text-right">
                                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickRemoveDocument(index)">x</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- MODO MASIVO -->
                <template v-if="mode === 'massive'">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Fecha inicio</label>
                                <el-date-picker
                                    v-model="massive.date_start"
                                    type="date"
                                    :clearable="false"
                                    value-format="yyyy-MM-dd"
                                    :disabled="massive.processing"
                                ></el-date-picker>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Fecha fin</label>
                                <el-date-picker
                                    v-model="massive.date_end"
                                    type="date"
                                    :clearable="false"
                                    value-format="yyyy-MM-dd"
                                    :disabled="massive.processing"
                                ></el-date-picker>
                            </div>
                        </div>
                        <div class="col-md-3" v-if="show_summary_status_type">
                            <div class="form-group">
                                <label class="control-label">Tipo de estado</label>
                                <el-select v-model="form.summary_status_type_id" filterable :disabled="massive.processing">
                                    <el-option v-for="option in summary_status_types"
                                                :key="option.id"
                                                :label="option.description"
                                                :value="option.id"></el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end pt-2">
                            <el-button
                                type="primary"
                                @click.prevent="searchDaysWithDocuments"
                                :loading="massive.loading_search"
                                :disabled="massive.processing"
                            >
                                Buscar días
                            </el-button>
                        </div>
                    </div>

                    <!-- Resultados de búsqueda masiva -->
                    <template v-if="massive.days.length > 0">
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong>{{ massive.total_days }}</strong> días con
                                    <strong>{{ massive.total_documents }}</strong> documentos pendientes
                                </div>
                            </div>
                        </div>

                        <!-- Barra de progreso -->
                        <div class="row mt-2" v-if="massive.processing">
                            <div class="col-md-12">
                                <el-progress
                                    :percentage="massive.progress_percentage"
                                    :status="massive.progress_status"
                                ></el-progress>
                                <p class="text-center mt-2">
                                    Procesando: {{ massive.current_date }}
                                    ({{ massive.processed_count }}/{{ massive.total_days }})
                                </p>
                            </div>
                        </div>

                        <!-- Tabla de días -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Fecha</th>
                                                <th class="text-center">Documentos</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-center">Mensaje</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(day, index) in massive.days" :key="index"
                                                :class="{
                                                    'table-success': day.status === 'success',
                                                    'table-danger': day.status === 'error',
                                                    'table-warning': day.status === 'processing',
                                                    'table-secondary': day.status === 'skipped'
                                                }">
                                                <td>{{ index + 1 }}</td>
                                                <td>{{ day.date_of_issue }}</td>
                                                <td class="text-center">{{ day.total }}</td>
                                                <td class="text-center">
                                                    <span v-if="day.status === 'pending'" class="badge badge-secondary">Pendiente</span>
                                                    <span v-else-if="day.status === 'processing'" class="badge badge-warning">
                                                        <i class="fas fa-spinner fa-spin"></i> Enviando
                                                    </span>
                                                    <span v-else-if="day.status === 'sent'" class="badge badge-primary">Enviado</span>
                                                    <span v-else-if="day.status === 'querying'" class="badge badge-info">
                                                        <i class="fas fa-spinner fa-spin"></i> Consultando
                                                    </span>
                                                    <span v-else-if="day.status === 'accepted'" class="badge badge-success">Aceptado</span>
                                                    <span v-else-if="day.status === 'rejected'" class="badge badge-danger">Rechazado</span>
                                                    <span v-else-if="day.status === 'query_error'" class="badge badge-warning">Error consulta</span>
                                                    <span v-else-if="day.status === 'error'" class="badge badge-danger">Error envío</span>
                                                    <span v-else-if="day.status === 'skipped'" class="badge badge-secondary">Sin docs</span>
                                                </td>
                                                <td class="text-center">
                                                    <small>{{ day.message || '-' }}</small>
                                                    <br v-if="day.identifier">
                                                    <small v-if="day.identifier" class="text-muted">{{ day.identifier }}</small>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen final -->
                        <div class="row mt-3" v-if="massive.finished">
                            <div class="col-md-12">
                                <div class="alert" :class="{
                                    'alert-success': massive.errors_count === 0 && massive.rejected_count === 0 && massive.query_errors_count === 0,
                                    'alert-warning': massive.errors_count > 0 || massive.rejected_count > 0 || massive.query_errors_count > 0
                                }">
                                    <strong>Proceso finalizado:</strong>
                                    <span class="text-success">{{ massive.accepted_count }} aceptados</span>,
                                    <span class="text-danger" v-if="massive.rejected_count > 0">{{ massive.rejected_count }} rechazados,</span>
                                    <span class="text-warning" v-if="massive.query_errors_count > 0">{{ massive.query_errors_count }} error consulta,</span>
                                    <span class="text-danger" v-if="massive.errors_count > 0">{{ massive.errors_count }} error envío,</span>
                                    <span class="text-muted">{{ massive.skipped_count }} sin documentos</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </template>
            </div>

            <div class="form-actions text-right pt-2">
                <el-button class="second-buton" @click.prevent="close()" :disabled="massive.processing">Cancelar</el-button>

                <!-- Botón modo individual -->
                <el-button
                    v-if="mode === 'individual'"
                    type="primary"
                    native-type="submit"
                    :loading="loading_submit"
                    v-show="form.documents.length > 0"
                    dusk="save-summary"
                >
                    Guardar
                </el-button>

                <!-- Botón modo masivo -->
                <el-button
                    v-if="mode === 'massive' && massive.days.length > 0 && !massive.finished"
                    type="primary"
                    @click.prevent="processMassive"
                    :loading="massive.processing"
                    :disabled="massive.processing"
                >
                    {{ massive.processing ? 'Procesando...' : 'Procesar todos los días' }}
                </el-button>

                <!-- Botón reintentar errores de envío -->
                <el-button
                    v-if="mode === 'massive' && massive.finished && massive.errors_count > 0"
                    type="danger"
                    @click.prevent="retryErrors"
                >
                    Reintentar envíos ({{ massive.errors_count }})
                </el-button>

                <!-- Botón reintentar consultas fallidas -->
                <el-button
                    v-if="mode === 'massive' && massive.finished && massive.query_errors_count > 0"
                    type="warning"
                    @click.prevent="retryQueries"
                >
                    Reintentar consultas ({{ massive.query_errors_count }})
                </el-button>
            </div>
        </form>
    </el-dialog>
</template>
<script>

    export default {
        props: ['showDialog'],
        data () {
            return {
                mode: 'individual',
                loading_submit: false,
                loading_search: false,
                titleDialog: null,
                resource: 'summaries',
                errors: {},
                form: {},
                summary_status_types: [],
                show_summary_status_type: false,
                columns: {
                    total_exportation: {
                        title: 'T.Exportación',
                        visible: false
                    },
                    total_free: {
                        title: 'T.Gratuito',
                        visible: false
                    },
                    total_unaffected: {
                        title: 'T.Inafecto',
                        visible: false
                    },
                    total_exonerated: {
                        title: 'T.Exonerado',
                        visible: false
                    },
                    total_charge: {
                        title: 'T.Cargos',
                        visible: false
                    },
                },
                massive: {
                    date_start: null,
                    date_end: null,
                    days: [],
                    total_days: 0,
                    total_documents: 0,
                    loading_search: false,
                    processing: false,
                    processed_count: 0,
                    current_date: null,
                    progress_percentage: 0,
                    progress_status: null,
                    finished: false,
                    success_count: 0,
                    errors_count: 0,
                    skipped_count: 0,
                    accepted_count: 0,
                    rejected_count: 0,
                    query_errors_count: 0
                }
            }
        },
        created() {
            this.getTables()
            this.initForm()
        },
        methods: {
            async getTables(){

                await this.$http.get(`/${this.resource}/tables`)
                    .then(response => {
                        this.summary_status_types = response.data.summary_status_types
                        this.show_summary_status_type = response.data.show_summary_status_type
                    })

            },
            initForm() {
                this.loading_submit = false,
                this.loading_search = false,
                this.errors = {}
                this.form = {
                    id: null,
                    summary_status_type_id: '1',
                    date_of_issue: null,
                    date_of_reference: moment().format('YYYY-MM-DD'),
                    documents: [],
                }
                this.initMassive()
            },
            initMassive() {
                this.massive = {
                    date_start: moment().subtract(7, 'days').format('YYYY-MM-DD'),
                    date_end: moment().format('YYYY-MM-DD'),
                    days: [],
                    total_days: 0,
                    total_documents: 0,
                    loading_search: false,
                    processing: false,
                    processed_count: 0,
                    current_date: null,
                    progress_percentage: 0,
                    progress_status: null,
                    finished: false,
                    success_count: 0,
                    errors_count: 0,
                    skipped_count: 0,
                    accepted_count: 0,
                    rejected_count: 0,
                    query_errors_count: 0
                }
            },
            changeMode() {
                if (this.mode === 'massive') {
                    this.initMassive()
                } else {
                    this.form.documents = []
                }
            },
            create() {
                this.titleDialog = 'Registrar Resumen'
                this.changeDateOfReference()
            },
            clickSearchDocuments() {
                this.loading_search = true
                this.$http.post(`/${this.resource}/documents`, {
                    'date_of_reference': this.form.date_of_reference
                })
                    .then(response => {

                        if(response.data.success){

                            this.form.documents = response.data.data

                        }else{

                            this.$message.error(response.data.message)

                        }

                    })
                    .catch(error => {
                        this.$message.error(error.response.data.message)
                    })
                    .then(() => {
                        this.loading_search = false
                    })
            },
            changeDateOfReference() {
                this.form.documents = []
            },
            clickRemoveDocument(index) {
                this.form.documents.splice(index, 1)
            },
            submit() {
                this.loading_submit = true
                this.$http.post(`${this.resource}`, this.form)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                            this.$eventHub.$emit('reloadData')
                            this.close()
                        } else {
                            this.$message.error(response.data.message)
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.errors = error.response.data
                        } else {
                            this.$message.error(error.response.data.message)
                        }
                    })
                    .then(() => {
                        this.loading_submit = false
                    })
            },
            close() {
                this.$emit('update:showDialog', false)
                this.initForm()
                this.mode = 'individual'
            },

            // Métodos para modo masivo
            searchDaysWithDocuments() {
                if (!this.massive.date_start || !this.massive.date_end) {
                    this.$message.warning('Seleccione fecha inicio y fecha fin')
                    return
                }

                this.massive.loading_search = true
                this.massive.days = []
                this.massive.finished = false

                this.$http.post(`/${this.resource}/days-with-documents`, {
                    date_start: this.massive.date_start,
                    date_end: this.massive.date_end
                })
                .then(response => {
                    if (response.data.success) {
                        this.massive.days = response.data.data.map(day => ({
                            ...day,
                            status: 'pending',
                            message: null,
                            summary_id: null,
                            identifier: null
                        }))
                        this.massive.total_days = response.data.total_days
                        this.massive.total_documents = response.data.total_documents

                        if (this.massive.days.length === 0) {
                            this.$message.info('No se encontraron documentos pendientes en el rango seleccionado')
                        }
                    } else {
                        this.$message.error(response.data.message || 'Error al buscar días')
                    }
                })
                .catch(error => {
                    let errorMsg = 'Error al buscar días'
                    if (error.response && error.response.data && error.response.data.message) {
                        errorMsg = error.response.data.message
                    }
                    this.$message.error(errorMsg)
                })
                .finally(() => {
                    this.massive.loading_search = false
                })
            },

            async processMassive() {
                this.massive.processing = true
                this.massive.finished = false
                this.massive.processed_count = 0
                this.massive.success_count = 0
                this.massive.errors_count = 0
                this.massive.skipped_count = 0
                this.massive.accepted_count = 0
                this.massive.rejected_count = 0
                this.massive.query_errors_count = 0
                this.massive.progress_percentage = 0

                const daysToProcess = this.massive.days.filter(d =>
                    d.status === 'pending' || d.status === 'error' || d.status === 'query_error'
                )

                for (let i = 0; i < daysToProcess.length; i++) {
                    const day = daysToProcess[i]
                    const dayIndex = this.massive.days.findIndex(d => d.date_of_issue === day.date_of_issue)

                    this.massive.current_date = day.date_of_issue
                    this.massive.days[dayIndex].status = 'processing'
                    this.massive.days[dayIndex].message = 'Enviando...'

                    try {
                        const response = await this.$http.post(`/${this.resource}/store-massive`, {
                            date_of_reference: day.date_of_issue,
                            summary_status_type_id: this.form.summary_status_type_id
                        })

                        if (response.data.success) {
                            this.massive.days[dayIndex].status = 'sent'
                            this.massive.days[dayIndex].message = 'Enviado, consultando estado...'
                            this.massive.days[dayIndex].summary_id = response.data.summary_id
                            this.massive.days[dayIndex].identifier = response.data.identifier
                            this.massive.success_count++

                            // Esperar antes de consultar (SUNAT necesita tiempo para procesar)
                            await this.sleep(3000)

                            // Consultar estado del resumen
                            await this.queryDayStatus(dayIndex)
                        } else {
                            if (response.data.message && response.data.message.includes('No hay documentos')) {
                                this.massive.days[dayIndex].status = 'skipped'
                                this.massive.days[dayIndex].message = 'Sin documentos pendientes'
                                this.massive.skipped_count++
                            } else {
                                this.massive.days[dayIndex].status = 'error'
                                this.massive.days[dayIndex].message = response.data.message || 'Error desconocido'
                                this.massive.errors_count++
                            }
                        }
                    } catch (error) {
                        this.massive.days[dayIndex].status = 'error'
                        let errorMsg = 'Error de conexión'
                        if (error.response && error.response.data && error.response.data.message) {
                            errorMsg = error.response.data.message
                        }
                        this.massive.days[dayIndex].message = errorMsg
                        this.massive.errors_count++
                    }

                    this.massive.processed_count++
                    this.massive.progress_percentage = Math.round((this.massive.processed_count / daysToProcess.length) * 100)

                    // Pequeña pausa entre envíos para no sobrecargar
                    if (i < daysToProcess.length - 1) {
                        await this.sleep(1000)
                    }
                }

                this.massive.processing = false
                this.massive.finished = true
                this.massive.progress_status = (this.massive.errors_count === 0 && this.massive.rejected_count === 0) ? 'success' : 'warning'
                this.$eventHub.$emit('reloadData')

                const totalErrors = this.massive.errors_count + this.massive.rejected_count + this.massive.query_errors_count
                if (totalErrors === 0) {
                    this.$message.success('Todos los resúmenes fueron aceptados correctamente')
                } else {
                    this.$message.warning(`Proceso completado con ${totalErrors} error(es)`)
                }
            },

            async queryDayStatus(dayIndex) {
                const day = this.massive.days[dayIndex]
                if (!day.summary_id) return

                this.massive.days[dayIndex].status = 'querying'
                this.massive.days[dayIndex].message = 'Consultando estado en SUNAT...'

                try {
                    const response = await this.$http.get(`/${this.resource}/status/${day.summary_id}`)

                    if (response.data.success) {
                        this.massive.days[dayIndex].status = 'accepted'
                        this.massive.days[dayIndex].message = response.data.message || 'Aceptado por SUNAT'
                        this.massive.accepted_count++
                        // Restar del contador de enviados ya que ahora está aceptado
                        this.massive.success_count--
                    } else {
                        this.massive.days[dayIndex].status = 'rejected'
                        this.massive.days[dayIndex].message = response.data.message || 'Rechazado por SUNAT'
                        this.massive.rejected_count++
                        // Restar del contador de enviados
                        this.massive.success_count--
                    }
                } catch (error) {
                    this.massive.days[dayIndex].status = 'query_error'
                    let errorMsg = 'Error al consultar estado'
                    if (error.response && error.response.data && error.response.data.message) {
                        errorMsg = error.response.data.message
                    }
                    this.massive.days[dayIndex].message = errorMsg
                    this.massive.query_errors_count++
                    // Restar del contador de enviados
                    this.massive.success_count--
                }
            },

            retryErrors() {
                // Marcar los errores de envío como pendientes para reintentar
                this.massive.days.forEach(day => {
                    if (day.status === 'error') {
                        day.status = 'pending'
                        day.message = null
                        day.summary_id = null
                        day.identifier = null
                    }
                })
                this.massive.finished = false
                this.processMassive()
            },

            async retryQueries() {
                // Reintentar solo las consultas fallidas
                this.massive.processing = true
                this.massive.finished = false

                const daysToQuery = this.massive.days
                    .map((day, index) => ({ day, index }))
                    .filter(({ day }) => day.status === 'query_error' && day.summary_id)

                for (const { day, index } of daysToQuery) {
                    this.massive.current_date = day.date_of_issue

                    // Resetear contador antes de reintentar
                    this.massive.query_errors_count--

                    await this.queryDayStatus(index)
                    await this.sleep(2000)
                }

                this.massive.processing = false
                this.massive.finished = true
                this.$eventHub.$emit('reloadData')
            },

            sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms))
            }
        }
    }
</script>

<style>
    .table thead th {
        white-space: nowrap;
    }
    .white-space {
        white-space: nowrap;
    }
    .el-button--primary:focus {
        outline: 5px auto #66b1ff;
    }
    .table-sm td, .table-sm th {
        padding: 0.3rem 0.5rem;
    }
</style>
