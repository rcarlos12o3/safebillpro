<template>
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="my-0">Certificado Qz Tray</h3>
        </div>
        <div class="card-body">
            <p>Se tiene que ingresar los dos archivos generados en los certificados de Qz Tray</p>
            <div class="table-responsive" v-if="record">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Archivo</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{{ record }}</td>
                        <td class="text-right">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                    @click.prevent="clickDelete">Eliminar</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
                <div class="row text-center mx-auto" v-else>
                    <div class="col-md-6 col-12">
                        <label>Digital Certificate</label>
                        <el-input v-model="form.digital_qztray"
                            :readonly="true">
                            <el-upload slot="append"
                                ref="digitalqz"
                                :name="'digital_qztray'"
                                :headers="headers"
                                :on-success="successUpload"
                                :on-error="errorUpload"
                                :show-file-list="false"
                                :multiple="false"
                                action="/certificates-qztray/uploads">
                                <el-button icon="el-icon-upload"
                                    type="primary"></el-button>
                            </el-upload>
                        </el-input>
                    </div>
                    <div class="col-lg-6 col-12">
                        <span>Private Key</span>
                        <el-input v-model="form.private_qztray"
                            :readonly="true">
                            <el-upload slot="append"
                                ref="privateqz"
                                :data="{'digital_qztray': null}"
                                :name="'private_qztray'"
                                :headers="headers"
                                :on-success="successUpload"
                                :on-error="errorUpload"
                                :show-file-list="false"
                                :multiple="false"
                                action="/certificates-qztray/uploads">
                                <el-button icon="el-icon-upload"
                                    type="primary"></el-button>
                            </el-upload>
                        </el-input>
                    </div>
                    <div class="row mt-4" v-if="showButtonDelete">
                        <div class="col-md-12 text-right">
                                <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                        @click.prevent="removeCertificateQzTray">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>

<script>
import {deletable} from '../../../../mixins/deletable'

export default {
    mixins: [deletable],
    data() {
        return {
            headers: headers_token,
            showButtonDelete : false,
            resources: 'certificates-qztray',
            form: {},
        }
    },
    created() {
        this.initForm()
        this.getRecordCertificatesQzTray()
    },
    methods: {
        successUpload(response, file, fileList) {

            if (response.success) {
                this.$message.success(response.message)
                this.form[response.type] = response.name
                this.showButtonDelete = true;
            } else {
                this.$message({message: 'Error al subir el archivo', type: 'error'})
            }
        },
        errorUpload(error)
        {
            this.$message({message: 'Error al subir el archivo', type: 'error'})
        },
        initForm(){
            this.form = {
                digital_qztray: null,
                private_qztray: null
            }
        },
        getRecordCertificatesQzTray() {
            this.$http
                .get(`/${this.resources}/record`)
                .then(response => {
                    let certificates = response.data.record[0]
                    
                        if (certificates.digital_certificate_qztray || certificates.private_certificate_qztray ) {
                            this.showButtonDelete = true
                        }
                        this.form.digital_qztray = certificates.digital_certificate_qztray ? certificates.digital_certificate_qztray : null;
                        this.form.private_qztray = certificates.private_certificate_qztray ? certificates.private_certificate_qztray : null;
                        
                })
        },
        async removeCertificateQzTray() {
            await this.destroy(`/${this.resources}`)
            this.showButtonDelete = false;
            this.getRecordCertificatesQzTray()
        }
    }
}
</script>