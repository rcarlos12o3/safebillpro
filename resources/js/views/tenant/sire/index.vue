<template>
  <div>
    <div class="page-header pr-0">
      <h2><a href="#">
        <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-chart-histogram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 3v18h18" /><path d="M20 18v3" /><path d="M16 16v5" /><path d="M12 13v8" /><path d="M8 16v5" /><path d="M3 11c6 0 5 -5 9 -5s3 5 9 5" /></svg>
      </a></h2>
      <ol class="breadcrumbs">
          <li class="active"><span>SIRE</span></li>
      </ol>
    </div>
    <div class="card tab-content-default row-new mt-0">
      <div class="card-body border-bottom">
        <el-form :inline="true" :model="form" class="demo-form-inline mb-0">
          <el-form-item label="Año" class="label-mt-0">
            <el-select v-model="form.year" @change="setPeriods()">
              <el-option v-for="(year, index) in period_year" :key="index" :label="year.title" :value="year.id" ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="Periodo" class="label-mt-0">
            <el-select v-model="form.period">
              <el-option v-for="(period, index) in period_month" :key="index" :label="period.perTributario+' '+period.desEstado" :value="period.perTributario"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item class="label-mt-0">
            <el-button type="primary" @click="sendPeriod" :disabled="form.period == null">Enviar</el-button>
          </el-form-item>
        </el-form>
      </div>
      <div class="card-body border-bottom" v-if="code_ticket !== null">
        <table class="table table-unstyled mb-0">
          <tr>
            <th>Ticket Actual</th>
            <th>Estado</th>
            <th class="ps-3">Página</th>
            <th></th>
          </tr>
          <tr>
            <td>{{ code_ticket }}</td>
            <td>{{ states[status_ticket] }}</td>
            <td width="10%">
              <el-input v-model="page" label="Pagina" size="mini" type="number"></el-input>
            </td>
            <td class="text-right">
              <el-button
                type="primary"
                @click="queryTicket"
                :disabled="code_ticket == null"
                class="bg-primary"
                size="mini"
                :loading="loading_query">
                Consultar
              </el-button>
            </td>
          </tr>
        </table>
      </div>
      <div class="card-body">
        <el-table
          :data="documents"
          style="width: 100%"
          stripe
          :row-class-name="diffRows">
          <el-table-column
            prop="service"
            label="Servicio">
          </el-table-column>
          <el-table-column
            prop="date"
            label="F. Emisión">
          </el-table-column>
          <el-table-column
            prop="document_type"
            label="Tipo Documento">
          </el-table-column>
          <el-table-column
            prop="serie"
            label="Serie">
          </el-table-column>
          <el-table-column
            prop="number"
            label="Número">
          </el-table-column>
          <el-table-column
            prop="total"
            label="Total">
          </el-table-column>
        </el-table>
      </div>
      <div class="card-footer">
        <div class="row d-flex align-items-end">
          <div class="col-6">
            <el-button
              type="success"
              class=""
              v-show="type == 'sale'"
              :disabled="documents.length <= 0"
              @click="sendAccept"
              :loading="loading_accept">
              Aceptar Propuesta
            </el-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {setSireStorage, updateFieldToCompany, getStorageSireCompany} from './utils/sire_storage.js'

export default {
  components: {},
  data() {
    return {
      resource: 'sire',
      type: 'sale',
      period_year: [],
      period_month: [],
      form: {},
      code_ticket: null,
      states: {
        '00': 'No solicitado',
        '01': 'Cargado (solicitado)',
        '02': 'Validando Archivo (en proceso)',
        '03': 'Procesado con Errores',
        '04': 'Procesado sin errores (concluido)',
        '05': 'En proceso',
        '06': 'Terminado'
      },
      status_ticket: '01',
      period_current: null,
      page: 1,
      filename: null,
      loading_query: false,
      loading_data: false,
      documents: [],
      loading_accept: false,
    }
  },
  created() {
    this.setType()
    this.setStorageDefault()
    this.getPeriods()
    this.getData()
  },
  methods: {
    setType() {
      this.type = window.location.href.split('/').pop()
    },
    setStorageDefault() {
      setSireStorage()
    },
    getPeriods() {
      this.$http.get(`/${this.resource}/${this.type}/tables`)
        .then((response)=>{
          this.setPeriodsData(response.data.data)
        })
        .catch((error)=>{
          console.error(error)
        })
    },
    setPeriodsData(data) {
      this.period_year = data.map(period => {
        return {
          id: period.numEjercicio,
          title: period.numEjercicio+' - '+period.desEstado,
          periods: period.lisPeriodos
        }
      })
    },
    setPeriods() {
      let record = this.period_year.find(item => item.id == this.form.year)
      this.period_month = record.periods
    },
    sendPeriod() {
      this.period_current = this.form.period
      this.$http.get(`/${this.resource}/${this.type}/${this.form.period}/ticket`)
        .then((response)=>{
          this.code_ticket = response.data.data.numTicket
          this.status_ticket = '00'
          updateFieldToCompany(this.type, 'period', this.form.period)
          updateFieldToCompany(this.type, 'ticket', this.code_ticket)
          updateFieldToCompany(this.type, 'status', this.status_ticket)
        })
        .catch((error)=>{
          console.error(error)
        })
    },
    getData() {
      let company = getStorageSireCompany()
      this.code_ticket = company[this.type]['ticket']
      this.status_ticket = company[this.type]['status']
      if(this.code_ticket) {
        this.period_current = company[this.type]['period']
      }
    },
    queryTicket() {
      this.documents = []
      this.loading_query = true
      let params = {
        period: this.period_current,
        page: this.page,
        ticket: this.code_ticket
      }
      this.$http.post(`/${this.resource}/${this.type}/query`, params)
        .then((response)=>{
          this.loading_query = false
          if(response.data.success){
            this.status_ticket = response.data.data.status_code
            this.documents = response.data.data.documents
            updateFieldToCompany(this.type, 'status', this.status_ticket)
          }
        })
        .catch((error)=>{
          this.loading_query = false
          console.error(error)
        })
    },
    diffRows({ row, rowIndex }) {
      if (rowIndex < this.documents.length - 1) {
        const currentRow = row;
        const nextRow = this.documents[rowIndex + 1];

        if (
          currentRow.number == nextRow.number &&
          currentRow.serie == nextRow.serie &&
          currentRow.total !== nextRow.total
        ) {
          return 'bg-danger';
        }
      }
      return null;
    },
    sendAccept() {
      this.loading_accept = true
      let sire_period = localStorage.getItem(this.type+'_sire_period')
      this.$http.get(`/${this.resource}/${this.type}/${sire_period}/accept`)
        .then((response)=>{
          if(response.data.success){
            // console.log(response.data)
            this.$message.success('Propuesta enviada exitosamente')
          }
          this.loading_accept = false
        })
        .catch((error)=>{
          this.loading_accept = false
          console.error(error)
        })
    }
  }
}
</script>

<style scope>
.label-mt-0, .label-mt-0 label {
  margin-top: 0px;
  margin-bottom: 0px !important;
}
.el-button--mini {
  padding: 7px 15px !important;
}
</style>