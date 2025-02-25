<template>
    <div v-loading="loading_submit">
        <div class="row ">       
            
            <div class="col-md-12">
                <div class="table-responsive">
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
        promotionType: String
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
            let resource = (this.promotionType=='banners')?this.resource:'promotions-list'
            return this.$http
                .get(`/${resource}/records?${this.getQueryParameters()}`)
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
