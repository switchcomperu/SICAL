<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * SCRIPT DE DESINSTALACIÓN - IMPORT PROJECTS
 * -----------------------------------------
 * @version 1.0.1
 * @author Miguel Angel Sánchez
 */

$CI = &get_instance();

// ==========================================
// 1. ELIMINACIÓN DE TABLAS PRINCIPALES
// ==========================================
$tables = [
    'import_projects_rows',
    'import_projects_history'
];

foreach ($tables as $table) {
    if ($CI->db->table_exists(db_prefix() . $table)) {
        $CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . $table . "`");
    }
}

// ==========================================
// 2. LIMPIEZA DE CONFIGURACIONES
// ==========================================
// Eliminar configuración del módulo
$CI->db->where('name', 'import_projects_settings');
$CI->db->delete(db_prefix() . 'options');

// Eliminar registros de idioma
$CI->db->where('module', 'import_projects');
$CI->db->delete(db_prefix() . 'language');

// ==========================================
// 3. LIMPIEZA DE ARCHIVOS TEMPORALES
// ==========================================
$uploadPath = FCPATH . 'modules/import_projects/uploads/';

if (file_exists($uploadPath)) {
    // Eliminar todos los archivos CSV subidos
    array_map('unlink', glob($uploadPath . '*.csv'));
    
    // Eliminar directorio (si está vacío)
    if (count(glob($uploadPath . '*')) === 0) {
        rmdir($uploadPath);
    }
}

// ==========================================
// 4. REGISTRO DE LA DESINSTALACIÓN
// ==========================================
log_activity('Módulo Import Projects desinstalado (v1.0.1)');

// Opcional: Eliminar cron jobs relacionados
$CI->db->like('name', 'import_projects', 'after');
$CI->db->delete(db_prefix() . 'cron_jobs');