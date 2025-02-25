<template>
    <el-dialog
        :title="titleDialog"
        width="50%"
        :visible="showDialog"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        append-to-body
        :show-close="false"
    >
        <div class="form-body">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Código</th>
                            <th>Cantidad</th>
                            <th>Fecha vencimiento</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr
                            v-for="(row, index) in lotsGroupSelected"
                            :key="index"
                        >
                            <th>{{ row.code }}</th>
                            <th class>{{ row.compromise_quantity }}</th>
                            <th class>{{ row.date_of_due }}</th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="form-actions text-right pt-2">
            <el-button @click.prevent="close()">Cerrar</el-button>
        </div>
    </el-dialog>
</template>

<script>
export default {
    name: 'ListLotsGroupSelected',
    props: ["showDialog",
        'lotsGroupSelected'
    ],
    data() {
        return {
            titleDialog: "Lotes seleccionados",
            loading: false,
        };
    },
    async created() {

    },
    computed: {

    },
    methods: {
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>
