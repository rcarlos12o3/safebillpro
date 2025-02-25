
 <template>
  <div class="col-lg-6 col-md-12">
    <div class="card">
      <div class="card-header bg-info">
        <h3 class="my-0">Redes Sociales</h3>
      </div>
      <div class="card-body">
        <form autocomplete="off" @submit.prevent="submit">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group" :class="{'has-danger': errors.link_facebook}">
                  <label class="control-label">Link Facebook</label>
                  <el-input v-model="form.link_facebook"></el-input>
                  <small
                    class="form-control-feedback"
                    v-if="errors.link_facebook"
                    v-text="errors.link_facebook[0]"
                  ></small>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group" :class="{'has-danger': errors.link_youtube}">
                  <label class="control-label">Link YouTube</label>
                  <el-input v-model="form.link_youtube"></el-input>
                  <small
                    class="form-control-feedback"
                    v-if="errors.link_youtube"
                    v-text="errors.link_youtube[0]"
                  ></small>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group" :class="{'has-danger': errors.link_tiktok}">
                  <label class="control-label">Link TikTok</label>
                  <el-input v-model="form.link_tiktok"></el-input>
                  <small
                    class="form-control-feedback"
                    v-if="errors.link_tiktok"
                    v-text="errors.link_tiktok[0]"
                  ></small>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group" :class="{'has-danger': errors.link_instagram}">
                  <label class="control-label">Link Instagram</label>
                  <el-input v-model="form.link_instagram"></el-input>
                  <small
                    class="form-control-feedback"
                    v-if="errors.link_instagram"
                    v-text="errors.link_instagram[0]"
                  ></small>
                </div>
              </div>
            </div>
          </div>
          <div class="form-actions text-right pt-2">
            <el-button type="primary" native-type="submit" :loading="loading_submit">Guardar</el-button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>




<script>
export default {
  data() {
    return {
      loading_submit: false,
      // headers: headers_token,
      resource: "ecommerce",
      errors: {},
      form: {},
      soap_sends: [],
      soap_types: []
    };
  },
  async created() {
    await this.initForm();

    await this.$http.get(`/${this.resource}/record`).then(response => {
      if (response.data !== "") {
        let data = response.data.data;
        this.form.id = data.id;
        this.form.link_youtube = data.link_youtube;
        this.form.link_facebook = data.link_facebook;
        this.form.link_twitter = data.link_twitter;
        this.form.link_tiktok = data.link_tiktok;
        this.form.link_instagram = data.link_instagram;
      }
    });
  },
  methods: {
    initForm() {
      this.errors = {};
      this.form = {
        id: null,
        link_twitter: "",
        link_youtube: "",
        link_facebook: "",
        link_tiktok: "",
        link_instagram: ""
      };
    },
    submit() {
      this.loading_submit = true;
      this.$http
        .post(`/${this.resource}/configuration_social`, this.form)
        .then(response => {
          if (response.data.success) {
            this.$message.success(response.data.message);
          } else {
            this.$message.error(response.data.message);
          }
        })
        .catch(error => {
          if (error.response.status === 422) {
            this.errors = error.response.data;
          } else {
            console.log(error);
          }
        })
        .then(() => {
          this.loading_submit = false;
        });
    }
  }
};
</script>

