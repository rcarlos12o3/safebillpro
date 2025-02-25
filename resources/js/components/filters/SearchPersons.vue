<template>
    <div class="form-group">
        <label class="control-label">
            {{ isCustomer ? 'Cliente' : 'Proveedor' }}
            <el-tooltip class="item" effect="dark" :content="`Escribir al menos ${init_search_length} caracteres para buscar`" placement="top-start">
                <i class="fa fa-info-circle"></i>
            </el-tooltip>
        </label>
        <el-select v-model="person_id" filterable remote
            :disabled="isDisabled"
            class="full"
            placeholder="Escriba el nombre o número de documento"
            :remote-method="searchRemoteData"
            :clearable="isClearable"
            :loading="loading_search"
            @change="changePerson">
            <el-option v-for="option in records" :key="option.id" :value="option.id" :label="option.search_full_name"></el-option>
        </el-select>

        <small class="text-danger" v-if="errors_person_id" v-text="errors_person_id[0]"></small>
    </div>
</template>

<script>

    export default {
        props: {
            errors_person_id: Array,
            isDisabled: {
                type: Boolean,
                default: false
            },
            isClearable: {
                type: Boolean,
                default: false
            },
            type: {
                type: String,
                required: false,
                default: 'customers'
            },
        },
        data () {
            return {
                records: [],
                all_records: [],
                loading_search: false,
                init_search_length: 2,
                person_id: null,
            }
        },
        created()
        {
            this.initData()
        },
        computed:
        {
            isCustomer()
            {
                return this.type === 'customers'
            }
        },
        methods: {
            cleanValue()
            {
                this.person_id = null
            },
            changePerson()
            {
                this.$emit('changePerson', this.person_id)
            },
            async initData()
            {
                await this.getData()
                            .then(response => {
                                this.all_records = response.data
                                this.filterRecords()
                            })
            },
            async getData(params = '')
            {
                return await this.$http.get(`/persons/search-data/${this.type}?${params}`)
            },
            async searchRemoteData(input)
            {
                if (input.length >= this.init_search_length)
                {
                    this.loading_search = true
                    const parameters = `input=${input}`

                    await this.getData(parameters)
                                .then(response => this.setDataFromResponse(response))
                }
                else
                {
                    this.filterRecords()
                }

            },
            setDataFromResponse(response)
            {
                this.records = response.data
                this.loading_search = false
                if(this.records.length == 0) this.filterRecords()
            },
            async searchDataById(id)
            {
                const parameters = `id=${id}`

                await this.getData(parameters)
                            .then(response => this.setDataFromResponse(response))

            },
            setValueFromUpdate(id)
            {
                this.person_id = id
                this.searchDataById(id)
            },
            filterRecords()
            {
                this.records = this.all_records
            },
        }
    }
</script>