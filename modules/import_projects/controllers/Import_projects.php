public function confirm_upload()
{
    $rows = $this->session->userdata('import_preview_data');
    $file_name = $this->session->userdata('import_filename');

    $total_imported = 0;

    foreach ($rows as $r) {
        if (count($r['errors'])) continue;

        $this->db->insert(db_prefix() . 'import_projects_rows', [
            'project_name' => $r['project'],
            'client' => $r['client'],
            'start_date' => $r['start'],
            'end_date' => $r['end'],
        ]);
        $total_imported++;
    }

    $history_id = $this->import_projects_model->log_import(get_staff_user_id(), $file_name, $total_imported);

    // Actualizar las filas con ID de historial
    $this->db->where('history_id', 0)->update(db_prefix() . 'import_projects_rows', ['history_id' => $history_id]);

    $this->session->unset_userdata('import_preview_data');
    $this->session->unset_userdata('import_filename');

    set_alert('success', 'Importación completada correctamente.');
    redirect(admin_url('import_projects/history'));
}
