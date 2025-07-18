<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_projects_model extends App_Model
{
    protected $history_table;
    protected $rows_table;
    protected $projects_table;
    protected $clients_table;
    protected $staff_table;
    
    public function __construct()
    {
        parent::__construct();
        $this->history_table = db_prefix() . 'import_projects_history';
        $this->rows_table = db_prefix() . 'import_projects_rows';
        $this->projects_table = db_prefix() . 'projects';
        $this->clients_table = db_prefix() . 'clients';
        $this->staff_table = db_prefix() . 'staff';
    }

    /**
     * Obtiene el historial de importaciones
     * 
     * @param int|null $id ID específico o null para listado completo
     * @param bool $with_relations Incluir relaciones con otras tablas
     * @return mixed
     */
    public function get_import_history($id = null, bool $with_relations = true)
    {
        $select = ['h.*'];
        
        if ($with_relations) {
            array_push($select, 
                'CONCAT(s.firstname, " ", s.lastname) as staff_name',
                's.email as staff_email'
            );
            $this->db->join($this->staff_table . ' s', 'h.staff_id = s.staffid', 'left');
        }
        
        $this->db->select($select);
        $this->db->from($this->history_table . ' h');
        
        if ($id) {
            $this->db->where('h.id', $id);
            return $this->db->get()->row();
        }
        
        $this->db->order_by('h.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Filtra el historial con múltiples criterios
     * 
     * @param array $filters Puede contener: date_from, date_to, staff_id, status
     * @param array $options Opciones adicionales (pagination, limit, etc.)
     * @return array
     */
    public function filter_history(array $filters = [], array $options = [])
    {
        // Selección base
        $this->db->select('h.*, s.firstname, s.lastname');
        $this->db->from($this->history_table . ' h');
        $this->db->join($this->staff_table . ' s', 'h.staff_id = s.staffid', 'left');
        
        // Aplicar filtros
        $this->apply_filters($filters);
        
        // Ordenamiento por defecto
        $this->db->order_by('h.created_at', 'DESC');
        
        // Opciones adicionales
        if (isset($options['limit'])) {
            $this->db->limit($options['limit'], $options['offset'] ?? 0);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Cuenta importaciones según filtros
     */
    public function count_imports(array $filters = []): int
    {
        $this->db->from($this->history_table . ' h');
        $this->apply_filters($filters);
        return $this->db->count_all_results();
    }

    /**
     * Crea un nuevo registro de historial
     * 
     * @param array $data Datos del historial
     * @return int ID del registro creado
     */
    public function create_import_history(array $data): int
    {
        $defaults = [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'status' => 'pending'
        ];
        
        $this->db->insert($this->history_table, array_merge($defaults, $data));
        return $this->db->insert_id();
    }

    /**
     * Actualiza un registro de historial
     * 
     * @param int $id ID del historial
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update_history(int $id, array $data): bool
    {
        $this->db->where('id', $id);
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->history_table, $data);
    }

    /**
     * Procesa filas de importación y crea proyectos
     * 
     * @param array $rows Datos de filas a importar
     * @param int $history_id ID del historial asociado
     * @return array Resultados del proceso
     */
    public function process_import(array $rows, int $history_id): array
    {
        $results = [
            'success_count' => 0,
            'error_count' => 0,
            'status' => 'completed',
            'notes' => '',
            'project_ids' => []
        ];
        
        $error_messages = [];
        
        $this->db->trans_begin();
        
        try {
            foreach ($rows as $row) {
                $result = $this->process_single_row($row, $history_id);
                
                if ($result['status'] === 'success') {
                    $results['success_count']++;
                    $results['project_ids'][] = $result['project_id'];
                } else {
                    $results['error_count']++;
                    $error_messages[] = $result['error_message'];
                }
            }
            
            // Determinar estado final
            $results['status'] = $this->determine_import_status($results);
            $results['notes'] = implode("\n", $error_messages);
            
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                throw new RuntimeException('Transaction failed during import');
            }
            
            $this->db->trans_commit();
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        }
        
        return $results;
    }

    /**
     * Obtiene filas importadas con opciones de filtrado
     */
    public function get_import_rows(
        int $history_id, 
        bool $with_errors = false, 
        bool $with_project_info = false
    ): array {
        $this->db->where('history_id', $history_id);
        
        if ($with_errors) {
            $this->db->where('status', 'failed');
        }
        
        if ($with_project_info) {
            $this->db->select('r.*, p.name as project_name, p.status as project_status');
            $this->db->join($this->projects_table . ' p', 'r.project_id = p.id', 'left');
        }
        
        $this->db->order_by('row_number', 'ASC');
        return $this->db->get($this->rows_table . ' r')->result();
    }

    /**
     * Busca un cliente usando múltiples criterios
     * 
     * @param string $identifier Puede ser ID, email o nombre
     * @return int|null ID del cliente
     */
    public function find_client(string $identifier): ?int
    {
        $this->db->select('userid');
        
        if (is_numeric($identifier)) {
            $this->db->where('userid', $identifier);
        } elseif (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $this->db->where('email', $identifier);
        } else {
            $this->db->group_start()
                ->like('company', $identifier, 'both')
                ->or_like('CONCAT(firstname, " ", lastname)', $identifier, 'both')
                ->group_end();
        }
        
        $client = $this->db->get($this->clients_table)->row();
        return $client ? (int) $client->userid : null;
    }

    /**
     * Elimina una importación y sus datos relacionados
     * 
     * @param int $history_id
     * @param bool $delete_projects Si debe eliminar los proyectos creados
     * @return bool
     */
    public function delete_import(int $history_id, bool $delete_projects = false): bool
    {
        $this->db->trans_begin();
        
        try {
            // Obtener proyectos relacionados si es necesario
            $project_ids = [];
            if ($delete_projects) {
                $this->db->select('project_id');
                $this->db->where('history_id', $history_id);
                $this->db->where('project_id IS NOT NULL');
                $rows = $this->db->get($this->rows_table)->result();
                $project_ids = array_column($rows, 'project_id');
            }
            
            // Eliminar filas
            $this->db->where('history_id', $history_id);
            $this->db->delete($this->rows_table);
            
            // Eliminar historial
            $this->db->where('id', $history_id);
            $deleted = $this->db->delete($this->history_table);
            
            // Eliminar proyectos si es necesario
            if ($delete_projects && !empty($project_ids)) {
                $this->db->where_in('id', $project_ids);
                $this->db->delete($this->projects_table);
            }
            
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            }
            
            $this->db->trans_commit();
            return $deleted;
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_activity('Error deleting import: ' . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // MÉTODOS PRIVADOS
    // ==========================================

    private function apply_filters(array $filters): void
    {
        if (!empty($filters['date_from'])) {
            $this->db->where('h.created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        
        if (!empty($filters['date_to'])) {
            $this->db->where('h.created_at <=', $filters['date_to'] . ' 23:59:59');
        }
        
        if (!empty($filters['staff_id'])) {
            $this->db->where('h.staff_id', $filters['staff_id']);
        }
        
        if (!empty($filters['status'])) {
            $this->db->where('h.status', $filters['status']);
        }
    }

    private function process_single_row(array $row, int $history_id): array
    {
        if (!empty($row['errors'])) {
            return [
                'status' => 'failed',
                'error_message' => sprintf(
                    _l('import_row_error'), 
                    $row['row_number'], 
                    implode(', ', $row['errors'])
                )
            ];
        }
        
        // Crear proyecto
        $project_id = $this->create_project([
            'name' => $row['project_name'],
            'clientid' => $row['client_id'],
            'start_date' => $row['start_date'],
            'deadline' => $row['end_date'],
            'status' => 1, // Abierto
            'addedfrom' => get_staff_user_id()
        ]);
        
        // Registrar fila
        $row_data = [
            'history_id' => $history_id,
            'row_number' => $row['row_number'],
            'project_name' => $row['project_name'],
            'client_id' => $row['client_id'],
            'client_raw' => $row['client_raw'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'project_id' => $project_id,
            'status' => $project_id ? 'success' : 'failed',
            'error_message' => $project_id ? null : _l('project_creation_failed')
        ];
        
        $this->db->insert($this->rows_table, $row_data);
        
        if (!$project_id) {
            return [
                'status' => 'failed',
                'error_message' => sprintf(
                    _l('import_row_error'), 
                    $row['row_number'], 
                    _l('project_creation_failed')
                )
            ];
        }
        
        return [
            'status' => 'success',
            'project_id' => $project_id
        ];
    }

    private function create_project(array $data): ?int
    {
        $this->db->insert($this->projects_table, $data);
        return $this->db->insert_id();
    }

    private function determine_import_status(array $results): string
    {
        if ($results['error_count'] > 0 && $results['success_count'] > 0) {
            return 'partial';
        }
        
        if ($results['error_count'] > 0) {
            return 'failed';
        }
        
        return 'completed';
    }
}