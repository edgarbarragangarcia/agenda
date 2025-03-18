# Agenda - Aplicación de Escritorio

Esta es la versión de escritorio de la aplicación Agenda.

## Requisitos Previos

1. Node.js (versión 14 o superior)
2. XAMPP instalado y configurado
3. La aplicación web Agenda funcionando en XAMPP

## Instalación

1. Asegúrate de que XAMPP esté corriendo y la aplicación web funcione correctamente
2. Abre una terminal en esta carpeta
3. Instala las dependencias:
```bash
npm install
```

## Desarrollo

Para ejecutar la aplicación en modo desarrollo:
```bash
npm start
```

## Crear Instalador

Para crear el instalador de la aplicación:
```bash
npm run build
```

Los instaladores se crearán en la carpeta `dist`:
- Windows: `.exe`
- macOS: `.dmg`
- Linux: `.AppImage`

## Importante

- Asegúrate de que XAMPP esté corriendo antes de iniciar la aplicación
- La aplicación necesita acceso a `http://localhost/agenda/`
- Verifica que la base de datos MySQL esté funcionando
