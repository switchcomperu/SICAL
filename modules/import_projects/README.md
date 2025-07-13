# Import Projects Module for Perfex CRM

Este módulo permite importar proyectos desde archivos Excel directamente en el área de administración de Perfex CRM, con validación previa, historial de importaciones, detalle por fila y filtrado por fecha o usuario.

## ✅ Características

- Importación desde archivos `.xlsx` usando PhpSpreadsheet.
- Validación previa de los datos antes de la importación.
- Historial completo de importaciones por usuario.
- Detalle por fila de cada archivo importado.
- Filtros por fechas o por miembro del staff.
- Botón de descarga del archivo Excel original.

## 📁 Estructura de Carpetas

modules/
└── import_projects/
├── controllers/
│ └── Import_projects.php
├── language/
│ └── english/
│ └── import_projects_lang.php
├── models/
│ └── Import_projects_model.php
├── views/
│ └── admin/
│ ├── import_view.php
│ ├── preview.php
│ ├── history.php
│ └── detail.php
├── install.php
├── uninstall.php
├── import_projects.php
└── README.md


## 🛠️ Requisitos

- Perfex CRM >= 2.3.2
- Biblioteca PhpSpreadsheet instalada (`vendor/` ya incluida en Perfex desde 2.3.2)
- Permisos de escritura en `modules/` y en la base de datos

## 🚀 Instalación

1. **Copiar el módulo:**  
   Sube la carpeta `import_projects` dentro del directorio `modules/` de Perfex CRM.

2. **Activar el módulo:**  
   Ve a `Setup > Modules`, busca `Importación de Proyectos` y haz clic en **Activar**.  
   Esto creará automáticamente las tablas necesarias (`tblimport_projects_history` y `tblimport_projects_rows`).

3. **Acceder al módulo:**  
   En el panel de administración, verás un nuevo ítem de menú llamado **Importar Proyectos** con las opciones:
   - **Importar:** Cargar y validar el archivo.
   - **Historial:** Revisar cargas pasadas y ver detalle por fila.

## 📦 Funciones adicionales

- Si se desinstala el módulo desde el panel, se ejecutará automáticamente `uninstall.php`, eliminando las tablas relacionadas.
- Soporta traducciones personalizadas vía `custom_lang.php`.

## 📌 URL Directas

- Subida: `/admin/import_projects`
- Vista previa: `/admin/import_projects/preview_upload`
- Confirmación: `/admin/import_projects/confirm_upload`
- Historial: `/admin/import_projects/history`
- Detalle: `/admin/import_projects/detail/{ID}`

## 📋 Notas

- El archivo Excel debe tener como mínimo 4 columnas:
  1. Nombre del proyecto
  2. Cliente
  3. Fecha de inicio (formato `YYYY-MM-DD`)
  4. Fecha de fin (formato `YYYY-MM-DD`)
- Las filas con errores serán ignoradas en la importación final.

## 📧 Soporte

Para cualquier duda o mejora, contáctate con el desarrollador original del módulo.

---


