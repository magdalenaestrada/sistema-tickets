# Sistema de Venta de Boletos de Autobús

Este proyecto es un sistema de venta de boletos de autobús.

## Características Principales

*   Gestión de múltiples sucursales.
*   Gestión de autobuses.
*   Venta de boletos.

## Tecnologías

*   **Backend:** Laravel 12 (PHP 8.2 - 8.4)
*   **Frontend:** JavaScript vainilla con módulos de jQuery
*   **Base de datos:** MySQL
*   **Gestor de paquetes de Node:** pnpm
*   **Roles y Permisos:** spatie/laravel-permission

## Estructura de Datos

La arquitectura de datos se organiza en 3 niveles jerárquicos: Empresa -> Sucursal -> Usuario.

*   **Empresa**: Es la entidad fiscal principal. Contiene datos únicos como el RUC, razón social y dirección principal.
*   **Sucursales**: Cada sucursal pertenece a una empresa. Hereda los datos fiscales pero tiene su propio nombre comercial, dirección y ubicación geográfica (distrito).
*   **Usuarios**: Cada usuario (empleado) está asignado a una única sucursal.
*   **Ubicaciones**: Se mantiene una estructura geográfica normalizada (País -> Departamento -> Provincia -> Distrito) para la localización de las sucursales. Todas las operaciones se centran en Perú.

## Links
*   Template documentation - https://phpstack-1384472-5121645.cloudwaysapps.com/document/html/ki-admin/index.html
*   Icons Fontawesome - https://fontawesome.com/
*   listJs - https://listjs.com/
*   masonry - https://masonry.desandro.com/
*   notifications - https://apvarun.github.io/toastify-js/
*   nouislider - https://refreshless.com/nouislider/
*   select - https://select2.org/
*   simplebar - https://grsmto.github.io/simplebar/
*   slick - https://kenwheeler.github.io/slick/
*   stacks - https://draggabilly.desandro.com/
*   sweetalert - https://sweetalert2.github.io/
*   tourguide-js - https://shepherdjs.dev/docs/index.html
*   trumbowyg - https://alex-d.github.io/Trumbowyg/documentation/
*   typeahead - https://twitter.github.io/typeahead.js/
*   animation - https://animate.style/
*   apexcharts - https://apexcharts.com/
*   bootstrap - https://getbootstrap.com/
*   chats.js - https://www.chartjs.org/
*   cleavejs - https://nosir.github.io/cleave.js/
*   datatable - https://datatables.net/
*   datepikar - https://flatpickr.js.org/
*   dual listboxes - https://bulma.io/
*   filepond - https://pqina.nl/filepond/
*   FullCalendar - https://fullcalendar.io/docs/initialize-globals
*   glightbox - https://biati-digital.github.io/glightbox/
*   googlemap - https://hpneo.dev/gmaps/
*   introjs - https://introjs.com/
*   kanban_board - https://muuri.dev/
*   leafletmaps - https://leafletjs.com/index.html