<?php
defined('BASEPATH') or exit('No direct script access allowed');

function import_projects_module_activate()
{
    $CI = &get_instance();

    // 1. Registro del módulo
    $module_data = [
        'module_name' => 'import_projects',
        'installed_version' => '1.1.1',
        'active' => 1
    ];

    $existing_module = $CI->db->where('module_name', 'import_projects')
                             ->get(db_prefix().'modules')
                             ->row();

    if (!$existing_module) {
        $CI->db->insert(db_prefix().'modules', $module_data);
    }

    // 2. Creación de tablas
    $tables_created = false;

    if (!$CI->db->table_exists(db_prefix() . 'import_projects_history')) {
        $CI->db->query("
            CREATE TABLE IF NOT EXISTS `" . db_prefix() . "import_projects_history` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `staff_id` INT(11) NOT NULL,
                `file_name` VARCHAR(255) NOT NULL,
                `file_path` VARCHAR(512),
                `total_rows` MEDIUMINT(8) NOT NULL DEFAULT 0,
                `imported_rows` MEDIUMINT(8) NOT NULL DEFAULT 0,
                `failed_rows` MEDIUMINT(8) NOT NULL DEFAULT 0,
                `ip_address` VARCHAR(45),
                `status` ENUM('pending','completed','partial','failed') DEFAULT 'pending',
                `notes` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $tables_created = true;
    }

    if (!$CI->db->table_exists(db_prefix() . 'import_projects_rows')) {
        $CI->db->query("
            CREATE TABLE IF NOT EXISTS `" . db_prefix() . "import_projects_rows` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `history_id` INT(11) NOT NULL,
                `project_id` INT(11),
                `row_number` MEDIUMINT(8) NOT NULL,
                `project_name` VARCHAR(255) NOT NULL,
                `client_id` INT(11),
                `client_raw` VARCHAR(255),
                `start_date` DATE,
                `end_date` DATE,
                `status` ENUM('pending','success','failed') DEFAULT 'pending',
                `error_message` TEXT,
                `metadata` JSON,
                `processed_at` DATETIME,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk_history` FOREIGN KEY (`history_id`)
                    REFERENCES `" . db_prefix() . "import_projects_history` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $tables_created = true;
    }

    // 3. Configuración inicial
    $default_settings = [
        'csv_delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
        'max_file_size' => 5,
        'keep_history_days' => 90,
        'default_date_format' => 'Y-m-d',
        'notify_on_failure' => 1
    ];

    if (!$CI->db->where('name', 'import_projects_settings')->get(db_prefix().'options')->row()) {
        $CI->db->insert(db_prefix().'options', [
            'name' => 'import_projects_settings',
            'value' => json_encode($default_settings),
            'autoload' => 1
        ]);
    }

    // 4. Registro de hook
    $existing_hook = $CI->db->where('hook', 'admin_init')
                           ->where('function', 'import_projects_init_menu_items')
                           ->get(db_prefix().'hooks')
                           ->row();

    if (!$existing_hook) {
        $CI->db->insert(db_prefix().'hooks', [
            'hook' => 'admin_init',
            'function' => 'import_projects_init_menu_items',
            'priority' => 10
        ]);
    }

    // 5. Guardar versión instalada
    if ($tables_created) {
        log_activity('Import Projects Module Installed (v1.1.1)');
        $CI->db->where('name', 'import_projects_installed_version');
        if (!$CI->db->get(db_prefix().'options')->row()) {
            $CI->db->insert(db_prefix().'options', [
                'name' => 'import_projects_installed_version',
                'value' => '1.1.1',
                'autoload' => 0
            ]);
        }
    }
}
