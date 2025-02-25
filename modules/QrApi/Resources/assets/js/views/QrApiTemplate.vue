<template>
    <div :class="colClass?colClass:'col-12'">
        <el-input v-model="wsPhone">
            <template slot="prepend">+51</template>
            <template slot="append">
                <el-tooltip class="item"
                    content="Requiere configuración de tokens en módulo de empresa"
                    effect="dark"
                    placement="top-start">
                    <el-button
                        @click="sendQrChat"
                        :disabled="button_disable"
                        :loading="loading_submit">
                        Enviar <i class="fab fa-whatsapp"></i>
                    </el-button>
                </el-tooltip>
            </template>
        </el-input>
        <small v-if="errors.customer_telephone"
            class="form-control-feedback"
            v-text="errors.customer_telephone[0]"></small>
    </div>
</template>

<script>

import {mapState} from "vuex/dist/vuex.mjs";
export default {
    props: ['colClass','wsPhone','wsFile','wsDocument','wsMessage', 'wsData'],
    data() {
        return {
            form: {},
            errors: {},
            button_disable: true,
            loading_submit: false,
            resource: 'qrapi'
            // text: 'Su comprobante de pago electrónico F001-4 ha sido generado correctamente',
        }
    },
      computed: {
        ...mapState([
            'config',
        ]),
    },
    mounted(){
        this.enableSend()
    },

    methods: {
        enableSend() {
            if(this.config.qr_api_url_ws != '' && this.config.qr_api_key_ws != '') {
                this.button_disable = false
            }
        },
        async sendQrChat() {
            this.loading_submit = true
            if (this.wsPhone == '') {
                return this.$message.error('El número es obligatorio')
            }

            const {extension_only, filename_only} = this.wsData;
            this.convertFileToBase64(this.wsFile)
                .then(file_encode64 => {
                    
                    this.setForm(file_encode64, `${filename_only}.${extension_only}`)
                    return this.$http
                        .post(`${this.config.qr_api_url_ws}\\api\\message\\send\\pdf`, this.form, {
                            headers: {
                                "Authorization" : `Bearer ${this.config.qr_api_key_ws}`
                            }
                        })
                        .then(response => {
                            if(response.status == 200) {
                                return this.$message.success('Documento enviado con exito')
                            }
                        })
                        .catch(error => {
                            return this.$message.error('No se puede enviar')
                        })
                        .finally(() => {
                            this.loading_submit = false
                        });
                })

        },
        setForm(base64file, full_filename) {
            this.form = {
                file: base64file,
                number: `51${this.wsPhone}`,
                message: this.wsMessage,
                filename: full_filename 
            }
        },
        async convertFileToBase64(url) {
            try {
                const response = await fetch(url);
                const blob = await response.blob();
                return await this.blobToBase64(blob);
            } catch (error) {
                console.error(error);
                return this.$message.error('Error al convertir el archivo a base64:')
            }
        },
        blobToBase64(blob) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => {
                    resolve(reader.result.split(',')[1]); // Quitar la parte 'data:*/*;base64,'
                };
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        },

    }
}

</script>