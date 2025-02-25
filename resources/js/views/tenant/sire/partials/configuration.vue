<template>
  <div class="card mt-3">
    <div class="card-header bg-info">
      <h3 class="my-0">SIRE</h3>
    </div>
    <div class="card-body">
      <form autocomplete="off"
        @submit.prevent="submit">
        <div class="form-body">
          <div class="row">
            <div
              class="col-md-6">
              <div :class="{'has-danger': errors.sire_client_id}"
                class="form-group">
                <label class="control-label">
                  Client ID
                </label>
                <el-input v-model="form.sire_client_id"></el-input>
                <small
                  v-if="errors.sire_client_id"
                  class="invalid-feedback"
                  v-text="errors.sire_client_id[0]"
                ></small>
              </div>
            </div>
            <div
              class="col-md-6">
              <div :class="{'has-danger': errors.sire_client_secret}"
                class="form-group">
                <label class="control-label">
                  Client Secret (clave)
                </label>
                <el-input v-model="form.sire_client_secret"></el-input>
                <small
                  v-if="errors.sire_client_secret"
                  class="invalid-feedback"
                  v-text="errors.sire_client_secret[0]"
                ></small>
              </div>
            </div>
            <div
              class="col-md-6">
              <div :class="{'has-danger': errors.sire_username}"
                class="form-group">
                <label class="control-label">
                  Usuario
                </label>
                <el-input v-model="form.sire_username"></el-input>
                <small
                  v-if="errors.sire_username"
                  class="invalid-feedback"
                  v-text="errors.sire_username[0]"
                ></small>
              </div>
            </div>
            <div
              class="col-md-6">
              <div :class="{'has-danger': errors.sire_password}"
                class="form-group">
                <label class="control-label">
                  Contraseña
                </label>
                <el-input v-model="form.sire_password"></el-input>
                <small
                  v-if="errors.sire_password"
                  class="invalid-feedback"
                  v-text="errors.sire_password[0]"
                ></small>
              </div>
            </div>
          </div>
        </div>
        <div class="form-actions text-end pt-2">
          <el-button :loading="loading_submit"
            native-type="submit"
            type="primary">Guardar
          </el-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      loading_submit: false,
      resource: 'sire',
      errors: {},
      form: {},
    };
  },
  created() {
    this.getConfig()
  },
  methods: {
    getConfig() {
      this.$http
        .get(`/${this.resource}/configuration`)
        .then(response => {
          this.form = response.data.data
        })
        .catch(error => {
          if (error.response.status === 422) {
            this.errors = error.response.data;
          } else {
            console.log(error);
          }
        });
    },
    submit() {
      this.loading_submit = true;
      this.$http
        .post(`/${this.resource}/configuration/update`, this.form)
        .then(response => {
          if (response.data.success) {
            this.$message.success(response.data.message);
          } else {
            this.$message.error(response.data.message);
          }
        })
        .catch(error => {
          if (error.response.status === 422) {
            this.errors = error.response.data;
          } else {
            console.log(error);
          }
          this.loading_submit = false;
        })
        .then(() => {
          this.loading_submit = false;
        });
    }
  }
}
</script>