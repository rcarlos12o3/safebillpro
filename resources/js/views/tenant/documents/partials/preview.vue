<template>
    <div>
      <el-dialog
        :visible="showDialog"
        @open="loadPDF"
        :close-on-click-modal="false"
        :close-on-press-escape="true"
        width="100%"
        :fullscreen="true"
        :show-close="false"
        custom-class="d-own"
      >
        <div
          style="
            width: 100%;
            height: 100%;
            display: grid;
            grid-template-rows: auto 1fr auto;
          "
        >
          <div class="row">
            <div class="col-6 mb-2">
              <el-select
                v-model="format"
                class="border-left rounded-left border-info"
                dusk="document_type_id"
                popper-class="el-select-document_type"
                @change="changeFormat"
              >
                <el-option value="a4" label="A4"> </el-option>
                <el-option v-if="ShowTicket80" value="ticket" label="80MM">
                </el-option>
                <el-option v-if="ShowTicket58" value="ticket_58" label="58MM">
                </el-option>
                <el-option v-if="ShowTicket50" value="ticket_50" label="50MM">
                </el-option>
                <el-option value="a5" label="A5"> </el-option>
              </el-select>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 container-tabs">
              <iframe
                :src="URL"
                type="application/pdf"
                width="100%"
                height="100%"
              />
            </div>
          </div>
          <div class="col-md-6 mt-2">
            <el-button @click="clickClose" class="btn-primary">Cerrar</el-button>
          </div>
        </div>
      </el-dialog>
    </div>
  </template>
  
  <script>
  import { mapState } from "vuex/dist/vuex.mjs";
  export default {
    props: ["showDialog", "preview"],
    data() {
      return {
        format: "a4",
        URL: null,
        URLs: [],
      };
    },
    computed: {
      ...mapState(["config"]),
      ShowTicket58: function () {
        if (this.config === undefined) return false;
        if (this.config == null) return false;
        if (this.config.show_ticket_58 === undefined) return false;
        if (this.config.show_ticket_58 == null) return false;
        if (
          this.config.show_ticket_58 !== undefined &&
          this.config.show_ticket_58 !== null
        ) {
          return this.config.show_ticket_58;
        }
        return false;
      },
      ShowTicket80: function () {
        if (this.config === undefined) return false;
        if (this.config == null) return false;
        if (this.config.show_ticket_80 === undefined) return false;
        if (this.config.show_ticket_80 == null) return false;
        if (
          this.config.show_ticket_80 !== undefined &&
          this.config.show_ticket_80 !== null
        ) {
          return this.config.show_ticket_80;
        }
        return false;
      },
      ShowTicket50: function () {
        if (this.config === undefined) return false;
        if (this.config == null) return false;
        if (this.config.show_ticket_50 === undefined) return false;
        if (this.config.show_ticket_50 == null) return false;
        if (
          this.config.show_ticket_50 !== undefined &&
          this.config.show_ticket_50 !== null
        ) {
          return this.config.show_ticket_50;
        }
        return false;
      },
    },
    methods: {
      async loadPDF() {
        this.URL = null;
        let URLExists = this.URLs.find((x) => x.format === this.format);
  
        if (URLExists) {
          this.URL = URLExists.URL;
        } else {
          this.URL = await this.preview(this.format);
          this.URLs = [...this.URLs, { format: this.format, URL: this.URL }];
        }
      },
      clickClose() {
        this.$emit("update:showDialog", false);
        this.URLs = [];
        this.URL = null;
      },
      async changeFormat() {
        await this.loadPDF();
      },
    },
  };
  </script>
  
  <style>
  .d-own > .el-dialog__header {
    display: none;
  }
  .d-own > .el-dialog__body {
    height: 100vh;
  }
  </style>
  