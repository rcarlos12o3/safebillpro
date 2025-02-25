<template>
    <div class="card">
      <div class="card-header bg-info">
        <h3 class="my-0">Envio de mensajes a través de QR Api</h3>
      </div>
      <div class="card-body">
        <form action="" autocomplete="off" @submit.prevent="submit">
          <div class="form-body">
            <p v-show="!form.qr_api_enable">Deshabilitado esta función tiene dos formas de enviar sus comprobantes, a través de Chat Buho o el Servicio de WhatsApp Web</p>
            <p v-show="form.qr_api_enable">Recuerde que cuando activo esta funcionalidad necesita credenciales de un servicio, para registrarse entre a la página <a class="text-primary" href="https://qr-api.com">QR Api</a></p>

            <div class="row">
              <div class="col-12">
                <el-switch v-model="form.qr_api_enable_ws" @change="verifyQRChat"
                  active-text="Si"
                  inactive-text="No"></el-switch>
              </div>
               <div class="col-12" v-show="form.qr_api_enable_ws">
                <div :class="{'has-danger': errors.qr_api_url_ws}"
                  class="form-group">
                  <label class="control-label">
                    URL de Cliente
                  </label>
                  <el-input v-model="form.qr_api_url_ws"></el-input>
                  <small
                    v-if="errors.qr_api_url_ws"
                    class="invalid-feedback d-block"
                    v-text="errors.qr_api_url_ws[0]"></small>
                </div>
              </div>
              <div class="col-12" v-show="form.qr_api_enable_ws">
                <div :class="{'has-danger': errors.qr_api_key_ws}"
                  class="form-group">
                  <label class="control-label">
                    Api Key
                  </label>
                  <el-input v-model="form.qr_api_key_ws"></el-input>
                  <small
                    v-if="errors.qr_api_key_ws"
                    class="invalid-feedback d-block"
                    v-text="errors.qr_api_key_ws[0]"></small>
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

      <el-dialog
        title="Advertencia al Activar QR Api"
        :visible.sync="dialogVisibleAdvertencia"
        width="30%"
        >
            <span>Esta es una nueva funcionalidad que esta en fase beta, una vez aceptado se desactivará la función de QR Chat Buho.</span>
            <span slot="footer" class="dialog-footer">
            <el-button @click="dialogVisibleAdvertencia = false">Cancelar</el-button>
            <el-button type="primary" @click="confirmDisabledQRChatBuho">Confirmar</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>

export default {
   props: {
    enabledChatBuhoProp: {
        type: Boolean,
        require: true
    },
    changeQREnabled: {
        type: Function,
        require: true
    }
   },
   data(){
    return {
        form: {},
        dialogVisibleAdvertencia: false,
        errors: {},
        resource: 'qrapi',
        loading_submit: false

    }
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
    submit(){
      this.loading_submit = true;
      this.$http
      .post(`/${this.resource}/configuration/update`, this.form)
      .then(response => {
        if (response.data.success) {
          this.$message.success(response.data.message);
        } else {
          this.$message.error(response.data.message)
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
    verifyQRChat() {
        //Verificar si tiene en funcionamiento el QR Chat buho
        if (this.enabledChatBuhoProp == true) {
           this.dialogVisibleAdvertencia = true 
        }
   },
   confirmDisabledQRChatBuho() {
        this.changeQREnabled()
        this.dialogVisibleAdvertencia = false
   }
   }
}
</script>