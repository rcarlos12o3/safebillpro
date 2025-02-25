
 <template>
  <div class="col-lg-6 col-md-12">
    <div class="card">
      <div class="card-header bg-info">
        <h3 class="my-0">Links personalizados para el menú</h3>
      </div>
      <div class="card-body">
        <form autocomplete="off" @submit.prevent="submit">
          <div class="form-body">
            <div class="row">
              <div class="col-md-5">
                <div class="form-group" :class="{'has-danger': errors.title_one_customised_link}">
                  <label class="control-label">Titulo 1</label>
                  <el-input v-model="form.title_one_customised_link"></el-input>
                  <small
                    class="form-control-feedback"
                    v-if="errors.title_one_customised_link"
                    v-text="errors.title_one_customised_link[0]"
                  ></small>
                </div>
              </div>
              <div class="col-md-7">
                <div class="form-group" :class="{'has-danger': errors.customised_link_one}">
                  <label class="control-label">Link 1</label>
                  <el-input v-model="form.customised_link_one"></el-input>
                  <small
                    class="form-control-feedback"
                    v-if="errors.customised_link_one"
                    v-text="errors.customised_link_one[0]"
                  ></small>
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group" :class="{'has-danger': errors.title_two_customised_link}">
                  <label class="control-label">Titulo 2</label>
                  <el-input v-model="form.title_two_customised_link"></el-input>
                  <small
                    class="form-control-feedback"
                    v-if="errors.title_two_customised_link"
                    v-text="errors.title_two_customised_link[0]"
                  ></small>
                </div>
              </div>
              <div class="col-md-7">
                <div class="form-group" :class="{'has-danger': errors.customised_link_two}">
                  <label class="control-label">Link 2</label>
                  <el-input v-model="form.customised_link_two"></el-input>
                  <small
                    class="form-control-feedback"
                    v-if="errors.customised_link_two"
                    v-text="errors.customised_link_two[0]"
                  ></small>
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group" :class="{'has-danger': errors.title_three_customised_link}">
                  <label class="control-label">Titulo 3</label>
                  <el-input v-model="form.title_three_customised_link"></el-input>
                  <small
                    class="form-control-feedback"
                    v-if="errors.title_three_customised_link"
                    v-text="errors.title_three_customised_link[0]"
                  ></small>
                </div>
              </div>
              <div class="col-md-7">
                <div class="form-group" :class="{'has-danger': errors.customised_link_three}">
                  <label class="control-label">Link 3</label>
                  <el-input v-model="form.customised_link_three"></el-input>
                  <small
                    class="form-control-feedback"
                    v-if="errors.customised_link_three"
                    v-text="errors.customised_link_three[0]"
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
        this.form.title_one_customised_link = data.title_one_customised_link;
        this.form.title_two_customised_link = data.title_two_customised_link;
        this.form.title_three_customised_link = data.title_three_customised_link;
        this.form.customised_link_one = data.customised_link_one;
        this.form.customised_link_two = data.customised_link_two;
        this.form.customised_link_three = data.customised_link_three;
      }
    });
  },
  methods: {
    initForm() {
      this.errors = {};
      this.form = {
        id: null,
        title_one_customised_link: "",
        title_two_customised_link: "",
        title_three_customised_link: "",
        customised_link_one: "",
        customised_link_two: "",
        customised_link_three: "",
      };
    },
    submit() {
      this.loading_submit = true;
      this.$http
        .post(`/${this.resource}/configuration_links`, this.form)
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

