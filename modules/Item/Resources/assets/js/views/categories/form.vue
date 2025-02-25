<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group" :class="{'has-danger': errors.name}">
                            <label class="control-label">Nombre</label>
                            <el-input v-model="form.name"></el-input>
                            <small class="form-control-feedback" v-if="errors.name" v-text="errors.name[0]"></small>
                        </div>
                    </div> 
                </div> 
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group" :class="{'has-danger': errors.image}">
                        <label class="control-label">
                            Imágen
                            <span class="text-danger"></span>
                            <div class="sub-title text-danger">
                            <small>Se recomienda resoluciones 1024x1024</small>
                            </div>
                        </label>
                        <el-upload
                            class="avatar-uploader"
                            :data="{'type': 'categories'}"
                            :headers="headers"
                            :action="`/${resource}/upload`"
                            :show-file-list="false"
                            :on-success="onSuccess"
                        >
                            <img v-if="form.image_url" :src="form.image_url" class="avatar" />
                            <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                        </el-upload>
                        <small class="form-control-feedback" v-if="errors.image" v-text="errors.image[0]"></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right pt-2">
                <el-button class="second-buton" @click.prevent="close()">Cancelar</el-button>
                <el-button type="primary" native-type="submit" :loading="loading_submit">Guardar</el-button>
            </div>
        </form>
    </el-dialog>
</template>
 
<script>
 

    export default {
        props: ['showDialog', 'recordId'],
        data() {
            return {
                loading_submit: false,
                titleDialog: null,
                resource: 'categories', 
                errors: {}, 
                form: {}, 
                headers: headers_token,
            }
        },
        created() {
            this.initForm() 
        },
        methods: {
            initForm() { 
                this.errors = {} 

                this.form = {
                    id: null,
                    name: null, 
                    image: null,
                    image_url: null,
                    temp_path: null,
                }
            },
            create() {

                this.titleDialog = (this.recordId)? 'Editar categoría':'Nueva categoría'
                if (this.recordId) {
                    this.$http.get(`/${this.resource}/record/${this.recordId}`).then(response => {
                            this.form = response.data
                        })
                }
            },
            submit() {   
 

                this.loading_submit = true  
                this.$http.post(`${this.resource}`, this.form)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                            this.$eventHub.$emit('reloadData')
                            this.close()
                        } else {
                            this.$message.error(response.data.message)
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.errors = error.response.data 
                        } else {
                            console.log(error.response)
                        }
                    })
                    .then(() => {
                        this.loading_submit = false
                    })
                    
            },  
            onSuccess(response, file, fileList) {
                if (response.success) {
                    this.form.image = response.data.filename;
                    this.form.image_url = response.data.temp_image;
                    this.form.temp_path = response.data.temp_path;
                } else {
                    this.$message.error(response.message);
                }
            },
            close() {
                this.$emit('update:showDialog', false)
                this.initForm()
            }
        }
    }
</script>
<style>
.avatar-uploader {
    width: 178px;
    height: 178px;
}
.avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.avatar-uploader-icon {
    font-size: 28px;
    color: #8c8c8c;
    line-height: 178px;
    text-align: center;
    max-width: 178px;
}
.el-icon-plus {
    width: 178px;
    height: 178px;
}
</style>