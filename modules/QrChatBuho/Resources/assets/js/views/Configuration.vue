<template>
  <div>
    <div class="card mt-3">
      <div class="card-header bg-info">
        <h3 class="my-0">Envio de mensajes QR.Chat.pe</h3>
      </div>
      <div class="card-body">
        <form autocomplete="off" @submit.prevent="submit">
          <div class="form-body">
            <p v-show="form.qrchat_enable">Crea tu cuenta en <a href="http://qr.chat.pe" class="text-primary" target="_blank" rel="noopener noreferrer">qr.chat.pe</a>, escanea el QR de tu WhatsApp, genera los token y comienza a enviar mensajes con tus comprobantes gratis</p>
            <p v-show="!form.qrchat_enable">Mientras esté deshabilitado se utilizará el servicio de Whatsapp web (requiere una sesión abierta en su equipo)</p>
            <div class="row">
              <div class="col-12">
                <el-switch v-model="form.qrchat_enable"
                  active-text="Si"
                  inactive-text="No"></el-switch>
              </div>
              <div class="col-12" v-show="form.qrchat_enable">
                <div :class="{'has-danger': errors.qrchat_url}"
                  class="form-group">
                  <label class="control-label">
                    URL
                  </label>
                  <el-input v-model="form.qrchat_url"></el-input>
                  <small
                    v-if="errors.qrchat_url"
                    class="invalid-feedback d-block"
                    v-text="errors.qrchat_url[0]"></small>
                </div>
              </div>
              <div class="col-12" v-show="form.qrchat_enable">
                <div :class="{'has-danger': errors.qrchat_app_key}"
                  class="form-group">
                  <label class="control-label">
                    APP Key
                  </label>
                  <el-input v-model="form.qrchat_app_key"></el-input>
                  <small
                    v-if="errors.qrchat_app_key"
                    class="invalid-feedback d-block"
                    v-text="errors.qrchat_app_key[0]"></small>
                </div>
              </div>
              <!-- <div class="col-12" v-show="form.qrchat_enable">
                <div :class="{'has-danger': errors.qrchat_auth_key}"
                  class="form-group">
                  <label class="control-label">
                    AUTH KEY
                  </label>
                  <el-input v-model="form.qrchat_auth_key"></el-input>
                  <small
                    v-if="errors.qrchat_auth_key"
                    class="invalid-feedback d-block"
                    v-text="errors.qrchat_auth_key[0]"></small>
                </div>
              </div> -->
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
    <tenant-qr-api :enabledChatBuhoProp="form.qrchat_enable" :changeQREnabled="disabledConfigurationQRChat"></tenant-qr-api>
  </div>

  </template>

  <script>
  export default {
    data() {
      return {
        loading_submit: false,
        resource: 'qrchat',
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
            this.form = response.data
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
          .finally(() => {
            this.loading_submit = false;
          });
      },
      disabledConfigurationQRChat(){
        this.form = {
          ...this.form,
          qrchat_enable: false,
        }
        this.submit()
      }
    }
  }
  </script>