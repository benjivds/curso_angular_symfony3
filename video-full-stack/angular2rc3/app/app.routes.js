"use strict";
/**
 * Created by Tu Lugar Favorito on 14/01/2017.
 */
var router_1 = require('@angular/router');
var login_component_1 = require("./component/login.component");
var register_component_1 = require("./component/register.component");
var default_component_1 = require("./component/default.component");
exports.routes = [
    {
        path: '',
        redirectTo: '/index',
        terminal: true
    },
    { path: 'index', component: default_component_1.DefaultComponent },
    { path: 'login', component: login_component_1.LoginComponent },
    { path: 'register', component: register_component_1.RegisterComponent },
];
exports.APP_ROUTER_PROVIDERS = [
    router_1.provideRouter(exports.routes)
];
//# sourceMappingURL=app.routes.js.map