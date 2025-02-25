<template>
  <div class="row top">
    <div class="col" v-if="company.certificate_due">
      <div class="card card-dashboard">
        <div
          class="card-body border-success"
          :class="{
            'border-danger': isDueWarning
          }"
        >
        <span class="text-success font-weight-bold" :class="{
            'text-danger': isDueWarning
          }">{{ company.certificate_due }}</span>
          <div class="card-title">Venci. del Certificado</div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card card-dashboard">
        <i class="fas fa-folder-open"></i>
        <div class="card-body">
          <el-tooltip
            class="item"
            effect="dark"
            :content="total_cpe"
            placement="top-start"
          >
            <h3 class="font-weight-bold m-0 mb-2">{{ total_cpe | formatNumber }}</h3>
          </el-tooltip>
          <small>CPE Emitidos</small>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card card-dashboard">
        <i class="fas fa-copy"></i>
        <div class="card-body">
          <el-tooltip
            class="item"
            effect="dark"
            :content="document_total_global"
            placement="top-start"
          >
            <h3 class="font-weight-bold m-0 mb-2">{{ document_total_global | formatNumber }}</h3>
          </el-tooltip>
          <small>Total comprobantes</small>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card card-dashboard">
        <i class="fas fa-file-alt"></i>
        <div class="card-body">
          <el-tooltip
            class="item"
            effect="dark"
            :content="sale_note_total_global"
            placement="top-start"
          >
            <h3 class="font-weight-bold m-0 mb-2">{{ sale_note_total_global | formatNumber }}</h3>
          </el-tooltip>
          <small>Total notas de ventas</small>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card card-dashboard">
        <i class="fas fa-chart-bar"></i>
        <div class="card-body">
          <el-tooltip
            class="item"
            effect="dark"
            :content="total"
            placement="top-start"
          >
            <h3 class="font-weight-bold m-0 mb-2">{{ total | formatNumber }}</h3>
          </el-tooltip>
          <small>Ventas totales</small>
        </div>
      </div>
    </div>
    <div class="col" v-if="utilities.totals">
      <div class="card card-dashboard">
        <i class="fas fa-money-bill"></i>
        <div class="card-body">
          <el-tooltip
            class="item"
            effect="dark"
            :content="utilities.totals.utility"
            placement="top-start"
          >
            <h3 class="font-weight-bold m-0 mb-2">{{ utilities.totals.utility | formatNumber }}</h3>
          </el-tooltip>
          <small>Utilidad neta</small>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";

export default {
  props: ["company", 'utilities'],
  data() {
    return {
      document_total_global: 0,
      total_cpe: 0,
      sale_note_total_global: 0,
      total: 0,
    };
  },
  mounted() {
    this.onFetchData();
  },
  computed: {
    isDueWarning() {
      if (this.company.certificate_due) {
        const dueDate = moment(this.company.certificate_due);

        const now = moment();
        const diffInDays = dueDate.diff(now, 'days')
        return diffInDays <= 15;
      }
      return false;
    },
  },
  methods: {
    onFetchData() {
      this.$http.get("/dashboard/global-data").then((response) => {
        const data = response.data;
        this.document_total_global = data.document_total_global;
        this.total_cpe = data.total_cpe;
        this.sale_note_total_global = data.sale_note_total_global;
        this.total =
          parseFloat(this.document_total_global) +
          parseFloat(this.sale_note_total_global);
      });
    },
  },
  filters: {
    formatNumber(value) {
      if (value >= 1000000) {
        let deciamlValue = (value / 1000000).toString().split(".");
        let entero = deciamlValue[0];
        let decimal = deciamlValue[1].slice(0,1)
        return `${entero}.${decimal}M`;
      } else if (value >= 1000) {
        let deciamlValue = (value / 1000).toString().split(".");
        let entero = deciamlValue[0];
        let decimal = deciamlValue[1].slice(0,1)
        return `${entero}.${decimal}K`;
      }
      return value;
    },
  },
};
</script>
<style>
.card-green {
  background-color: green;
  color: white;
}
.is-due-warning {
  background-color: red;
}
.card-green .card-title {
  color: white;
}
.row.top .card.card-dashboard i.fas {
    position: absolute;
    right: 10px;
    opacity: 0.075;
    overflow: hidden;
    z-index: 0;
    font-size: 24px;
    top: 10px;
}
</style>
