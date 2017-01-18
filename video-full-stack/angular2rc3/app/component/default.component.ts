/**
 * Created by Tu Lugar Favorito on 14/01/2017.
 */
// Importar el núcleo de Angular
import {Component} from '@angular/core';

// Decorador component, indicamos en que etiqueta se va a cargar la

@Component({
    selector: 'default',
    template: '<h1>Componente por Default</h1>'
})

// Clase del componente donde irán los datos y funcionalidades
export class DefaultComponent { }
