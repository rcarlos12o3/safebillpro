<template>
    <div id="styleSwitcher" class="style-switcher" style="z-index: 1040;">

        <!-- <a id="styleSwitcherOpen" class="style-switcher-open" href="#">
            <i class="fas fa-paint-brush"></i>
        </a> -->

        <form class="style-switcher-wrap" autocomplete="off">
            <h3>Estilos y Temas <a class="style-switcher-open close-config" href="#">+</a></h3>
            
            <div v-if="visual == null">
                <h5 class="">No posee ajustes actualmente</h5>
                <a href="" class="text-warning" v-if="typeUser != 'integrator'">cargar ajustes por defecto</a>
                <br>
            </div>
            <div v-if="typeUser != 'integrator'" class="pt-3">
                <div style="background-color: #283046;">
                    <a v-if="visuals.bg == 'white'"
                        href="/configurations/change-mode"
                        class="notification-icon btn btn-dark btn-sm btn-block"
                        data-toggle="tooltip"
                        data-placement="bottom"
                        title="Modo noche"
                        style="text-decoration: none;">
                        <i class="fas fa-moon"></i> Modo oscuro
                    </a>
                    <a v-if="visuals.bg == 'dark'"
                        href="/configurations/change-mode"
                        class="notification-icon btn btn-light btn-sm btn-block btn-light-mode"
                        data-toggle="tooltip"
                        data-placement="bottom"
                        title="Modo día"
                        style="text-decoration: none;">
                        <i class="fas fa-sun"></i> Modo Claro
                    </a>
                </div>
                <!-- <div class="pt-3">
                    <h5>Color de fondo del sidebar</h5>
                    <div class="form-group el-custom-control">
                        <button :class="{ 'active': visuals.sidebar_theme === 'white' }" type="button" @click="onChangeBgSidebar('white')" class="btn flex-fill" style="background-color: #ffffff;"></button>
                        <button :class="{ 'active': visuals.sidebar_theme === 'blue' }" type="button" @click="onChangeBgSidebar('blue')" class="btn flex-fill" style="background-color: #7367f0;"></button>
                        <button :class="{ 'active': visuals.sidebar_theme === 'gray' }" type="button" @click="onChangeBgSidebar('gray')" class="btn" style="background-color: #82868b;"></button>
                        <button :class="{ 'active': visuals.sidebar_theme === 'green' }" type="button" @click="onChangeBgSidebar('green')" class="btn flex-fill" style="background-color: #28c76f;"></button>
                        <button :class="{ 'active': visuals.sidebar_theme === 'red' }" type="button" @click="onChangeBgSidebar('red')" class="btn flex-fill" style="background-color: #ea5455;"></button>
                        <button :class="{ 'active': visuals.sidebar_theme === 'warning' }" type="button" @click="onChangeBgSidebar('warning')" class="btn" style="background-color: #ff9f43;"></button>
                        <button :class="{ 'active': visuals.sidebar_theme === 'ligth-blue' }" type="button" @click="onChangeBgSidebar('ligth-blue')" class="btn" style="background-color: #00cfe8;"></button>
                        <button :class="{ 'active': visuals.sidebar_theme === 'dark' }" type="button" @click="onChangeBgSidebar('dark')" class="btn flex-fill" style="background-color: #283046;"></button>
                    </div>
                </div> -->

                <div class="mt-3">
                    <h5>Selecciona un color de tema:</h5>
                    <div class="color-selector">
                        <button type="button" 
                                class="btn-theme-white"
                                @click="onChangeTheme('white')"
                                style="background-color: #90dad9;">
                        </button>
                        <button type="button" 
                                class="btn-theme-acid"
                                @click="onChangeTheme('acid')"
                                style="background-color: #c1b1f1;">
                        </button>
                        <button type="button" 
                                class="btn-theme-cupcake"
                                @click="onChangeTheme('cupcake')"
                                style="background-color: #e7dad0;">
                        </button>
                        <button type="button" 
                                class="btn-theme-retro"
                                @click="onChangeTheme('retro')"
                                style="background-color: #ebddb7;">
                        </button>
                        <button type="button" 
                                class="btn-theme-lemonade"
                                @click="onChangeTheme('lemonade')"
                                style="background-color: #cddfae;">
                        </button>
                    </div>
                </div>

                <div class="pt-3">
                    <h5>Menú lateral contraído</h5>
                    <div :class="{'has-danger': errors.compact_sidebar}">
                        <el-switch
                            v-model="form.compact_sidebar"
                            active-text="Si"
                            inactive-text="No"
                            @change="submitForm">
                        </el-switch>
                        <br>
                        <small class="form-control-feedback" v-if="errors.compact_sidebar" v-text="errors.compact_sidebar[0]"></small>
                    </div>
                </div>

                <div class="mt-3">
                    <h5>Mostrar panel de bienvenida en el dashboard</h5>
                    <div>
                        <el-switch
                            v-model="showWelcome"
                            active-text="Si"
                            inactive-text="No"
                            @change="updateConfig">
                        </el-switch>
                    </div>
                </div>

                <div class="pt-3 form-modern">
                    <label class="control-label">Cantidad de columnas en POS</label>
                    <div :class="{'has-danger': errors.amount_plastic_bag_taxes}">
                        <el-select v-model="form.colums_grid_item" @change="submitForm">
                            <el-option label="3" value="3"></el-option>
                            <el-option label="4" value="4"></el-option>
                            <el-option label="5" value="5"></el-option>
                            <el-option label="6" value="6"></el-option>
                        </el-select>
                        <small class="form-control-feedback" v-if="errors.amount_plastic_bag_taxes" v-text="errors.amount_plastic_bag_taxes[0]"></small>
                    </div>
                </div>

                <div class="pt-3 form-modern">
                    <label class="control-label">Cambiar tema</label>
                    <div :class="{'has-danger': errors.compact_sidebar}">
                        <el-select
                            v-model="form.skin_id"
                            placeholder="Tema"
                            @change="submitForm"
                            class="pb-3">
                            <el-option
                                v-for="item in skins"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id">
                            </el-option>
                        </el-select>
                        <small class="form-control-feedback" v-if="errors.compact_sidebar" v-text="errors.compact_sidebar[0]"></small>
                        <el-button class="second-buton" type="button" @click="dialogSkins()" color="primary">Subir tema</el-button>
                    </div>
                </div>

            </div>
        </form>
        <dialog-skins :showDialog.sync="dialogSkinsVisible" :skins.sync="skins"/>
    </div>
</template>

<script>
    import DialogSkins from './partials/dialog_skins.vue'
    export default {
        props:['visual','typeUser'],
        components: {
            DialogSkins
        },
        data() {
            return {
                themes: {},
                showWelcome: localStorage.getItem('show_welcome_panel') === 'true', 
                loading_submit: false,
                resource: 'configurations',
                errors: {},
                form: {},
                visuals: {},
                skins: {},
                dialogSkinsVisible: false
            }
        },
        async created() {
            await this.loadThemes();
            await this.initForm()
            await this.getRecords()
        },
        methods: {
            async loadThemes() {
                try {
                    const response = await fetch('/json/themes/themes.json');
                    this.themes = await response.json();
                } catch (error) {
                    console.error('Error loading themes:', error);
                }
            },
            updateConfig() {
                localStorage.setItem('show_welcome_panel', this.showWelcome);
                this.toggleWelcomeComponent();
            },
            toggleWelcomeComponent() {
                const welcomeComponent = document.querySelector('.welcome-component');
                if (this.showWelcome) {
                    welcomeComponent.style.display = 'block';
                } else {
                    welcomeComponent.style.display = 'none';
                }
            },
            applyTheme(theme) {
                const colors = this.themes[theme];
                if (!colors) {
                    console.error(`Theme "${theme}" not found.`);
                    return;
                }

                let styleTag = document.getElementById('theme-styles');
                if (!styleTag) {
                    styleTag = document.createElement('style');
                    styleTag.id = 'theme-styles';
                    document.head.appendChild(styleTag);
                }

                let cssString = ':root {';
                Object.keys(colors).forEach(variable => {
                    cssString += `${variable}: ${colors[variable]}; `;
                });

                cssString += '}';

                styleTag.innerHTML = cssString;
            },
            onChangeTheme(theme) {
                this.visuals.sidebar_theme = theme;
                this.submit();
                this.applyTheme(theme);
            },
            onChangeBgSidebar(theme) {
                this.visuals.sidebar_theme = theme;
                this.submit();
            },
            initForm() {
                this.errors = {}
                this.form = {
                    id: 1,
                    compact_sidebar: true,
                    colums_grid_item: 4,
                    enable_whatsapp: true,
                    phone_whatsapp: '',
                    skins: 1,
                }
            },
            async getRecords() {
                this.$http.get(`/${this.resource}/record`).then(response => {
                    if (response.data !== '') {
                        this.visuals = response.data.data.visual;
                        this.form = response.data.data;
                        this.skins = response.data.data.skins;
                        if (this.visual.sidebar_theme) {
                            this.applyTheme(this.visual.sidebar_theme)
                        }
                    }
                });
            },
            submit() {
                this.visuals.navbar = 'fixed';
                this.$http.post(`/${this.resource}/visual_settings`, this.visuals).then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                    }
                    else {
                        this.$message.error(response.data.message);
                    }
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors;
                    }
                    else {
                        console.log(error);
                    }
                }).then(() => {
                    // location.reload();
                });
            },
            submitForm() {
                this.loading_submit = true;
                this.$http.post(`/${this.resource}`, this.form).then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        location.reload()
                    }
                    else {
                        this.$message.error(response.data.message);
                    }
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors;
                    }
                    else {
                        console.log(error);
                    }
                }).then(() => {
                    this.loading_submit = false;
                });
            },
            dialogSkins() {
                this.dialogSkinsVisible = true
            },
        },
        mounted() {
            this.toggleWelcomeComponent();
        }
    }
</script>
<style scoped lang=scss>
.el-custom-control{
    display: flex;
    align-content: center;
    .btn{
        margin-right: .5rem;
        $size: 20px;
        width: $size;
        height: $size;
        border-radius: 4px;
        padding: 0;
        &.active{
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, .5);
        }
    }
}
.color-selector {
    display: flex;
    gap: 10px;
}
.color-selector button {
    width: 48px;
    height: 25px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    outline: none;
}
</style>