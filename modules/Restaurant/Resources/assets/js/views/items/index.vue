<template>
  <div style="width: 100%;" class="mr-0">
    <div class="page-header pr-0">
      <h2>
        <a href="/restaurant/list/items">
          <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-tools-kitchen-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19 3v12h-5c-.023 -3.681 .184 -7.406 5 -12zm0 12v6h-1v-3m-10 -14v17m-3 -17v3a3 3 0 1 0 6 0v-3" /></svg>
        </a>
      </h2>
      <ol class="breadcrumbs">
        <li class="active">
          <span>Productos</span>
        </li>
      </ol>
      <div class="right-wrapper pull-right">
        <template>
          <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickImport()"><i class="fa fa-upload"></i> Importar</button>
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
        <h3 class="my-0">Listado de productos Restaurante</h3>
      </div> -->
      <div class="card-body">
        <data-table 
          :resource="'items'"
          :restaurant="restaurant"
          >
          <tr slot="heading" width="100%">
            <!-- <th>#</th> -->
            <th>Cód. Interno</th>
            <th>Unidad</th>
            <th class="text-center">Imagen</th>
            <th>Nombre</th>
            <th class="text-right">P.Unitario (Venta)</th>
            <th class="text-right">Stock General</th>
            <th class="text-center">Categoría</th>
            <th class="text-center">Visible en Restaurant</th>
            <th class="text-right">Acciones</th>
          </tr>
          <tr></tr>
          <tr slot-scope="{ index, row }">
            <!-- <td>{{ index }}</td> -->
            <td>{{ row.internal_id }}</td>
            <td>{{ row.unit_type_id }}</td>
            <td class="text-center">
              <a @click="viewImages(row)" href="#">
                <img :src="row.image_url_small" style="object-fit: contain;" alt width="32px" height="32px" />
              </a>
            </td>
            <td>{{ row.description }}</td>
            <td class="text-right">{{ row.sale_unit_price }}</td>
            <td class="text-right">{{ stock(row.warehouses) }}</td>
            <td class="text-center">
              {{row.category_description}}
            </td>
            <td class="text-center">
              <el-checkbox
                size="medium"
                @change="visibleRestaurant($event, row.id)"
                v-model="row.apply_restaurant"
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

      <items-import :showDialog.sync="showImportDialog"></items-import>

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
import ItemsForm from "../../../../../../../resources/js/views/tenant/items_ecommerce/form.vue";
import WarehousesDetail from "../../../../../../../resources/js/views/tenant/items_ecommerce/partials/warehouses.vue";
import ItemsImport from './import.vue'
import DataTable from "../../../../../../../resources/js/components/DataTable.vue";
import { deletable } from "../../../../../../../resources/js/mixins/deletable";

export default {
  props: [],
  mixins: [deletable],
  components: { ItemsForm, DataTable, WarehousesDetail, ItemsImport },
  data() {
    return {
      showDialog: false,
      showImportDialog: false,
      showWarehousesDetail: false,
      showImageDetail: false,
      resource: "restaurant",
      recordId: null,
      warehousesDetail: [],
      recordImages: {
        image_url: "",
        image_url_medium: "",
        image_url_small: ""
      },
      restaurant:true,
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
    visibleRestaurant(apply_restaurant, id) {
      this.$http
        .post(`/${this.resource}/items/visible`, { id, apply_restaurant })
        .then(response => {
          if (response.data.success) {
            if (apply_restaurant) {
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
      this.destroy(`/items/${id}`).then(() =>
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
