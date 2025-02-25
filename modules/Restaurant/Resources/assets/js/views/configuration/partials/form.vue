<template>
    <el-dialog
        :title="titleDialog"
        :visible="showDialog"
        @close="close"
        @open="create"
    >
        <p>Cree rápidamente un usuario para su restaurante desde aquí. Si necesita darle más permisos para su sistema de facturación entre al <span class="btn btn-link p-0" @click="clickUser()">módulo de usuarios</span></p>
        <form autocomplete="off" @submit.prevent="submit" v-loading="loading">
            <div class="form-body">
                <el-tabs v-model="activeName">
                    <el-tab-pane class name="first">
                        <span slot="label">Datos de Usuario</span>
                        <div class="row">
                            <div class="col-md-5">
                                <div
                                    :class="{ 'has-danger': errors.restaurant_role_id }"
                                    class="form-group"
                                >
                                    <label class="control-label">Rol</label>
                                    <el-select v-model="form.restaurant_role_id" filterable clearable >
                                        <el-option
                                            v-for="option in roles"
                                            :key="option.id"
                                            :label="option.name"
                                            :value="option.id"
                                        ></el-option>
                                    </el-select>
                                    <small
                                        v-if="errors.restaurant_role_id"
                                        class="form-control-feedback"
                                        v-text="errors.restaurant_role_id[0]"
                                    ></small>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div :class="{ 'has-danger': errors.name }" class="form-group">
                                    <label class="control-label">
                                        Nombre
                                        <el-tooltip class="item"
                                                    content="Nombre que se muestra en el sistema al haber iniciado sesión"
                                                    effect="dark"
                                                    placement="top">
                                            <i class="fa fa-info-circle"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input v-model="form.name"></el-input>
                                    <small
                                        v-if="errors.name"
                                        class="form-control-feedback"
                                        v-text="errors.name[0]"
                                    ></small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div :class="{ 'has-danger': errors.restaurant_pin }" class="form-group">
                                    <label class="control-label">Pin</label>
                                    <el-input
                                        v-model="form.restaurant_pin"
                                    ></el-input>
                                    <small
                                        v-if="errors.restaurant_pin"
                                        class="form-control-feedback"
                                        v-text="errors.restaurant_pin[0]"
                                    ></small>
                                </div>
                            </div>
                            <div v-if="form.restaurant_role_id==3||form.restaurant_role_id==2" class="col-md-5">
                                <div :class="{ 'has-danger': errors.email }" class="form-group">
                                    <label class="control-label">Correo electrónico
                                        <el-tooltip class="item"
                                                    content="Correo de acceso/login"
                                                    effect="dark"
                                                    placement="top">
                                            <i class="fa fa-info-circle"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input
                                        v-model="form.email"
                                        :disabled="form.id != null"
                                    ></el-input>
                                    <small
                                        v-if="errors.email"
                                        class="form-control-feedback"
                                        v-text="errors.email[0]"
                                    ></small>
                                </div>
                            </div>
                            <div v-if="form.restaurant_role_id==3||form.restaurant_role_id==2" class="col-md-5">
                                <div :class="{ 'has-danger': errors.password }" class="form-group">
                                    <label class="control-label">Contraseña
                                        <el-tooltip class="item" effect="dark" placement="top-start" v-if="config_regex_password_user">
                                            <i class="fa fa-info-circle"></i>
                                            <div slot="content">
                                                <strong>FORMATO DE CONTRASEÑA</strong><br/><br/>
                                                La contraseña debe contener al menos una letra minúscula.<br/>
                                                La contraseña debe contener al menos una letra mayúscula.<br/>
                                                La contraseña debe contener al menos un dígito.<br/>
                                                La contraseña debe contener al menos un carácter especial [@.$!%*#?&-].<br/>
                                            </div>
                                        </el-tooltip>
                                    </label>
                                    <el-input v-model="form.password" show-password></el-input>
                                    <small
                                        v-if="errors.password"
                                        class="form-control-feedback"
                                        v-text="errors.password[0]"
                                    ></small>
                                </div>
                            </div>

                        </div>
                    </el-tab-pane>
                </el-tabs>
            </div>
            <div class="form-actions text-right mt-4">
                <el-button class="second-buton" @click.prevent="close()">Cancelar</el-button>
                <el-button :loading="loading_submit" native-type="submit" type="primary"
                >Guardar
                </el-button
                >
            </div>
        </form>
    </el-dialog>
</template>

<script>
export default {
    props: ["showDialog", "recordId"],
    data() {
        return {
            headers: headers_token,
            defaultProps: {
                children: "childrens",
                label: "description",
            },
            form_zone: {add: false, name: null, id: null},
            loading_submit: false,
            titleDialog: null,
            resource: "restaurant",
            errors: {},
            zones:[],
            form: {
                id: null,
                name: null,
                email: null,
                api_token: null,
                establishment_id: null,
                password: null,
                password_confirmation: null,
                locked: false,
                type: null,
                series_id: null,
                document_id: null,
                modules: [],
                levels: [],
                permission_edit_cpe: false,
                recreate_documents: false,
                permission_force_send_by_summary: false,
                permission_edit_item_prices: true,
                restaurant_pin:'',
                restaurant_role_id: null,
            },
            modules: [],
            datai: [],
            establishments: [],
            documents: [],
            series: [],
            types: [],
            // define the default value
            value: null,
            // define options
            alwaysOpen: true,
            options: [],
            activeName: 'first',
            config_permission_to_edit_cpe : false,
            config_regex_password_user: false,
            identity_document_types: [],
            document_types: [],
            loading: false,
            roles: {},
        };
    },
    updated() {
        // Set default values for multiple selection trees
        if (this.modules !== undefined && this.$refs.tree !== undefined) {
            // this.$refs.tree.setCheckedKeys(this.modules)
        }
    },
    async created() {
        await this.$http.get(`/${this.resource}/get-roles`).then(response => {
          if (response.data !== '') {
            this.roles = response.data.data;
          }
        });
        await this.initForm();
    },
    methods: {
        async getSeries(){
            this.series = [];
            if(this.form.establishment_id !== null) {
                let url = `/series/records/${this.form.establishment_id}`;
                if (this.form.document_id !== null) {
                    url = url + `/${this.form.document_id}`;
                }
                await this.$http
                    .get(url)
                    .then((response) => {
                        this.series = response.data.data;
                    });
            }
        },
        FixChildren(currentObj, treeStatus) {
            if (currentObj !== undefined) {
                let selected = treeStatus.checkedKeys.indexOf(currentObj.id) // -1 is unchecked
                if (selected !== -1) {
                    this.SelectParent(currentObj)
                    this.FixSameValueToChild(currentObj, true)
                } else {
                    if (currentObj.childrens !== undefined && currentObj.childrens.length !== 0) {
                        this.FixSameValueToChild(currentObj, false)
                    }
                }
            }
        },
        FixSameValueToChild(treeList, isSelected) {
            if (treeList !== undefined) {
                this.$refs.tree.setChecked(treeList.id, isSelected)
                if (treeList.childrens !== undefined) {
                    for (let i = 0; i < treeList.childrens.length; i++) {
                        this.FixSameValueToChild(treeList.childrens[i], isSelected)
                    }
                }
            }
        },
        SelectParent(currentObj) {
            if (currentObj !== undefined) {
                let currentNode = this.$refs.tree.getNode(currentObj)
                if (currentNode.parent.key !== undefined) {
                    this.$refs.tree.setChecked(currentNode.parent, true)
                    this.SelectParent(currentNode.parent)
                }
            }
        },


        initForm() {
            this.errors = {};
            this.form = {
                id: null,
                name: null,
                email: null,
                api_token: null,
                establishment_id: null,
                password: null,
                password_confirmation: null,
                locked: false,
                type: null,
                series_id: null,
                document_id: null,
                modules: [],
                levels: [],
                permission_edit_cpe: false,
                recreate_documents: false,
                create_payment: true,
                delete_payment: true,
                edit_purchase: true,
                annular_purchase: true,
                delete_purchase: true,
                
                identity_document_type_id: null,
                number: null,
                address: null,
                names: null,
                last_names: null,
                personal_email: null,
                corporate_email: null,
                personal_cell_phone: null,
                corporate_cell_phone: null,
                date_of_birth: null,
                contract_date: null,
                position: null,

                photo_filename: null,
                photo_temp_image: null,
                photo_temp_path: null,
                multiple_default_document_types: false,
                default_document_types: [],
                permission_force_send_by_summary: false,
                permission_edit_item_prices: true,
                restaurant_pin:'',
                restaurant_role_id: 1 ,
            };
        },
        async changeEstablishment()
        {
            await this.getSeries()
            await this.initDataDefaultDocumentTypes()
        },
        initDataDefaultDocumentTypes(init_series_id = true)
        {
            this.form.default_document_types.forEach(row => {
                if(init_series_id) row.series_id = null
                row.default_series = this.getDefaultDocumentTypeSeries(row.document_type_id)
            })
        },
        clickAddDefaultDocumentType()
        {
            if(!this.form.establishment_id) return this.$message.warning('Seleccione un sucursal para buscar las series disponibles.')

            this.form.default_document_types.push({
                document_type_id: null,
                series_id: null,
                default_series: [],
            })
        },
        changeMultipleDefaultDocumentType()
        {
            if(this.form.multiple_default_document_types)
            {
                this.form.document_id = null
                this.getSeries()
            } 
            else
            {
                this.form.default_document_types = []
            }
        },
        clickDeleteDefaultDocumentType(index)
        {
            this.form.default_document_types.splice(index, 1)
        },
        changeDefaultDocumentType(index)
        {
            this.form.default_document_types[index].series_id = null

            const current_document_type_id = this.form.default_document_types[index].document_type_id

            const exist_document_type = this.getExistDocumentType(current_document_type_id, index)

            if(exist_document_type)
            {
                this.form.default_document_types[index].document_type_id = null
                return this.$message.warning('Ya agregó ese tipo de documento')
            }

            this.form.default_document_types[index].default_series = this.getDefaultDocumentTypeSeries(current_document_type_id)
        },
        getExistDocumentType(current_document_type_id, index)
        {
            return this.form.default_document_types.find((row, row_index)=>{
                    return row.document_type_id === current_document_type_id && index !== row_index
                })
        },
        getDefaultDocumentTypeSeries(document_type_id)
        {
            return _.filter(this.series, { document_type_id : document_type_id })
        },
        async create() {
            this.titleDialog = this.recordId ? "Editar Usuario" : "Nuevo Usuario"

            this.loading = true

            if (this.recordId) 
            {
                await this.$http.get(`/${this.resource}/users/record/${this.recordId}`)
                    .then((response) => {
                        this.form = response.data.data;
                    });
            } 

            this.loading = false

        },
        submit() {
            const modules = [];
            const levels = [];
            this.form.modules = modules;
            this.form.levels = levels;

            if(this.form.restaurant_pin){
                if(this.form.restaurant_pin.length!=4){
                    return this.$message.error("Debe ingresar pin de 4 dígitos");
                }
            }

            this.loading_submit = true;
            this.$http
                .post(`/${this.resource}/users`, this.form)
                .then((response) => {
                    if (response.data.success) {
                        this.form.password = null;
                        this.form.password_confirmation = null;
                        this.$message.success(response.data.message);
                        this.$eventHub.$emit("reloadData");
                        this.close();
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        this.$message.error(error.response.data.message);
                    }
                })
                .then(() => {
                    this.loading_submit = false;
                });
        },
        close() {
            this.$emit("update:showDialog", false);
            this.initForm();
        },
        clickUser() {
            window.location.href = "/users";
        },
    },
};
</script>