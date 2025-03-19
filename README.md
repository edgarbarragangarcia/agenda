# Agenda INGENES

Sistema de gestión de citas médicas para clínicas de fertilidad.

## Estructura del Proyecto

El proyecto ha sido reorganizado para facilitar su despliegue en Vercel:

```
agenda/
├── config/           # Configuración de la base de datos
├── includes/         # Archivos PHP incluidos (navbar, estilos)
├── pages/            # Páginas principales de la aplicación
│   ├── index.html    # Página de inicio de sesión
│   ├── dashboard.html # Panel principal
│   ├── appointments.php # Gestión de citas
│   ├── citas.php     # Vista de citas
│   ├── groups.php    # Gestión de grupos
│   ├── pacientes.php # Gestión de pacientes
│   ├── register.php  # Registro de usuarios
│   ├── setup_admin.php # Configuración inicial
│   ├── usuarios.php  # Gestión de usuarios
│   └── logout.php    # Cierre de sesión
├── public/           # Archivos públicos (CSS, JS, imágenes)
└── vercel.json       # Configuración para Vercel
```

## Despliegue en Vercel

Para desplegar esta aplicación en Vercel:

1. Asegúrate de tener una cuenta en [Vercel](https://vercel.com)
2. Conecta tu repositorio de GitHub a Vercel
3. Configura las variables de entorno necesarias para la base de datos
4. Despliega la aplicación

El archivo `vercel.json` ya está configurado para manejar las rutas correctamente.

## Desarrollo Local

Para ejecutar la aplicación localmente:

1. Instala XAMPP o un servidor PHP similar
2. Coloca el proyecto en la carpeta `htdocs`
3. Configura la base de datos en `config/database.php`
4. Accede a la aplicación a través de `http://localhost/agenda/pages/index.html`

## Notas Importantes

- La aplicación utiliza almacenamiento local (localStorage) para mantener la sesión del usuario en las páginas HTML
- Las páginas PHP utilizan sesiones de PHP para la autenticación
- Se ha implementado una estructura híbrida para facilitar la transición de PHP a HTML/JavaScript
