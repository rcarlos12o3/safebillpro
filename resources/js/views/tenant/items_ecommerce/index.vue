<template>
  <div class="items_ecommerce">
    <div class="page-header pr-0">
      <h2>
        <a href="/items_ecommerce">
          <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-cart"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 17h-11v-14h-2" /><path d="M6 5l14 1l-1 7h-13" /></svg>
        </a>
      </h2>
      <ol class="breadcrumbs">
        <li class="active">
          <span>Productos Tienda Virtual</span>
        </li>
      </ol>
      <div class="right-wrapper pull-right">
        <template>
          <!-- v-if="typeUser === 'admin'" -->
          <!-- <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickImport()"><i class="fa fa-upload"></i> Importar</button>-->
          <button
            type="button"
            class="btn btn-custom btn-sm mt-2 mr-2"
            @click.prevent="clickCreate()"
          >
            <i class="fa fa-plus-circle"></i> Nuevo
          </button>
        </template>
      </div>
    </div>
    <div class="card tab-content-default row-new mb-0">
      <!-- <div class="card-header bg-info">
        <h3 class="my-0">Listado de productos Tienda Virtual</h3>
      </div> -->
      <div class="card-body">
        <data-table 
          :resource="resource"
          :ecommerce="ecommerce"
          >
          <tr slot="heading" width="100%">
            <!-- <th>#</th> -->
            <th>Cód. Interno</th>
            <th>Unidad</th>
            <th class="text-center">Imagen</th>
            <th>Nombre</th>
            <th class="text-center">P.Unitario (Venta)</th>
            <th class="text-center">Stock General</th>
            <th class="text-center">Tags</th>

            <th class="text-center">Visible en Tienda</th>
            <th class="text-right">Acciones</th>
          </tr>
          <tr></tr>
          <tr slot-scope="{ index, row }">
            <!-- <td>{{ index }}</td> -->
            <td>{{ row.internal_id }}</td>
            <td>{{ row.unit_type_id }}</td>
            <td class="text-center">
              <a @click="viewImages(row)" href="#">
                <img :src="row.image_url_small" tyle="object-fit: contain;"  alt width="32px" height="32px" />
              </a>
              <!--<img :src="row.image_url_medium"  width="40" height="40" class="img-thumbail img-custom" /> -->
            </td>
            <td>{{ row.description }}</td>
            <td class="text-center">{{ row.sale_unit_price }}</td>
            <td class="text-center">{{ stock(row.warehouses) }}</td>
            <td>
              <el-tag style="margin:1px" v-for="tag in row.tags" :key="tag.id">{{tag.tag.name}}</el-tag>
            </td>
            <td class="text-center">
              <el-checkbox
                size="medium"
                @change="visibleStore($event, row.id)"
                 v-model="row.apply_store"
              ></el-checkbox>
            </td>
            <td class="text-right">
              <template>
                <!-- v-if="typeUser === 'admin'" -->
                <button
                  type="button"
                  class="btn waves-effect waves-light btn-xs btn-info"
                  @click.prevent="clickCreate(row.id)"
                >Editar</button>
                <button
                  type="button"
                  class="btn waves-effect waves-light btn-xs btn-danger"
                  @click.prevent="clickDelete(row.id)"
                >Eliminar</button>
              </template>
            </td>
          </tr>
        </data-table>
      </div>

      <items-form :showDialog.sync="showDialog" :recordId="recordId"></items-form>

      <!-- <items-import :showDialog.sync="showImportDialog"></items-import> -->

      <warehouses-detail :showDialog.sync="showWarehousesDetail" :warehouses="warehousesDetail"></warehouses-detail>

      <!-- <images-record :showDialog.sync="showImageDetail" :recordImages="recordImages"></images-record> -->

      <el-dialog
        :visible.sync="showImageDetail"
        title="Imagenes de Producto"
        width="50%"
        append-to-body
        top="7vh"
      >
        <div class="row d-flex align-items-end justify-content-end">
          <div class="col-md-3">
            <h4>Thumbs</h4>
            <img class="img-thumbnail" :src="recordImages.image_url_small" alt width="128" />
          </div>
          <div class="col-md-4">
            <h4>Para productos de Venta</h4>
            <img class="img-thumbnail" :src="recordImages.image_url_medium" alt width="256" />
          </div>
          <div class="col-md-4">
            <h4>Para Tienda</h4>
            <img class="img-thumbnail" :src="recordImages.image_url" alt width="512" />
          </div>
        </div>
        <div class="row text-right pt-2">
          <div class="col align-self-end">
            <el-button type="primary" @click="showImageDetail = false">Cerrar</el-button>
          </div>
        </div>
      </el-dialog>
    </div>
  </div>
</template>
<style>
@media only screen and (max-width: 485px){
  .filter-container{
    margin-top: 0px;
    & .btn-filter-content, .btn-container-mobile{
      display: flex;
      align-items: center;
      justify-content: start;
    }
  }
}
</style>
<script>
import ItemsForm from "./form.vue";
import WarehousesDetail from "./partials/warehouses.vue";
// import ItemsImport from './import.vue'
import DataTable from "../../../components/DataTable.vue";
import { deletable } from "../../../mixins/deletable";

export default {
  props: [], //'typeUser'
  mixins: [deletable],
  components: { ItemsForm, DataTable, WarehousesDetail }, //ItemsImport
  data() {
    return {
      showDialog: false,
      showImportDialog: false,
      showWarehousesDetail: false,
      showImageDetail: false,
      resource: "items",
      recordId: null,
      warehousesDetail: [],
      recordImages: {
        image_url: "",
        image_url_medium: "",
        image_url_small: ""
      },
      ecommerce:true,
    };
  },
  created() {},
  methods: {
    viewImages(row) {
      this.recordImages.image_url = row.image_url;
      this.recordImages.image_url_medium = row.image_url_medium;
      this.recordImages.image_url_small = row.image_url_small;
      this.showImageDetail = true;
    },
    visibleStore(apply_store, id) {
      this.$http
        .post(`/${this.resource}/visible_store`, { id, apply_store })
        .then(response => {
          if (response.data.success) {
            if (apply_store) {
              this.$message.success(response.data.message);
            } else {
              this.$message.warning(response.data.message);
            }
            this.$eventHub.$emit("reloadData")

          } else {
            this.$message.error(response.data.message);
            this.$eventHub.$emit("reloadData")

          }
        })
        .catch(error => {})
        .then(() => {});
    },
    clickWarehouseDetail(warehouses) {
      this.warehousesDetail = warehouses;
      this.showWarehousesDetail = true;
    },
    clickCreate(recordId = null) {
      this.recordId = recordId;
      this.showDialog = true;
    },
    clickImport() {
      this.showImportDialog = true;
    },
    clickDelete(id) {
      this.destroy(`/${this.resource}/${id}`).then(() =>
        this.$eventHub.$emit("reloadData")
      );
    },
    stock (items) {
      let stock = 0
      items.forEach((item) => {
        stock += parseInt(item.stock)
      })
      return stock
    }
  }
}
</script>