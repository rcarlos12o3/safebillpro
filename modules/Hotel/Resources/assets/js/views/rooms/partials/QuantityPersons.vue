<template>
    <el-dialog :title="titleDialog"
               width="50%"
               :visible="showDialog"
               @open="create"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               append-to-body
               :show-close="false">

        <div class="form-body" v-if="persons">
            <div class="row" >
                <div class="col-lg-12 col-md-12">
                    <table width="100%">
                        <el-button v-if="loading_search" type="primary" slot="append" :loading="loading_search" icon="el-icon-search">
                        </el-button>
                        <thead>
                            <tr width="100%">
                                <th>DNI/RUC</th>
                                <th>Apellidos y nombres</th>
                                <th width="15%"><a href="#" @click.prevent="clickAddPerson" class="text-center font-weight-bold text-info">[+ Agregar]</a></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in persons" :key="index" width="100%" >
                                <td>
                                    <div class="form-group mb-2 mr-2"  >
                                        <el-input @change="searchPerson(row,index)" @blur="duplicateDocument(row.number, index)" v-model="row.number"></el-input>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group mb-2 mr-2" >
                                        <el-input v-model="row.name"></el-input>
                                    </div>
                                </td>
                                <td class="series-table-actions text-center">
                                    <button  type="button" class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickCancel(index)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                                <br>
                            </tr>
                        </tbody>
                    </table>


                </div>

            </div>
        </div>

        <div class="form-actions text-right pt-2">
            <el-button @click.prevent="clickCancelSubmit()">Cancelar</el-button>
            <el-button type="primary" @click="submit" >Guardar</el-button>
        </div>
    </el-dialog>
</template>

<script>
    export default {
        props: ['showDialog', 'persons', 'quantity'],
        data() {
            return {
                titleDialog: 'Personas en la habitación',
                loading: false,
                errors: {},
                form: {},
                loading_search: false,
            }
        },
        async created() {

        },
        methods: {
            async duplicateDocument(data, index)
            {

                let duplicates = await _.filter(this.persons, {'number':data})
                if(duplicates.length > 1)
                {
                    this.persons[index].number = ''
                }
            },
            create(){
                if(this.persons.length == 0){
                    this.clickAddPerson()
                }
            },
            async validatePersons(){

                let error = 0

                await this.persons.forEach(element => {
                    if(element.number == null){
                        error++
                    }
                });

                if(error>0)
                    return {success:false, message:'El campo número es obligatorio'}

                return {success:true}

            },
            async submit(){

                let val_persons = await this.validatePersons()
                if(!val_persons.success)
                    return this.$message.error(val_persons.message);

                await this.$emit('addRowPerson', this.persons);
                await this.$emit('update:showDialog', false)

            },
            clickAddPerson() {
                this.persons.push({
                    number: null,
                    name:  ''
                });
            },

            close() {
                this.$emit('update:showDialog', false)
            },
            clickCancel(index) {
                this.persons.splice(index, 1);
            },
            async clickCancelSubmit() {
                await this.$emit('update:showDialog', false)
            },
            close() {
                this.$emit('update:showDialog', false)
            },
            searchPerson(row,index) {
                if(row.number!='' && row.number.length >=8 && row.number.length <=11){
                    let type = row.number.length==8 ?'dni':'ruc';
                    this.searchServiceNumberByType(type,row)
                }
                
            },
            async searchServiceNumberByType(type,row) {
                this.loading_search = true
                let response = await this.$http.get(`/service/${type}/${row.number}`)
                if(response.data.success) {
                    let data = response.data.data
                    row.name = data.name

                } else {
                    this.$message.error(response.data.message)
                }
                this.loading_search = false
            },
        }
    }
</script>
