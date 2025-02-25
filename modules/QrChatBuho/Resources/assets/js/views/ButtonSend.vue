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
import * as https from 'https';

export default {
    props: ['colClass','wsPhone','wsFile','wsDocument','wsMessage'],
    data() {
        return {
            form: {},
            errors: {},
            button_disable: true,
            loading_submit: false,
            // text: 'Su comprobante de pago electrónico F001-4 ha sido generado correctamente',
        }
    },
    computed: {
        ...mapState([
            'config',
        ]),
    },
    created() {
        this.enableSend()
    },
    methods: {
        enableSend() {
            if(this.config.qrchat_url != '' && this.config.qrchat_app_key != '' && this.config.qrchat_auth_key != '') {
                this.button_disable = false
            }
        },
        sendQrChat() {
            this.loading_submit = true
            if (this.wsPhone == '') {
                return this.$message.error('El número es obligatorio')
            }
            this.convertFileToBase64(this.wsFile)
                .then(base64File => {
                    this.setForm(base64File);
                    return this.$http.post(this.config.qrchat_url, this.form, {
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${this.config.qrchat_app_key}`,
                        }
                    });
                })
                .then(response => {
                    if(response.status == 200) {
                        return this.$message.success('Documento enviado con exito')
                    }
                })
                .catch(error => {
                    console.log(error)
                    return this.$message.error('No se puede enviar')
                })
                .finally(() => {
                    this.loading_submit = false
                });
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
        setForm(base64File) {
            this.form = {
                // appkey: this.config.qrchat_app_key,
                // authkey: this.config.qrchat_auth_key,
                number: `51${this.wsPhone}`,
                message: this.wsMessage,
                file: base64File,
                filename: 'file.pdf'
            }
        }
    }
}

</script>