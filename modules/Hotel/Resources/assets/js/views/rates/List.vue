<template>
  <div>
    <div class="page-header pr-0">
      <h2>
        <a href="/hotels/rates">
          <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-building-skyscraper"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M5 21v-14l8 -4v18" /><path d="M19 21v-10l-6 -4" /><path d="M9 9l0 .01" /><path d="M9 12l0 .01" /><path d="M9 15l0 .01" /><path d="M9 18l0 .01" /></svg>
        </a>
      </h2>
      <ol class="breadcrumbs">
        <li class="active"><span>REGISTRO DE TARIFAS</span></li>
      </ol>
      <div class="right-wrapper pull-right">
        <div class="btn-group flex-wrap">
          <button
            type="button"
            class="btn btn-custom btn-sm mt-2 mr-2"
            @click="onCreateRate"
          >
            <i class="fa fa-plus-circle"></i> Nuevo
          </button>
        </div>
      </div>
    </div>
    <div class="card tab-content-default row-new mb-0">
      <!-- <div class="card-header bg-info">
        <h3 class="my-0">Listado de tarifas</h3>
      </div> -->
      <div class="card-body">
        <div class="row">
          <div v-if="userType==='admin'" class="col-12 col-md-3 mb-3">
            <div class="form-group">
              <select
                class="form-control form-control-default"
                v-model="filter.establishment_id"
                @change="onFilter"
              >
                <option value="">Todos</option>
                <option v-for="item in establishments" :key="item.id" :value="item.id">
                  {{ item.description }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <!-- <th class="text-center">#</th> -->
                <th>Nombre</th>
                <th class="text-center">Visible</th>
                <th>Sucursal</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <!-- <td class="text-center">{{ item.id }}</td> -->
                <td>{{ item.description }}</td>
                <td class="text-center">
                  <span v-if="item.active">Si</span>
                  <span v-else>No</span>
                </td>
                <td>{{ item.establishment_name }}</td>
                <td class="text-center">
                  <el-button type="success" @click="onEdit(item)">
                    <i class="fa fa-edit"></i>
                  </el-button>
                  <el-button type="danger" @click="onDelete(item)">
                    <i class="fa fa-trash"></i>
                  </el-button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="text-center">
          <el-pagination
            :page-size="paginator.per_page"
            :page-count="paginator.size"
            layout="prev, pager, next"
            :total="paginator.total"
            :current-page.sync="filter.page"
            @current-change="onFilter"
          >
          </el-pagination>
        </div>
      </div>
    </div>
    <ModalAddEdit
      :visible.sync="openModalAddEdit"
      @onAddItem="onAddItem"
      @onUpdateItem="onUpdateItem"
      :rate="rate"
      :establishments="establishments"
      :user-type="userType"
      :establishment-id="establishmentId"
    ></ModalAddEdit>
  </div>
</template>

<script>
import ModalAddEdit from "./AddEdit";

export default {
  props: {
    rates: {
      type: Object,
      required: true,
    },
    establishments: {
      type: Array,
      required: true,
    },
    userType: {
      type: String,
      required: true,
    },
    establishmentId: {
      type: Number,
      required: true,
    },
  },
  components: {
    ModalAddEdit,
  },
  data() {
    return {
      items: [],
      rate: null,
      openModalAddEdit: false,
      loading: false,
      filter: {
        establishment_id: '',
        page: 1,
      },
      paginator: {
        size: 1,
        total: 1,
        per_page: 1
      },
    };
  },
  mounted() {
    this.items = this.rates.data;
    this.paginator.size = this.rates.last_page;
    this.paginator.total = this.rates.total;
    this.paginator.per_page = this.rates.per_page;
    this.filter.establishment_id = this.establishmentId;
  },
  methods: {
    onDelete(item) {
      this.$confirm(`¿estás seguro de eliminar al elemento ${item.description}?`, 'Atención', {
          confirmButtonText: 'Si, continuar',
          cancelButtonText: 'No, cerrar',
          type: 'warning'
        }).then(() => {
          this.$http.delete(`/hotels/rates/${item.id}/delete`).then(response => {
            this.$message({
              type: 'success',
              message: response.data.message
            });
            this.items = this.items.filter(i => i.id !== item.id);
          }).catch(error => {
            this.axiosError(error)
          });
        }).catch();
    },
    onEdit(item) {
      this.rate = { ...item };
      this.openModalAddEdit = true;
    },
    onUpdateItem(data) {
      this.onFilter()
    },
    onAddItem(data) {
      this.onFilter()
    },
    onCreateRate() {
      this.rate = null;
      this.openModalAddEdit = true;
    },
    onFilter() {
      this.loading = true;
      const params = this.filter;
      this.$http
        .get("/hotels/rates", { params })
        .then((response) => {
          this.items = response.data.rates.data;
          this.paginator.size = response.data.rates.last_page;
          this.paginator.total = response.data.rates.total;
          this.paginator.per_page = response.data.rates.per_page;
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
};
</script>
