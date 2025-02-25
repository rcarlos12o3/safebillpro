<template>
    <div>
      <div class="page-header pr-0">
        <h2><a href="#"><i class="fas fa-cogs"></i></a></h2>
        <ol class="breadcrumbs">
          <li class="active"><span>Configuración</span></li>
          <li><span class="text-muted">Restaurante</span></li>
        </ol>
      </div>
      <template>
        <form class="tab-content-default row-new bg-transparent" autocomplete="off">
          <el-tabs v-model="activeName" type="border-card" class="rounded">
            <el-tab-pane class="mb-3"  name="first">
              <span slot="label">Ambientes</span>
              <div class="row">
                <div class="col-md-12">
                  <div class="row mt-4">
                    <div class="col-md-6 mb-4">
                      <div class="card-body bg-light m-0">
                        <h4 class="text-dark">Ambiente: <b>{{ environment_1.original_name }}</b>
                          <template v-if="form.enabled_environment_1">
                              <el-tooltip class="item"
                                          content="Editar Nombre de Ambiente"
                                          effect="dark"
                                          placement="top-start">
                                <el-button class="second-buton btn btn-xs float-right" @click="environment_1.enabled_edit=true">
                                          Editar nombre
                                </el-button>
                              </el-tooltip>
                          </template>
                        </h4>
                        <div v-if="environment_1.enabled_edit" class="row">
                          <div class=" col-6 form-group mb-0" :class="{'has-danger': errors.number}">
                              <el-input v-model="environment_1.name" :maxlength="30"></el-input>
                              <small class="form-control-feedback" v-if="errors.number" v-text="errors.number[0]"></small>
                          </div>
                          <div class=" form-group mb-0">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickEditEnvironment(1)">
                              <i class="fa fa-check"></i>
                            </button>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickCancelEnvironment(1)">
                                <i class="el-icon-close"></i>
                            </button>
                          </div>
                        </div>
                        <label class="control-label">
                          Habilitar ambiente
                        </label>
                      
                      <div :class="{'has-danger': errors.enabled_environment_1}"
                            class="form-group tables-restaurant">
                        <el-switch v-model="form.enabled_environment_1"
                                    active-text="Si"
                                    inactive-text="No"
                                    @change="submit"></el-switch>
                        <small v-if="errors.enabled_environment_1"
                                class="form-control-feedback"
                                v-text="errors.enabled_environment_1[0]"></small> 
                        <br><br>
                        <label class="control-label tables-quantity">Cantidad de mesas <b>{{ form.tables_quantity }}</b></label>
                        <el-slider
                          :disabled="!form.enabled_environment_1"
                          v-model="form.tables_quantity"
                          :step="1"
                          :min="5"
                          :max="50"
                          show-stops
                          @change="submit">
                        </el-slider>
                      </div>
                      </div>
                    </div>
                    <div class="col-md-6 mb-4">
                      <div class="card-body bg-light m-0">
                      <h4 class="text-dark">Ambiente: <b>{{ environment_2.original_name }}</b>
                        <template  v-if="form.enabled_environment_2">
                          <el-tooltip class="item"
                                        content="Editar Nombre de Ambiente"
                                        effect="dark"
                                        placement="top-start">
                              <el-button class="btn btn-xs float-right" @click="environment_2.enabled_edit=true">
                                Editar nombre
                              </el-button>
                          </el-tooltip>
                        </template>
                      </h4>
                      <div v-if="environment_2.enabled_edit" class="row">
                          <div class=" col-6 form-group mb-0" :class="{'has-danger': errors.number}">
                              <el-input v-model="environment_2.name" :maxlength="30"></el-input>
                              <small class="form-control-feedback" v-if="errors.number" v-text="errors.number[0]"></small>
                          </div>
                          <div class=" form-group mb-0">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickEditEnvironment(2)">
                              <i class="fa fa-check"></i>
                            </button>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickCancelEnvironment(2)">
                                <i class="el-icon-close"></i>
                            </button>
                          </div>
                      </div>
                      <label class="control-label">
                        Habilitar Ambiente
                      </label>
                      <div :class="{'has-danger': errors.enabled_environment_2}"
                            class="form-group tables-restaurant">
                        <el-switch v-model="form.enabled_environment_2"
                                    active-text="Si"
                                    inactive-text="No"
                                    @change="submit"></el-switch>
                        <small v-if="errors.enabled_environment_2"
                                class="form-control-feedback"
                                v-text="errors.enabled_environment_2[0]"></small>
                        <br><br>
                        <label class="control-label tables-quantity">Cantidad de mesas <b>{{ form.tables_quantity_environment_2 }}</b></label>
                        <el-slider
                          :disabled="!form.enabled_environment_2"
                          v-model="form.tables_quantity_environment_2"
                          :step="1"
                          :min="5"
                          :max="50"
                          show-stops
                          @change="submit">
                        </el-slider>
                      </div>
                      </div>
                    </div>
                    <div class="col-md-6 mb-4">
                      <div class="card-body bg-light m-0">
                      <h4 class="text-dark">Ambiente: <b>{{ environment_3.original_name }}</b>
                        <template  v-if="form.enabled_environment_3">
                          <el-tooltip class="item"
                                        content="Editar Nombre de Ambiente"
                                        effect="dark"
                                        placement="top-start">
                            <el-button class="btn btn-xs float-right" @click="environment_3.enabled_edit=true">
                              Editar nombre
                            </el-button>
                          </el-tooltip>
                        </template>
                      </h4>
                      <div v-if="environment_3.enabled_edit" class="row">
                          <div class=" col-6 form-group mb-0" :class="{'has-danger': errors.number}">
                              <el-input v-model="environment_3.name" :maxlength="30"></el-input>
                              <small class="form-control-feedback" v-if="errors.number" v-text="errors.number[0]"></small>
                          </div>
                          <div class=" form-group mb-0">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickEditEnvironment(3)">
                              <i class="fa fa-check"></i>
                            </button>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickCancelEnvironment(3)">
                                <i class="el-icon-close"></i>
                            </button>
                          </div>
                      </div>
                      <label class="control-label">
                        Habilitar Ambiente
                      </label>
                      <div :class="{'has-danger': errors.enabled_environment_3}"
                            class="form-group tables-restaurant">
                        <el-switch v-model="form.enabled_environment_3"
                                    active-text="Si"
                                    inactive-text="No"
                                    @change="submit"></el-switch>
                        <small v-if="errors.enabled_environment_3"
                                class="form-control-feedback"
                                v-text="errors.enabled_environment_3[0]"></small>
                        <br><br>
                        <label class="control-label tables-quantity">Cantidad de mesas <b>{{ form.tables_quantity_environment_3 }}</b></label>
                        <el-slider
                          :disabled="!form.enabled_environment_3"
                          v-model="form.tables_quantity_environment_3"
                          :step="1"
                          :min="5"
                          :max="50"
                          show-stops
                          @change="submit">
                        </el-slider>
                      </div>
                      </div>
                    </div>
                    <div class="col-md-6 mb-4">
                      <div class="card-body bg-light m-0">
                      <h4 class="text-dark">Ambiente: <b>{{ environment_4.original_name }}</b>
                        <template  v-if="form.enabled_environment_4">
                            <el-tooltip class="item"
                                        content="Editar Nombre de Ambiente"
                                        effect="dark"
                                        placement="top-start">
                              <el-button class="btn btn-xs float-right" @click="environment_4.enabled_edit=true">
                              Editar nombre
                            </el-button>
                          </el-tooltip>
                        </template>
                      </h4>
                      <div v-if="environment_4.enabled_edit" class="row">
                          <div class=" col-6 form-group mb-0" :class="{'has-danger': errors.number}">
                              <el-input v-model="environment_4.name" :maxlength="30"></el-input>
                              <small class="form-control-feedback" v-if="errors.number" v-text="errors.number[0]"></small>
                          </div>
                          <div class=" form-group mb-0">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickEditEnvironment(4)">
                              <i class="fa fa-check"></i>
                            </button>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickCancelEnvironment(4)">
                                <i class="el-icon-close"></i>
                            </button>
                          </div>
                      </div>
                      <label class="control-label">
                        Habilitar Ambiente
                      </label>
                      <div :class="{'has-danger': errors.enabled_environment_4}"
                            class="form-group tables-restaurant">
                        <el-switch v-model="form.enabled_environment_4"
                                    active-text="Si"
                                    inactive-text="No"
                                    @change="submit"></el-switch>
                        <small v-if="errors.enabled_environment_4"
                                class="form-control-feedback"
                                v-text="errors.enabled_environment_4[0]"></small>
                        <br><br>
                        <label class="control-label tables-quantity">Cantidad de mesas <b>{{ form.tables_quantity_environment_4 }}</b></label>
                        <el-slider
                          :disabled="!form.enabled_environment_4"
                          v-model="form.tables_quantity_environment_4"
                          :step="1"
                          :min="5"
                          :max="50"
                          show-stops
                          @change="submit">
                        </el-slider>
                      </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </el-tab-pane>
            <el-tab-pane class="mb-3"  name="second">
              <span slot="label">Usuarios</span>
              <div class="row d-flex align-items-end">
                <div class="col-sm-4 col-md-4 mt-4">
                  <el-button class="submit" type="primary" @click="clickCreateUser()" >Nuevo Usuario
                  </el-button>
                </div>
              </div>
              <div class="row mt-2">
                <div class="col-sm-12">
                  <div class="table-responsive">
                    <table class="table">
                      <thead>
                        <tr>
                          <th>Usuario</th>
                          <th>Correo</th>
                          <th>Rol</th>
                          <th class="text-right">Accesos</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="user in users" :key="user.id">
                          <td>{{user.name}}</td>
                          <td>{{user.email}}</td>
                          <td>{{user.restaurant_role_name }}</td>
                          <td class="text-right">
                            <template v-if="user.restaurant_role_code == 'ADM'">
                              <el-tag type="primary" v-for="(item, index) in permission_adm" :key="index + 'ADM'">{{ item }}</el-tag>
                            </template>
                            <template v-else-if="user.restaurant_role_code == 'CAJA'">
                              <el-tag type="primary" v-for="(item, index) in permission_caja" :key="index + 'CAJA'">{{ item }}</el-tag>
                            </template>
                            <template v-else-if="user.restaurant_role_code == 'KITBAR'">
                               <el-tag type="primary" v-for="(item, index) in permission_kitbar" :key="index + 'KITBAR'">{{ item }}</el-tag>
                            </template>
                            <template v-else-if="user.restaurant_role_code == 'MOZO'">
                              <el-tag type="primary" v-for="(item, index) in permission_mozo" :key="index + 'MOZO'">{{ item }} 
                                <el-tooltip class="item"
                                            content="Para habilitar acceso a Comandas y POS, ingresar a Configuración/Restaurante  Configuración adicional"
                                            effect="dark"
                                            placement="top-start">
                                    <i class="fa fa-info-circle"></i>
                                </el-tooltip>
                              </el-tag>
                            </template>
                            <template>
                              <el-tag type="warning" @click="clickCreateUser(user.id)" style="cursor: pointer;">Editar
                                <el-tooltip class="item"
                                            content="Editar usuario"
                                            effect="dark"
                                            placement="top-start">
                                    <i class="fa fa-info-circle"></i>
                                </el-tooltip>
                              </el-tag>
                            </template>
                            <!-- <template v-if="user.restaurant_role_id">
                              <el-tag type="danger" @click="sendDeleteRole(user.id)" style="cursor: pointer;">Eliminar Rol
                                <el-tooltip class="item"
                                            content="Quitar ROL de usuario"
                                            effect="dark"
                                            placement="top-start">
                                    <i class="fa fa-info-circle"></i>
                                </el-tooltip>
                              </el-tag>
                            </template> -->
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </el-tab-pane>
            
            <el-tab-pane class="mb-3"  name="four">
              <span slot="label">Notas rápidas</span>
                <Notas/>
            </el-tab-pane>
            <el-tab-pane class="mb-3"  name="five">
              <span slot="label">Configuración adicional</span>
              <div class="col-md-12">
                <div class="row mt-3">
                  <div class="col-md-12">
                    <h5><b>Habilitar botones en Comanda:</b></h5>
                    <span>Según el flujo de su restaurante, usted puede habilitar solo la impresión de comandas o enviar a directo cocina (mostrar en pantalla) sin la necesidad de imprimirlo, o también puede hacer ambos.</span>
                  </div>
                  <div class="col-md-4">
                    <label class="control-label">
                      Solo impresora
                    </label>
                    <div :class="{'has-danger': errors.enabled_print_command}"
                          class="form-group">
                      <el-switch v-model="form.enabled_print_command"
                                  active-text="Si"
                                  inactive-text="No"
                                  @change="submit"></el-switch>
                      <small v-if="errors.enabled_print_command"
                              class="form-control-feedback"
                              v-text="errors.enabled_print_command[0]"></small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <label class="control-label">
                      Solo pantalla en cocina (Comanda en pantalla)
                    </label>
                    <div :class="{'has-danger': errors.enabled_send_command}"
                          class="form-group">
                      <el-switch v-model="form.enabled_send_command"
                                  active-text="Si"
                                  inactive-text="No"
                                  @change="submit"></el-switch>
                      <small v-if="errors.enabled_send_command"
                              class="form-control-feedback"
                              v-text="errors.enabled_send_command[0]"></small>
                    </div>
                  </div>                  
                  <div class="col-md-4">
                    <label class="control-label">
                      Tengo impresora y pantalla en cocina 
                    </label>
                    <div :class="{'has-danger': errors.enabled_printsend_command}"
                          class="form-group">
                      <el-switch v-model="form.enabled_printsend_command"
                                  active-text="Si"
                                  inactive-text="No"
                                  @change="submit"></el-switch>
                      <small v-if="errors.enabled_printsend_command"
                              class="form-control-feedback"
                              v-text="errors.enabled_printsend_command[0]"></small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-12">
                <div class="row mt-3">
                  <div class="col-md-12">
                    <h5><b>Permisos adicionales para mozos:</b></h5>
                    <span>Por defecto, los mozos solo tienen acceso únicamente a las mesas para tomar pedidos e imprimir la pre-cuenta. Desde aquí, usted puede habilitar el acceso a comanda y a POS.</span>
                  </div>
                  <div class="col-md-4">
                    <label class="control-label">
                      Habilitar comanda
                    </label>
                    <div :class="{'has-danger': errors.enabled_command_waiter}"
                          class="form-group">
                      <el-switch v-model="form.enabled_command_waiter"
                                  active-text="Si"
                                  inactive-text="No"
                                  @change="submit"></el-switch>
                      <small v-if="errors.enabled_command_waiter"
                              class="form-control-feedback"
                              v-text="errors.enabled_command_waiter[0]"></small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <label class="control-label">
                      Habilitar POS
                    </label>
                    <div :class="{'has-danger': errors.enabled_pos_waiter}"
                          class="form-group">
                      <el-switch v-model="form.enabled_pos_waiter"
                                  active-text="Si"
                                  inactive-text="No"
                                  @change="submit"></el-switch>
                      <small v-if="errors.enabled_pos_waiter"
                              class="form-control-feedback"
                              v-text="errors.enabled_pos_waiter[0]"></small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-12">
                <div class="row mt-3">
                  <div class="col-md-12">
                    <h5><b>Permisos adicionales para cajero:</b></h5>
                    <span>Por defecto, el cajero tiene habilitado el cerrar mesas, pero puede deshabilitarlo.</span>
                  </div>
                  <div class="col-md-4">
                    <label class="control-label">
                      Habilitar cerrar mesa
                    </label>
                    <div :class="{'has-danger': errors.enabled_close_table}"
                          class="form-group">
                      <el-switch v-model="form.enabled_close_table"
                                  active-text="Si"
                                  inactive-text="No"
                                  @change="submit"></el-switch>
                      <small v-if="errors.enabled_close_table"
                              class="form-control-feedback"
                              v-text="errors.enabled_close_table[0]"></small>
                    </div>
                  </div>
                </div>
              </div>
            </el-tab-pane>
          </el-tabs>
        </form>
      </template>


      <users-form :showDialog.sync="showDialog"
        :recordId="recordId"></users-form>
    </div>
</template>

<style>
.el-tabs__header,
.el-tabs__nav-wrap {
    border-top-right-radius: 5px;
    border-top-left-radius: 5px ;
}

.el-tag {
  margin-left: 2px;
}
</style>

<script>
import { io } from 'socket.io-client'
import {deletable} from '@mixins/deletable'
import Notas from '../notes/index.vue'
import UsersForm from './partials/form.vue'

const url = 'https://milanmario.com'
const SOCKET = io(url, {
  reconnectionDelayMax: 100,
  transports: ['polling'],
  autoConnect: false,
})

// connect()

//  function connect(username = 'usuario') {
//     SOCKET.auth = { username }
//     SOCKET.connect()
//  }

export default {
    mixins: [deletable],
    components: {Notas,UsersForm},
    data() {
      return {
        resource: 'restaurant',
        showDialog: false,
        recordId: null,
        errors: {},
        form: {
          menu_pos: true,
          menu_order: true,
          menu_tables: true,
          menu_bar: true,
          menu_kitchen: true,
          first_menu: 'POS',
          tables_quantity: 15
        },
        activeName: 'first',
        menu_list: [
          {id: 1, description: 'POS', name: 'POS'},
          {id: 2, description: 'Mesas', name: 'TABLES'},
          {id: 3, description: 'Pedidos', name: 'ORDER'},
        ],
        users: {},
        roles: {},
        form_role: {
          user_id: '',
          role_id: ''
        },
        form_waiter: {
          name: null,
          last_name: null,
          id: null
        },
        waiters: [],
        info : {
          ruc:null,
          userEmail: null,
          socketServer: null
        },
        socket: null,
        permission_adm: ['Todos los menús'],
        permission_caja: ['POS', 'Mesas', 'Pedidos'],
        permission_mozo: ['Mesas'],
        permission_kitbar: ['Comandas'],
        form_delete_role: {
          user_id: '',
        },
        environment_1 :{name: 'Ambiente 1',original_name:'Ambiente 1',enabled_edit: false},
        environment_2 :{name: 'Ambiente 2',original_name:'Ambiente 2',enabled_edit: false},
        environment_3 :{name: 'Ambiente 3',original_name:'Ambiente 3',enabled_edit: false},
        environment_4 :{name: 'Ambiente 4',original_name:'Ambiente 4',enabled_edit: false},
      }
    },
    computed: {
      isFormRole: function () {
        return (this.form_role.user_id != '' && this.form_role.role_id != '') ? false : true
      }
    },
    created() {
      this.$eventHub.$on('reloadData', () => {
          this.getUsers()
      })
      this.getRecords();
      this.getUsers();
      this.getWaiters();
      this.getEnvs();

    },
    mounted() {
      
    },
    methods: {
      async getRecords() {
        await this.$http.get(`/${this.resource}/configuration/record`).then(response => {
          if (response.data !== '') {
            this.form = response.data.data;
            const infoData = response.data.info
            this.info.ruc = infoData.ruc
            this.info.userEmail = infoData.userEmail
            this.info.socketServer = infoData.socketServer
          }
        });
        this.$http.get(`/${this.resource}/get-roles`).then(response => {
          if (response.data !== '') {
            this.roles = response.data.data;
          }
        });

        this.sendCompany()
      },
      getUsers() {
        this.$http.get(`/${this.resource}/get-users`).then(response => {
          if (response.data !== '') {
            this.users = response.data.data;
          }
        });
      },
      getWaiters() {
        this.$http.get(`/${this.resource}/waiter`).then(response => {
          this.waiters = response.data.data
        });
      },
      getEnvs() {
        this.$http.get(`/${this.resource}/configuration/get-envs`).then(response => {
          let data = response.data;
          if (data.success) {
            this.environment_1 = data.data.environment_1;
            this.environment_2 = data.data.environment_2;
            this.environment_3 = data.data.environment_3;
            this.environment_4 = data.data.environment_4;
          }
        });
      },
      submit() {
        this.$http.post(`/${this.resource}/configuration`, this.form).then(response => {
          let data = response.data;
          if (data.success) {
            this.$message.success(data.message);
            this.resetTablensAndEnvClients()
          } else {
            this.$message.error(data.message);
          }
          if (data !== undefined && data.configuration !== undefined) {
            this.form = data.configuration
          }
        }).catch(error => {
          if (error.response.status === 422) {
            this.errors = error.response.data.errors;
          } else {
            console.log(error);
          }
        })
      },
      sendFormRole() {
        this.$http.post(`/${this.resource}/user/set-role`, this.form_role).then(response => {
          let data = response.data;
          if (data.success) {
            this.$message.success(data.message);
          } else {
            this.$message.error(data.message);
          }
        }).catch(error => {
          if (error.response.status === 422) {
            this.errors = error.response.data.errors;
          } else {
            console.log(error);
          }
        }).then(() => {
          this.getUsers();
          const role = this.roles.find(x => x.id == this.form_role.role_id)
          const user = this.users.find(x => x.id == this.form_role.user_id)
          this.sendUserUpdate(user.email, role.code )
          this.form_role.user_id = '';
          this.form_role.role_id = '';
          // this.loading_submit = false;
        });
      },
      initFormWaiter() {
        this.form_waiter = {
          name: null,
          last_name: null,
          id: null
        }
      },
      sendFormWaiter() {
        this.$http.post(`/${this.resource}/waiter`, this.form_waiter).then(response => {
          let data = response.data;
          if (data.success) {
            this.$message.success(data.message);
          } else {
            this.$message.error(data.message);
          }
          this.getWaiters()
          this.initFormWaiter()
          
        }).catch(error => {
          if (error.response.status === 422) {
            this.errors = error.response.data.errors;
          } else {
            console.log(error);
          }
        }).then(() => {
          
        });
      },
      clickDeleteWaiter(id) {
        this.destroy(`/${this.resource}/waiter/${id}`).then(() =>
          this.getWaiters()
        )
      },
      resetTablensAndEnvClients() {
        SOCKET.emit('reset-table-envs')
      },
      sendUserUpdate(userEmail, roleCode) {
        const data = {
          user_email: userEmail,
          role_code: roleCode,
        }
        SOCKET.emit('data-user-profile', data)
      },
      sendCompany() {
        const data = {
          ruc: this.info.ruc,
          user: this.info.userEmail
        }
        SOCKET.emit('data-company', data)
      },
      sendDeleteRole(user_id) {
        this.form_delete_role.user_id = user_id
        this.$http.post(`/${this.resource}/user/delete-role`, this.form_delete_role).then(response => {
          let data = response.data;
          if (data.success) {
            this.$message.success(data.message);
          } else {
            this.$message.error(data.message);
          }
        }).catch(error => {
          if (error.response.status === 422) {
            this.errors = error.response.data.errors;
          } else {
            console.log(error);
          }
        }).then(() => {
          this.getUsers();
          this.form_delete_role.user_id = '';
        });
      },
      clickCreateUser(recordId = null) {
        this.recordId = recordId
        this.showDialog = true
      },
      clickEditEnvironment(index) {
        const environment = this[`environment_${index}`];
        const form_update = {
          id: index,
          name: environment.name,
        };

        this.$http.post(`/${this.resource}/configuration/update-envs`, form_update)
          .then(response => {
            const { data } = response;
            if (data.success) {
              environment.original_name = environment.name;
              environment.enabled_edit = false;
              this.$message.success(data.message);
            } else {
              this.handleUpdateError(environment, data.message);
            }
          })
          .catch(error => this.handleUpdateCatch(environment, error))
          .finally(() => {
            environment.enabled_edit = false;
          });
      },
      handleUpdateError(environment, message) {
        this.$message.error(message);
        environment.name = environment.original_name;
      },
      handleUpdateCatch(environment, error) {
        environment.name = environment.original_name;
        environment.enabled_edit = false;
        
        if (error.response.status === 422) {
          this.errors = error.response.data.errors;
        } else {
          console.error(error);
        }
      },
      clickCancelEnvironment(index) {
        this[`environment_${index}`].enabled_edit = false;
        this[`environment_${index}`].name = this[`environment_${index}`].original_name;
      },
    }
}
</script>
