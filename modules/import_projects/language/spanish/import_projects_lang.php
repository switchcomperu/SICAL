<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Archivo de idioma para el módulo Import Projects
 * 
 * @version 1.1.0
 * @author Miguel Angel Sánchez
 */

// Títulos y cabeceras
$lang['import_projects'] = 'Import Projects';
$lang['import_projects_from_csv'] = 'Import Projects from CSV';
$lang['import_history'] = 'Import History';
$lang['import_details'] = 'Import Details';
$lang['preview_import'] = 'Import Preview';

// Mensajes de estado
$lang['completed'] = 'Completed';
$lang['pending'] = 'Pending';
$lang['failed'] = 'Failed';
$lang['partial'] = 'Partial';
$lang['valid'] = 'Valid';
$lang['invalid'] = 'Invalid';

// Etiquetas de formulario
$lang['select_csv_file'] = 'Select CSV File';
$lang['download_template'] = 'Download Template';
$lang['upload_and_preview'] = 'Upload and Preview';
$lang['confirm_import'] = 'Confirm Import';
$lang['back_to_history'] = 'Back to History';
$lang['file_requirements'] = 'File Requirements';
$lang['required_columns'] = 'Required Columns';
$lang['encoding_requirement'] = 'Encoding Requirement';
$lang['first_row_headers'] = 'First Row Headers';

// Mensajes de error/éxito
$lang['upload_error'] = 'Error uploading file. Please try again.';
$lang['invalid_file_type'] = 'Invalid file type. Only CSV files are allowed.';
$lang['file_size_exceeded'] = 'File size exceeds maximum allowed size of %s MB';
$lang['empty_file_no_data'] = 'The file is empty or contains no valid data.';
$lang['csv_processing_error'] = 'Error processing CSV file';
$lang['no_data_to_import'] = 'No data available to import';
$lang['import_completed_results'] = 'Import completed: %d successful, %d with errors';
$lang['import_contains_errors_warning'] = 'The import contains errors. Please fix them before proceeding.';
$lang['file_not_found'] = 'File not found';
$lang['import_row_error'] = 'Row %d: %s';
$lang['project_creation_failed'] = 'Project creation failed';
$lang['client_not_found'] = 'Client not found in database';

// Validación
$lang['project_name_required'] = 'Project name is required';
$lang['client_required'] = 'Client is required';
$lang['invalid_start_date'] = 'Invalid start date (format: YYYY-MM-DD)';
$lang['invalid_end_date'] = 'Invalid end date (format: YYYY-MM-DD)';
$lang['fix_errors_before_import'] = 'Please fix all errors before importing';

// Columnas CSV
$lang['project'] = 'Project';
$lang['client'] = 'Client';
$lang['start_date'] = 'Start Date';
$lang['end_date'] = 'End Date';
$lang['status'] = 'Status';
$lang['actions'] = 'Actions';
$lang['date'] = 'Date';
$lang['filename'] = 'Filename';
$lang['total_rows'] = 'Total Rows';
$lang['imported'] = 'Imported';
$lang['failed_rows'] = 'Failed Rows';
$lang['user'] = 'User';
$lang['notes'] = 'Notes';

// Botones y acciones
$lang['view_details'] = 'View Details';
$lang['delete_import'] = 'Delete Import';
$lang['download_original_file'] = 'Download Original File';
$lang['original_file'] = 'Original File';
$lang['new_import'] = 'New Import';

// Permisos
$lang['permission_view'] = 'View Imports';
$lang['permission_create'] = 'Create Imports';
$lang['permission_edit'] = 'Edit Imports';
$lang['permission_delete'] = 'Delete Imports';

// Mensajes de confirmación
$lang['confirm_import_message'] = 'Are you sure you want to import these projects?';
$lang['confirm_delete_import'] = 'Are you sure you want to delete this import? This action cannot be undone.';
$lang['confirm_delete_import_with_projects'] = 'Are you sure you want to delete this import and all associated projects? This action cannot be undone.';

// Otros
$lang['imported_by'] = 'Imported by';
$lang['import_summary'] = 'Import Summary';
$lang['imported_successfully'] = 'Imported Successfully';
$lang['client_found'] = 'Client found';
$lang['unknown'] = 'Unknown';
$lang['csv_file_requirements'] = 'The CSV file must contain specific columns and be properly formatted';
$lang['max_file_size_help'] = 'Maximum file size: %s MB';