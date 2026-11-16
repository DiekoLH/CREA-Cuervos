# CREA-Cuervos
Repositorio para CREA Cuervos.

## Cuentas y acceso por rol

Los formularios de registro e inicio de sesión funcionan en el navegador. Cada cuenta se crea como alumno o instructor, el acceso a cada panel valida ese rol y el botón **Cerrar sesión** termina la sesión actual. Las cuentas se guardan en el almacenamiento local del navegador, por lo que solo están disponibles en ese navegador. En esta demostración las contraseñas también se guardan allí sin cifrar; no uses contraseñas reales.

Esta implementación es una demostración para una aplicación estática, no una autenticación segura. No almacena los datos en un servidor y no protege información frente a alguien que pueda modificar el navegador o acceder directamente a los archivos. Para usarla con datos reales, se necesita un backend que gestione contraseñas de forma segura y autorice cada solicitud.
