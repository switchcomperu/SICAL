<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CONFIGURACIÓN DEL MÓDULO IMPORT PROJECTS
 */
$config = [
    // 1. METADATOS ESENCIALES (OBLIGATORIOS)
    'name' => 'Import Projects', // Nombre visible en UI
    'module_name' => 'import_projects', // Identificador único
    'version' => '1.0.1',
    'author' => 'Miguel Angel Sánchez',
    
    // 2. CONFIGURACIÓN ADICIONAL
    'description' => 'Importa proyectos desde archivos CSV con validación y historial',
    'author_uri' => 'https://github.com/miguelangel-sanchez',
    'required_perfex_version' => '2.3.0',
    
    // 3. CONFIGURACIÓN FUNCIONAL
    'csv_settings' => [
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
        'skip_empty_lines' => true,
        'trim_fields' => true,
        'max_file_size' => 5, // MB
        'allowed_columns' => ['project_name', 'client_id', 'start_date', 'end_date']
    ],
    
    // 4. PERMISOS (Deben coincidir con los del lang file)
    'permissions' => [
        'view' => [
            'name' => 'view_import_projects',
            'capability' => 'view'
        ],
        'create' => [
            'name' => 'create_import_projects',
            'capability' => 'create'
        ],
        'delete' => [
            'name' => 'delete_import_projects',
            'capability' => 'delete'
        ]
    ],
    
    // 5. CONFIGURACIÓN DE TABLAS
    'tables' => [
        'history' => 'import_projects_history',
        'rows' => 'import_projects_rows'
    ]
];

// Registrar archivos de idioma (OPCIONAL pero recomendado)
//register_language_files('import_projects', ['import_projects']);