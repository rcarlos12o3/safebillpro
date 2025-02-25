<template>
    <div v-loading="loading_submit">
        <div class="row ">
            <div class="col-md-8 col-lg-8 col-xl-8 filter-container">
                <div class="btn-filter-content">
                    <el-button
                        type="primary"
                        class="btn-show-filter mb-2"
                        :class="{ shift: isVisible }"
                        @click="toggleInformation"
                    >
                        {{ isVisible ? "Ocultar filtros" : "Mostrar filtros" }}
                    </el-button>
                </div>
                <div class="row filter-content" v-if="applyFilter && isVisible">
                    <div class="col-lg-6 col-md-6 col-sm-12 pb-2">
                        <div class="d-flex">
                            <div style="width:100px">
                                Filtrar por:
                            </div>
                            <el-select
                                v-model="search.column"
                                placeholder="Select"
                                @change="changeClearInput"
                            >
                                <el-option
                                    v-for="(label, key) in columns"
                                    :key="key"
                                    :value="key"
                                    :label="label"
                                ></el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 pb-2">
                        <template
                            v-if="
                                search.column === 'date_of_issue' ||
                                search.column === 'date_of_due' ||
                                search.column === 'date_of_payment' ||
                                search.column === 'delivery_date'
                            "
                        >
                            <el-date-picker
                                v-model="search.value"
                                type="date"
                                style="width: 100%;"
                                placeholder="Buscar"
                                value-format="yyyy-MM-dd"
                                @change="getRecords"
                            >
                            </el-date-picker>
                        </template>
                        <template v-else>
                            <el-input
                                placeholder="Buscar"
                                v-model="search.value"
                                style="width: 100%;"
                                prefix-icon="el-icon-search"
                                @input="getRecords"
                            >
                            </el-input>
                        </template>
                    </div>
                </div>
            </div>            
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="row" v-if="fromRestaurant||fromEcommerce">
                    <div class="col-lg-12 col-md-12 col-sm-12 pb-2">
                        <div class="d-flex">
                            <div style="width:150px">
                                Listar productos
                            </div>
                            <el-select
                                v-model="search.list_value"
                                placeholder="Select"
                                @change="getRecords"
                            >
                                <el-option
                                    v-for="(label, key) in list_columns"
                                    :key="key"
                                    :value="key"
                                    :label="label"
                                ></el-option>
                            </el-select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-4 col-xl-4" v-if="fromRestaurant||fromEcommerce">
                <div class="d-flex">
                    <div style="width:160px">
                        Hacer visiblie todo los productos
                        <el-tooltip class="item" content="Unicamente se hará visiblie si tiene codigo interno"
                            effect="dark" placement="top-start">
                            <i class="fa fa-info-circle"></i>
                        </el-tooltip>
                    </div>
                    <div style="width: 30px; height: 30px" class="my-auto">
                        <el-button  @click="methodVisibleAllProduct" type="primary" size="mini" icon="el-icon-check"></el-button>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="table-responsive table-responsive-new">
                    <table class="table">
                        <thead>
                            <slot name="heading"></slot>
                        </thead>
                        <tbody>
                            <slot
                                v-for="(row, index) in records"
                                :row="row"
                                :index="customIndex(index)"
                            ></slot>
                        </tbody>
                    </table>
                    <div>
                        <el-pagination
                            @current-change="getRecords"
                            layout="total, prev, pager, next"
                            :total="pagination.total"
                            :current-page.sync="pagination.current_page"
                            :page-size="pagination.per_page"
                        >
                        </el-pagination>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style>

</style>
<script>
import queryString from "query-string";

export default {
    props: {
        productType: {
            type: String,
            required: false,
            default: ''
        },
        resource: String,
        applyFilter: {
            type: Boolean,
            default: true,
            required: false
        },
        pharmacy: Boolean,
        restaurant: Boolean,
        ecommerce: Boolean,
    },
    data() {
        return {
            search: {
                column: null,
                value: null,
                list_value: 'all',
            },
            columns: [],
            records: [],
            pagination: {},
            isVisible: false,
            loading_submit: false,
            fromPharmacy: false,
            fromRestaurant: false,
            fromEcommerce: false,
            list_columns: {
                all:'Todos',
                visible:'Visibles',
                hidden:'Ocultos'
            },
        };
    },
    created() {
        if(this.pharmacy !== undefined && this.pharmacy === true){
            this.fromPharmacy = true;
        }
        if(this.ecommerce !== undefined && this.ecommerce === true){
            this.fromEcommerce = true;
        }
        if(this.restaurant !== undefined && this.restaurant === true){
            this.fromRestaurant = true;
        }
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });
        this.$root.$refs.DataTable = this;
    },
    async mounted() {
        let column_resource = _.split(this.resource, "/");
        await this.$http
            .get(`/${_.head(column_resource)}/columns`)
            .then(response => {
                this.columns = response.data;
                this.search.column = _.head(Object.keys(this.columns));
            });
        await this.getRecords();
    },
    methods: {
        toggleInformation() {
            this.isVisible = !this.isVisible;
        },
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
        },
        getRecords() {
            this.loading_submit = true;
            return this.$http
                .get(`/${this.resource}/records?${this.getQueryParameters()}`)
                .then(response => {
                    this.records = response.data.data;
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                })
                .catch(error => {})
                .then(() => {
                    this.loading_submit = false;
                });
        },
        getQueryParameters() {
            if (this.productType == 'ZZ') {
                this.search.type = 'ZZ';
            }
            if (this.productType == 'PRODUCTS') {
                // Debe listar solo productos
                this.search.type = this.productType;
            }
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                isPharmacy:this.fromPharmacy,
                isRestaurant:this.fromRestaurant,
                isEcommerce:this.fromEcommerce,
                ...this.search
            });
        },
        changeClearInput() {
            this.search.value = "";
            this.getRecords();
        },
        getSearch() {
            return this.search;
        },
        async methodVisibleAllProduct() {
            let response = await this.$http.post(`/${this.resource}/visibleMassive`,{
                resource: this.fromRestaurant ? 'restaurant' : 'ecommerce',
            });
            console.log(response);
                
            if (response.status === 200) {
                this.$message.success(response.data.message);
                this.getRecords()
            } else {
                this.$message.error(response.data.message);

            }
        }
    }
};
</script>
