<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_projects extends AdminController
{
    private $csv_config;
    private $import_service;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('import_projects_model');
        $this->load->library('Csvimport');
        $this->load->config('import_projects');
        
        $this->csv_config = $this->config->item('csv_settings');
        
        $this->check_permissions();
        $this->initialize_csv_library();
    }

    /**
     * Vista principal del módulo
     */
    public function index()
    {
        $data = $this->prepare_import_view_data();
        $this->load->view('import_view', $data);
    }

    /**
     * Procesa archivo CSV y muestra vista previa
     */
    public function preview_upload()
    {
        $this->validate_permission('create');
        
        try {
            $upload_data = $this->handle_file_upload();
            $preview_data = $this->process_csv_file($upload_data['file_path']);
            
            $this->store_import_session_data($preview_data, $upload_data);
            
            $view_data = $this->prepare_preview_view_data($preview_data);
            $this->load->view('preview', $view_data);
            
        } catch (Exception $e) {
            $this->handle_import_error($e);
        }
    }

    /**
     * Confirma la importación después de la vista previa
     */
    public function confirm_upload()
    {
        $this->validate_permission('create');
        
        $session_data = $this->get_import_session_data();
        $this->validate_session_data($session_data);
        
        $import_results = $this->execute_import($session_data);
        
        $this->clean_import_session();
        $this->redirect_with_results($import_results);
    }

    /**
     * Muestra el historial de importaciones
     */
    public function history($id = null)
    {
        $view_data = $id ? $this->get_import_detail_data($id) : $this->get_import_history_data();
        $this->load->view($id ? 'detail' : 'history', $view_data);
    }

    /**
     * Descarga el archivo original de una importación
     */
    public function download_original($history_id)
    {
        $import = $this->import_projects_model->get_import_history($history_id);
        
        if (!$this->validate_import_file($import)) {
            set_alert('warning', _l('file_not_found'));
            redirect(admin_url('import_projects/history'));
        }
        
        $this->output_import_file($import);
    }

    // ==========================================
    // MÉTODOS PRIVADOS
    // ==========================================

    private function check_permissions()
    {
        if (!staff_can('view', 'import_projects')) {
            access_denied('Import Projects');
        }
    }

    private function validate_permission($action)
    {
        if (!staff_can($action, 'import_projects')) {
            access_denied('Import Projects');
        }
    }

    private function initialize_csv_library()
    {
        $this->csvimport->initialize([
            'delimiter' => $this->csv_config['delimiter'],
            'encoding' => $this->csv_config['encoding']
        ]);
        
        $this->csvimport->set_expected_headers([
            'Proyecto', 'Cliente', 'Inicio', 'Fin'
        ]);
    }

    private function prepare_import_view_data()
    {
        return [
            'title' => _l('import_projects'),
            'max_file_size' => $this->csv_config['max_file_size'],
            'sample_url' => module_dir_url('import_projects', 'uploads/plantilla_proyectos.csv'),
            'allowed_columns' => $this->csv_config['allowed_columns']
        ];
    }

    private function handle_file_upload()
    {
        $this->validate_upload();
        
        return [
            'file_path' => $_FILES['file_csv']['tmp_name'],
            'file_name' => $_FILES['file_csv']['name']
        ];
    }

    private function validate_upload()
    {
        if (!isset($_FILES['file_csv']) || $_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(_l('upload_error'));
        }

        $valid_types = ['text/csv', 'application/vnd.ms-excel', 'text/plain', 'application/csv'];
        $file_type = $_FILES['file_csv']['type'];
        
        if (!in_array($file_type, $valid_types)) {
            throw new RuntimeException(_l('invalid_file_type'));
        }

        $max_size = $this->csv_config['max_file_size'] * 1024 * 1024;
        if ($_FILES['file_csv']['size'] > $max_size) {
            throw new RuntimeException(sprintf(_l('file_size_exceeded'), $this->csv_config['max_file_size']));
        }
    }

    private function process_csv_file($file_path)
    {
        try {
            $csv_data = $this->csvimport->get_array($file_path);
            
            if (empty($csv_data)) {
                throw new RuntimeException(_l('empty_file_no_data'));
            }
            
            return array_map([$this, 'process_csv_row'], $csv_data);
            
        } catch (Exception $e) {
            throw new RuntimeException(_l('csv_processing_error') . ': ' . $e->getMessage());
        }
    }

    private function process_csv_row($row)
    {
        $project = $row['Proyecto'] ?? '';
        $client_raw = $row['Cliente'] ?? '';
        $start = $row['Inicio'] ?? '';
        $end = $row['Fin'] ?? '';
        
        $errors = $this->validate_row_data($project, $client_raw, $start, $end);
        $client_id = $this->find_client_id($client_raw, $errors);
        
        return [
            'row_number' => $row['_row_number'],
            'project_name' => trim($project),
            'client_raw' => trim($client_raw),
            'client_id' => $client_id,
            'start_date' => $this->validate_date($start) ? $start : null,
            'end_date' => $this->validate_date($end) ? $end : null,
            'errors' => $errors,
            'status' => empty($errors) ? 'valid' : 'invalid'
        ];
    }

    private function validate_row_data($project, $client_raw, $start, $end)
    {
        $errors = [];
        
        if (empty(trim($project))) {
            $errors[] = _l('project_name_required');
        }
        
        if (empty(trim($client_raw))) {
            $errors[] = _l('client_required');
        }
        
        if (!$this->validate_date($start)) {
            $errors[] = _l('invalid_start_date');
        }
        
        if (!$this->validate_date($end)) {
            $errors[] = _l('invalid_end_date');
        }
        
        return $errors;
    }

    private function find_client_id($client_raw, &$errors)
    {
        if (empty(trim($client_raw))) {
            return null;
        }
        
        $client_id = $this->import_projects_model->find_client(trim($client_raw));
        
        if (!$client_id) {
            $errors[] = _l('client_not_found');
        }
        
        return $client_id;
    }

    private function validate_date($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', trim($date));
        return $d && $d->format('Y-m-d') === trim($date);
    }

    private function store_import_session_data($preview_data, $upload_data)
    {
        $this->session->set_userdata([
            'import_preview_data' => $preview_data,
            'import_file_path' => $upload_data['file_path'],
            'import_filename' => $upload_data['file_name']
        ]);
    }

    private function prepare_preview_view_data($preview_data)
    {
        return [
            'title' => _l('preview_import'),
            'rows' => $preview_data,
            'has_errors' => $this->has_errors($preview_data),
            'filename' => $this->session->userdata('import_filename')
        ];
    }

    private function has_errors($rows)
    {
        foreach ($rows as $row) {
            if (!empty($row['errors'])) {
                return true;
            }
        }
        return false;
    }

    private function get_import_session_data()
    {
        return [
            'rows' => $this->session->userdata('import_preview_data'),
            'file_path' => $this->session->userdata('import_file_path'),
            'file_name' => $this->session->userdata('import_filename')
        ];
    }

    private function validate_session_data($session_data)
    {
        if (empty($session_data['rows'])) {
            set_alert('warning', _l('no_data_to_import'));
            redirect(admin_url('import_projects'));
        }
    }

    private function execute_import($session_data)
    {
        $history_data = $this->prepare_history_data($session_data);
        $history_id = $this->import_projects_model->create_import_history($history_data);
        
        $results = $this->import_projects_model->process_import(
            $session_data['rows'], 
            $history_id
        );
        
        $this->update_import_history($history_id, $results);
        
        return [
            'history_id' => $history_id,
            'results' => $results
        ];
    }

    private function prepare_history_data($session_data)
    {
        return [
            'staff_id' => get_staff_user_id(),
            'file_name' => $session_data['file_name'],
            'file_path' => $session_data['file_path'],
            'ip_address' => $this->input->ip_address(),
            'total_rows' => count($session_data['rows']),
            'status' => 'pending'
        ];
    }

    private function update_import_history($history_id, $results)
    {
        $this->import_projects_model->update_history($history_id, [
            'imported_rows' => $results['success_count'],
            'failed_rows' => $results['error_count'],
            'status' => $results['status'],
            'notes' => $results['notes']
        ]);
    }

    private function clean_import_session()
    {
        $this->session->unset_userdata([
            'import_preview_data',
            'import_file_path',
            'import_filename'
        ]);
    }

    private function redirect_with_results($import_results)
    {
        set_alert('success', sprintf(
            _l('import_completed_results'), 
            $import_results['results']['success_count'], 
            $import_results['results']['error_count']
        ));
        
        redirect(admin_url('import_projects/history/' . $import_results['history_id']));
    }

    private function get_import_detail_data($id)
    {
        return [
            'import' => $this->import_projects_model->get_import_history($id),
            'rows' => $this->import_projects_model->get_import_rows($id),
            'title' => _l('import_details')
        ];
    }

    private function get_import_history_data()
    {
        return [
            'history' => $this->import_projects_model->get_import_history(),
            'title' => _l('import_history')
        ];
    }

    private function validate_import_file($import)
    {
        return $import && file_exists($import->file_path);
    }

    private function output_import_file($import)
    {
        $this->load->helper('download');
        force_download($import->file_name, file_get_contents($import->file_path));
    }

    private function handle_import_error(Exception $e)
    {
        log_activity('Import Projects Error: ' . $e->getMessage());
        set_alert('danger', $e->getMessage());
        redirect(admin_url('import_projects'));
    }
}