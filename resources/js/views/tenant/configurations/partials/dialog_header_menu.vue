<template>
    <div class="d-none ml-1 d-lg-block"
         style="height: inherit;">

        <a v-if="menu.menu_a != '' && menu.menu_a !== undefined && menu.menu_a.route_path !== undefined"
           :href="menu.menu_a.route_path"
           :title="menu.menu_a.description"
           class="topbar-links"
           data-placement="bottom"
           data-toggle="tooltip">
           <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-invoice"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M19 12v7a1.78 1.78 0 0 1 -3.1 1.4a1.65 1.65 0 0 0 -2.6 0a1.65 1.65 0 0 1 -2.6 0a1.65 1.65 0 0 0 -2.6 0a1.78 1.78 0 0 1 -3.1 -1.4v-14a2 2 0 0 1 2 -2h7l5 5v4.25" /></svg>
            <span>{{ menu.menu_a.label_menu }}</span>
        </a>
        <a v-if="menu.menu_b != '' && menu.menu_b !== undefined && menu.menu_b.route_path !== undefined"
           :href="menu.menu_b.route_path"
           :title="menu.menu_b.description"
           class="topbar-links"
           data-placement="bottom"
           data-toggle="tooltip">
           <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-license"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 21h-9a3 3 0 0 1 -3 -3v-1h10v2a2 2 0 0 0 4 0v-14a2 2 0 1 1 2 2h-2m2 -4h-11a3 3 0 0 0 -3 3v11" /><path d="M9 7l4 0" /><path d="M9 11l4 0" /></svg>
            <span>{{ menu.menu_b.label_menu }}</span>
        </a>
        <a v-if="menu.menu_c != '' && menu.menu_c !== undefined && menu.menu_c.route_path !== undefined"
           :href="menu.menu_c.route_path"
           :title="menu.menu_c.description"
           class="topbar-links"
           data-placement="bottom"
           data-toggle="tooltip">
           <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-writing"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 17v-12c0 -1.121 -.879 -2 -2 -2s-2 .879 -2 2v12l2 2l2 -2z" /><path d="M16 7h4" /><path d="M18 19h-13a2 2 0 1 1 0 -4h4a2 2 0 1 0 0 -4h-3" /></svg>
            <span>{{ menu.menu_c.label_menu }}</span>
        </a>
        <a v-if="menu.menu_d != '' && menu.menu_d !== undefined && menu.menu_d.route_path !== undefined"
           :href="menu.menu_d.route_path"
           :title="menu.menu_d.description"
           class="topbar-links"
           data-placement="bottom"
           data-toggle="tooltip">
           <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-tag-starred"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z" /><path d="M12.5 13.847l-1.5 1.153l.532 -1.857l-1.532 -1.143h1.902l.598 -1.8l.598 1.8h1.902l-1.532 1.143l.532 1.857z" /></svg>
            <span>{{ menu.menu_d.label_menu }}</span>
        </a>
        <a class="topbar-links"
           data-placement="bottom"
           data-toggle="tooltip"
           href="#"
           title="editar accesos directos"
           @click="showDialog = true">
           <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
            <span class="fix-m"><i class="fas fa-ellipsis-h"></i></span>
        </a>
        <el-dialog :visible="showDialog"
                   title="Accesos Directos"
                   top="20vh"
                   width="40%"
                   @close="close">
            <el-form :model="form">
                <el-form-item :label-width="formLabelWidth"
                              label="Menu 1">
                    <el-select v-model="form.menu_a"
                               placeholder="Menu 1">
                        <el-option v-for="option in modules"
                                   :key="'a'+option.id"
                                   :label="option.route_path"
                                   :v-if="option !== undefined && option.route_path !== undefined"
                                   :value="option.id"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item :label-width="formLabelWidth"
                              label="Menu 2">
                    <el-select v-model="form.menu_b"
                               placeholder="Menu 2"
                               required>
                        <el-option v-for="option in modules"
                                   :key="'b'+option.id"
                                   :label="option.route_path"
                                   :v-if="option !== undefined && option.route_path !== undefined"
                                   :value="option.id"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item :label-width="formLabelWidth"
                              label="Menu 3">
                    <el-select v-model="form.menu_c"
                               placeholder="Menu 3"
                               required>
                        <el-option v-for="option in modules"
                                   :key="'c'+option.id"
                                   :label="option.route_path"
                                   :v-if="option !== undefined && option.route_path !== undefined"
                                   :value="option.id"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item :label-width="formLabelWidth"
                              label="Menu 4">
                    <el-select v-model="form.menu_d"
                               placeholder="Menu 4"
                               required>
                        <el-option v-for="option in modules"
                                   :key="'one'+option.id"
                                   :label="option.route_path"
                                   :v-if="option !== undefined && option.route_path !== undefined"
                                   :value="option.id"></el-option>
                    </el-select>
                </el-form-item>
            </el-form>
            <span slot="footer"
                  class="dialog-footer">
        <el-button @click.prevent="close()">Cancel</el-button>
        <el-button type="primary"
                   @click.prevent="clickSubmit">Guardar</el-button>
      </span>
        </el-dialog>
    </div>
</template>

<script>
export default {
    data() {
        return {
            form: {
                menu_a: null,
                menu_b: null,
                menu_c: null,
                menu_d: null,
            },
            formLabelWidth: '120px',
            modules: [],
            showDialog: false,
            menu: {}
        };
    },
    created() {
        this.getRecords();
    },
    methods: {
        getRecords() {
            this.$http.get(`/configurations/visual/get_menu`).then(response => {
                if (response.data !== '') {
                    this.modules = response.data.modules;
                    this.menu = {
                        menu_a: response.data.menu.top_menu_a,
                        menu_b: response.data.menu.top_menu_b,
                        menu_c: response.data.menu.top_menu_c,
                        menu_d: response.data.menu.top_menu_d
                    }
                    this.form = {
                        menu_a: response.data.menu.top_menu_a.id,
                        menu_b: response.data.menu.top_menu_b.id,
                        menu_c: response.data.menu.top_menu_c.id,
                        menu_d: response.data.menu.top_menu_d.id
                    }
                }
            });
        },
        clickSubmit() {
            this.$http.post(`/configurations/visual/set_menu`, this.form).then(response => {
                if (response.data.success) {
                    this.$message.success(response.data.message);
                    this.menu = {
                        menu_a: response.data.menu.top_menu_a,
                        menu_b: response.data.menu.top_menu_b,
                        menu_c: response.data.menu.top_menu_c,
                        menu_d: response.data.menu.top_menu_d
                    }
                } else {
                    this.$message.error(response.data.message);
                }
            }).catch(error => {
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    console.log(error);
                }
            }).then(() => {
                this.loading_submit = false;
            });
            this.close()
        },
        close() {
            this.showDialog = false;
        },
    }
};
</script>

<style>
.v-modal {
    z-index: 1 !important;
}
</style>
