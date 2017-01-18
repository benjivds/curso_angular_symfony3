/**
 * Created by Tu Lugar Favorito on 14/01/2017.
 */
import {provideRouter,RouterConfig} from '@angular/router';
import {LoginComponent} from "./component/login.component";
import {RegisterComponent} from "./component/register.component"
import {DefaultComponent} from "./component/default.component"

export const routes: RouterConfig = [
    {
        path:'',
        redirectTo: '/index',
        terminal: true
    },
    {path: 'index', component: DefaultComponent},
    {path: 'login', component: LoginComponent},
    {path: 'register', component: RegisterComponent},
];

export const APP_ROUTER_PROVIDERS = [
    provideRouter(routes)
]

