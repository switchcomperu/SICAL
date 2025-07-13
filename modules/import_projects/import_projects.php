<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Importación de Proyectos
Description: Módulo para importar proyectos desde archivos Excel y mantener historial por usuario.
Version: 1.0.0
Requires at least: 2.3.*
Author: TuNombre
*/

// Evitar accesos directos
if (!defined('BASEPATH')) exit('No direct script access allowed');

// Ejecutar archivo install.php al activar el módulo
register_activation_hook('import_projects', 'import_projects_module_activate');
function import_projects_module_activate()
{
    include_once(__DIR__ . '/install.php');
}

// Ejecutar archivo uninstall.php al desinstalar el módulo
register_uninstall_hook('import_projects', 'import_projects_module_uninstall');
function import_projects_module_uninstall()
{
    include_once(__DIR__ . '/uninstall.php');
}

// Puedes usar esta función si necesitas desactivar alguna funcionalidad temporalmente
register_deactivation_hook('import_projects', 'import_projects_module_deactivate');
function import_projects_module_deactivate()
{
    // No hay lógica por ahora
}

// Registrar archivos de idioma
register_language_files('import_projects', ['import_projects']);

// Añadir ítem al menú del administrador
hooks()->add_action('admin_init', 'import_projects_module_init_menu_items');
function import_projects_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('import-projects-menu', [
        'name'     => _l('import_projects_menu_label'),
        'icon'     => 'fa fa-upload',
        'position' => 32, // Justo después de Proyectos (30)
        'collapse' => true,
    ]);

    $CI->app_menu->add_sidebar_children_item('import-projects-menu', [
        'slug'     => 'import-projects-upload',
        'name'     => 'Importar',
        'href'     => admin_url('import_projects'),
        'position' => 1,
    ]);

    $CI->app_menu->add_sidebar_children_item('import-projects-menu', [
        'slug'     => 'import-projects-history',
        'name'     => 'Historial',
        'href'     => admin_url('import_projects/history'),
        'position' => 2,
    ]);
}
