<template>
    <div v-loading="loading">
        <div class="page-header pr-0">
            <h2><a href="/live-app/configuration">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-device-mobile"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 5a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-14z" /><path d="M11 4h2" /><path d="M12 17v.01" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> App 3.0</span></li>
                <li class="active"><span> Configuración</span></li>
            </ol>
            <div class="right-wrapper pull-right"></div>
        </div>
        <div class="row tab-content-default row-new bg-transparent">
            <div class="col-md-7">

                <div class="row">
                    <div class="short-div col-md-12">
                        <tenant-mobile-app-permissions></tenant-mobile-app-permissions>
                    </div>
                </div>

            </div>
            <div class="w-auto ml-4">
                <video class="iphone-x" autoplay="" loop="" muted="" playsinline="" width="350" style="height:620px">
                    <source src="https://facturaloperu.com/video/FacturaloPeru-APPPremium.mp4" type="video/mp4">
                </video>
            </div>

        </div>
    </div>
</template>

<style scoped lang="scss">
.iphone-x {
  position: relative;
  margin: 25px auto;
  min-width: 330px;
  height: 750px;
  background-color: #ccc;
  border-radius: 40px;
  box-shadow: 0px 0px 0px 7px #1f1f1f, 0px 0px 0px 13px #191919,
    0px 0px 0px 14px #111;

  &:before,
  &:after {
    content: "";
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
  }

  // frontal camera/speaker frame
  &:before {
    top: 0px;
    width: 56%;
    height: 30px;
    background-color: #1f1f1f;
    border-radius: 0px 0px 40px 40px;
  }

  // speaker
  i {
    top: 0px;
    left: 50%;
    transform: translate(-50%, 6px);
    height: 8px;
    width: 15%;
    background-color: #101010;
    border-radius: 8px;
    box-shadow: inset 0px -3px 3px 0px rgba(256, 256, 256, 0.2);
  }

  // camera
  b {
    left: 10%;
    top: 0px;
    transform: translate(180px, 4px);
    width: 12px;
    height: 12px;
    background-color: #101010;
    border-radius: 12px;
    box-shadow: inset 0px -3px 2px 0px rgba(256, 256, 256, 0.2);

    &:after {
      content: "";
      position: absolute;
      background-color: #2d4d76;
      width: 6px;
      height: 6px;
      top: 2px;
      left: 2px;
      top: 3px;
      left: 3px;
      display: block;
      border-radius: 4px;
      box-shadow: inset 0px -2px 2px rgba(0, 0, 0, 0.5);
    }
  }
}
.box_color_shade_dark {
    background-color: #000000;
}
.box_color_tint_dark {
    background-color: #141414;
}
.box_color_dark {
    background-color: #1A1F1D;
}
.box_color_shade_blue {
    background-color: #331B83;
}
.box_color_tint_blue {
    background-color: #4A2CB3;
}
.box_color_blue {
    background-color: #3827B4;
}
.box_color_shade_red {
    background-color: #CC0D00;
}
.box_color_tint_red {
    background-color: #A30B00;
}
.box_color_red {
    background-color: #cb2027;
}
</style>

<script>
    export default {
        data() {
            return {
                form: {},
                domain: window.location.origin,
                path_app: window.location.origin + '/live-app',
                path_premium: window.location.origin + '/live-app/premium',
                source_iframe: '',
                loading_submit: false,
                resource: 'app-configurations',
                loading: false,
                change_mode: false,
                show_premium: false,
                loading_iframe: true,
            }
        },
        async created(){
            await this.initForm()
            await this.getRecord()
        },
        mounted(){
            this.checkConfiguration()
        },
        methods: {
            handleClick(tab, event) {
                if(tab.index == 4) {
                    this.show_premium = true
                } else {
                    this.show_premium = false
                }
                // console.log(tab, event);
            },
            async getRecord(){

                this.loading = true

                await this.$http.get(`/${this.resource}/record`)
                        .then(response => {
                            this.form = response.data.data
                            // console.log(response.data.data)
                        })
                        .then(()=>{
                            this.loading = false
                        })

            },
            initForm(){

                this.form = {
                    theme_color: 'blue',
                    card_color: 'multicolored',
                    header_waves: false,
                    app_mode: 'default',
                    direct_send_documents_whatsapp: false,
                    // operation_type: 1,
                    // permissions: {},
                }
                this.source_iframe = this.path_app

            },
            changeMode()
            {
                this.change_mode = true
            },
            changeThemePrimary() {

                let iframe = this.$refs.appIframe
                let doc = iframe.contentDocument

                switch (this.form.theme_color) {
                    case 'red':
                        doc.head.querySelectorAll('[href="' + this.domain + '/liveapp/assets/skin-dark.css"]').forEach(el => el.remove())
                        doc.head.innerHTML = doc.head.innerHTML + '<link href="' + this.domain + '/liveapp/assets/skin-red.css" rel="stylesheet">'
                        break
                    case 'dark':
                        doc.head.querySelectorAll('[href="' + this.domain + '/liveapp/assets/skin-red.css"]').forEach(el => el.remove())
                        doc.head.innerHTML = doc.head.innerHTML + '<link href="' + this.domain + '/liveapp/assets/skin-dark.css" rel="stylesheet">'
                        break
                    default:
                        doc.head.querySelectorAll('[href="' + this.domain + '/liveapp/assets/skin-dark.css"]').forEach(el => el.remove())
                        doc.head.querySelectorAll('[href="' + this.domain + '/liveapp/assets/skin-red.css"]').forEach(el => el.remove())
                        break
                }

            },
            changeThemeCards() {
                let iframe = this.$refs.appIframe
                let doc = iframe.contentDocument

                switch (this.form.card_color) {
                    case 'unicolor':
                        doc.head.innerHTML = doc.head.innerHTML + '<link href="' + this.domain + '/liveapp/assets/cards.css" rel="stylesheet">'
                        break
                    default:
                        doc.head.querySelectorAll('[href="' + this.domain + '/liveapp/assets/cards.css"]').forEach(el => el.remove())
                        break
                }
            },
            changeHeaderWaves() {
                let iframe = this.$refs.appIframe
                let doc = iframe.contentDocument
                let waves = doc.body.querySelectorAll('div.waves')

                switch (this.form.header_waves) {
                    case 0:
                        waves.forEach(el => {
                            el.parentNode.classList.remove('display-flex')
                            el.parentNode.classList.add('display-none')
                        })
                        break
                    default:
                        waves.forEach(el => {
                            el.parentNode.classList.add('display-flex')
                            el.parentNode.classList.remove('display-none')
                        })
                        break
                }
            },
            async checkConfiguration(){

                const iframe = this.$refs.appIframe
                const context = this

                await iframe.addEventListener('load', function() {
                    context.changeThemePrimary()
                    context.changeThemeCards()
                    context.changeHeaderWaves()
                })

            },
            async submit() {

                this.loading_submit = true
                await this.$http.post(`/${this.resource}`, this.form)
                    .then(response => {
                        if (response.data.success) {

                            this.$message.success(response.data.message)
                            // if(this.change_mode) location.reload() //reinicia el modo pos, pero valida la configuracion con la url que se inicia, por ejemplo demo, no del proyecto local

                        } else {
                            this.$message.error(response.data.message)
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.errors = error.response.data
                        } else {
                            console.log(error)
                        }
                    })
                    .then(() => {
                        this.loading_submit = false
                    })
            }
        }
    }
</script>
