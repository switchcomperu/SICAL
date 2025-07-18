<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * HELPERS PARA EL MÓDULO IMPORT PROJECTS
 * --------------------------------------
 * @version 1.1.0
 * @author Miguel Angel Sánchez
 */

/**
 * Inicializa los elementos del menú del módulo
 */
function import_projects_module_init_menu_items()
{
    $CI = &get_instance();

    if (staff_can('view', 'import_projects')) {
        // Menú principal
        $CI->app_menu->add_sidebar_menu_item('import_projects', [
            'slug'     => 'import_projects',
            'name'     => _l('import_projects'),
            'icon'     => 'fa fa-file-import',
            'href'     => admin_url('import_projects'),
            'position' => 36,
            'badge'    => get_import_projects_pending_tasks(),
        ]);

        // Submenú: Importar
        if (staff_can('create', 'import_projects')) {
            $CI->app_menu->add_sidebar_children_item('import_projects', [
                'slug'     => 'import_projects-import',
                'name'     => _l('import_new'),
                'href'     => admin_url('import_projects'),
                'position' => 1,
                'icon'     => 'fa fa-upload'
            ]);
        }

        // Submenú: Historial
        $CI->app_menu->add_sidebar_children_item('import_projects', [
            'slug'     => 'import_projects-history',
            'name'     => _l('import_history'),
            'href'     => admin_url('import_projects/history'),
            'position' => 2,
            'icon'     => 'fa fa-history'
        ]);

        // Submenú: Plantilla
        $CI->app_menu->add_sidebar_children_item('import_projects', [
            'slug'     => 'import_projects-template',
            'name'     => _l('download_template'),
            'href'     => admin_url('import_projects/download_template'),
            'position' => 3,
            'icon'     => 'fa fa-file-download'
        ]);

        // Submenú: Configuración (solo admin)
        if (staff_can('edit', 'settings')) {
            $CI->app_menu->add_sidebar_children_item('import_projects', [
                'slug'     => 'import_projects-settings',
                'name'     => _l('settings'),
                'href'     => admin_url('import_projects/settings'),
                'position' => 4,
                'icon'     => 'fa fa-cog'
            ]);
        }
    }
}

/**
 * Obtiene el número de importaciones pendientes o con errores
 * @return string|null
 */
function get_import_projects_pending_tasks()
{
    $CI = &get_instance();
    $CI->load->model('import_projects_model');

    if (staff_can('view', 'import_projects')) {
        $pending = $CI->import_projects_model->count_imports([
            'status' => 'pending',
            'staff_id' => get_staff_user_id()
        ]);

        $failed = $CI->import_projects_model->count_imports([
            'status' => 'failed',
            'staff_id' => get_staff_user_id()
        ]);

        $total = $pending + $failed;

        return $total > 0 ? $total : null;
    }

    return null;
}

/**
 * Registra los hooks del módulo
 */
function import_projects_init_hooks()
{
    $CI = &get_instance();

    // Añadir elemento al menú
    add_action('admin_init', 'import_projects_module_init_menu_items');

    // Añadir hoja de estilos y JS
    add_action('app_admin_head', 'import_projects_add_head_components');
    add_action('app_admin_footer', 'import_projects_add_footer_components');

    // Añadir permisos al instalador
    add_filter('staff_permissions', 'import_projects_add_permissions');
}

/**
 * Añade componentes al head (CSS)
 */
function import_projects_add_head_components()
{
    $CI = &get_instance();
    
    if (strpos($CI->uri->uri_string(), 'import_projects') !== false) {
        echo '<link href="' . module_dir_url('import_projects', 'assets/css/import_projects.css') . '" rel="stylesheet">';
    }
}

/**
 * Añade componentes al footer (JS)
 */
function import_projects_add_footer_components()
{
    $CI = &get_instance();
    
    if (strpos($CI->uri->uri_string(), 'import_projects') !== false) {
        echo '<script src="' . module_dir_url('import_projects', 'assets/js/import_projects.js') . '"></script>';
    }
}

/**
 * Añade permisos para el módulo
 * @param array $permissions
 * @return array
 */
function import_projects_add_permissions($permissions)
{
    $permissions['import_projects'] = [
        'view'   => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    return $permissions;
}

/**
 * Genera un botón para descargar la plantilla
 * @return string
 */
function import_projects_template_button()
{
    return '<a href="' . admin_url('import_projects/download_template') . '" class="btn btn-primary">
        <i class="fa fa-file-download"></i> ' . _l('download_template') . '
    </a>';
}

/**
 * Muestra un badge con el estado de la importación
 * @param string $status
 * @return string
 */
function import_projects_status_badge($status)
{
    $statuses = [
        'completed' => ['success', _l('completed')],
        'pending'   => ['warning', _l('pending')],
        'failed'    => ['danger', _l('failed')],
        'partial'   => ['info', _l('partial')]
    ];

    if (array_key_exists($status, $statuses)) {
        return '<span class="label label-' . $statuses[$status][0] . '">' . $statuses[$status][1] . '</span>';
    }

    return '<span class="label label-default">' . _l('unknown') . '</span>';
}