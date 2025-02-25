<template>
  <el-dialog
    :title="title"
    :visible="visible"
    @close="onClose"
    @open="onCreate"
    width="400px"
  >
    <form autocomplete="off" @submit.prevent="onSubmit">
      <div class="form-body">
        <div class="form-group">
          <label class="control-label" for="description">Nombre de la Ubicación</label>
          <el-input
            type="text"
            id="description"
            v-model="form.description"
            :class="{ 'is-invalid': errors.description }"
          />
          <div v-if="errors.description" class="invalid-feedback">
            {{ errors.description[0] }}
          </div>
        </div>
        <div v-if="userType==='admin'" class="form-group">
          <label class="control-label" for="establishment">Sucursal</label>
          <el-select
            type="text"
            id="establishment"
            v-model="form.establishment_id"
            :class="{ 'is-invalid': errors.establishment_id }"
          >
            <el-option v-for="item in establishments" :key="item.id" :value="item.id" :label="item.description">
              {{ item.description }}
            </el-option>
          </el-select>
          <div v-if="errors.establishment_id" class="invalid-feedback">
            {{ errors.establishment_id[0] }}
          </div>
        </div>
        <div class="form-group">
          <label>Mostrar ubicación</label>
          <el-switch v-model="form.active"></el-switch>
        </div>
        <div class="row text-center">
          <div class="col-6">
            <el-button
              native-type="submit"
              :disabled="loading"
              type="primary"
              class="btn-block"
              :loading="loading"
              >Guardar</el-button
            >
          </div>
          <div class="col-6">
            <el-button class="btn-block second-buton" @click="onClose">Cancelar</el-button>
          </div>
        </div>
      </div>
    </form>
  </el-dialog>
</template>

<script>
export default {
  props: {
    visible: {
      type: Boolean,
      required: true,
      default: false,
    },
    floor: {
      type: Object,
      required: false,
      default: {},
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
  data() {
    return {
      form: {},
      title: "",
      errors: {},
      loading: false,
    };
  },
  methods: {
    onUpdate() {
      this.loading = true;
      this.$http
        .put(`/hotels/floors/${this.floor.id}/update`, this.form)
        .then((response) => {
          this.$emit("onUpdateItem", response.data.data);
          this.onClose();
        })
        .finally(() => {
          this.loading = false;
          this.errors = {};
        })
        .catch((error) => {
          this.axiosError(error);
        });
    },
    onStore() {
      this.loading = true;
      this.$http
        .post("/hotels/floors/store", this.form)
        .then((response) => {
          this.$emit("onAddItem", response.data.data);
          this.onClose();
        })
        .finally(() => {
          this.loading = false;
          this.errors = {};
        })
        .catch((error) => {
          this.axiosError(error);
        });
    },
    onSubmit() {
      if (this.floor) {
        this.onUpdate();
      } else {
        this.onStore();
      }
    },
    onClose() {
      this.$emit("update:visible", false);
    },
    onCreate() {
      if (this.floor) {
        this.form = this.floor;
        this.title = "Editar ubicación";
      } else {
        this.title = "Crear ubicación";
        this.form = {
          active: true,
          establishment_id: this.establishmentId,
        };
      }
    },
  },
};
</script>
