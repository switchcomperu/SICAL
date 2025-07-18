<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-heading">
            <h4 class="panel-title">
              <i class="fa fa-file-import"></i> <?php echo _l('import_details'); ?> 
              <span class="pull-right">
                <?php echo import_projects_status_badge($import->status); ?>
              </span>
            </h4>
            <div class="panel-options">
              <span class="text-muted">
                <?php echo _l('imported_by'); ?>: 
                <a href="<?php echo admin_url('profile/' . $import->staff_id); ?>">
                  <?php echo $import->staff_name; ?>
                </a> | 
                <?php echo _l('date'); ?>: <?php echo _dt($import->created_at); ?> | 
                <?php echo _l('filename'); ?>: <?php echo $import->file_name; ?>
              </span>
            </div>
          </div>
          <div class="panel-body">
            <div class="row">
              <div class="col-md-4">
                <div class="panel panel-info">
                  <div class="panel-heading"><?php echo _l('import_summary'); ?></div>
                  <div class="panel-body">
                    <ul class="list-group">
                      <li class="list-group-item">
                        <?php echo _l('total_rows'); ?>: <span class="badge"><?php echo $import->total_rows; ?></span>
                      </li>
                      <li class="list-group-item list-group-item-success">
                        <?php echo _l('imported_successfully'); ?>: <span class="badge"><?php echo $import->imported_rows; ?></span>
                      </li>
                      <li class="list-group-item list-group-item-danger">
                        <?php echo _l('failed_rows'); ?>: <span class="badge"><?php echo $import->failed_rows; ?></span>
                      </li>
                    </ul>
                    <?php if (!empty($import->notes)): ?>
                      <div class="alert alert-warning">
                        <h5><?php echo _l('notes'); ?></h5>
                        <?php echo nl2br($import->notes); ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="col-md-8">
                <div class="table-responsive">
                  <table class="table table-hover table-striped table-bordered">
                    <thead>
                      <tr>
                        <th width="5%">#</th>
                        <th><?php echo _l('project'); ?></th>
                        <th width="20%"><?php echo _l('client'); ?></th>
                        <th width="15%"><?php echo _l('start_date'); ?></th>
                        <th width="15%"><?php echo _l('end_date'); ?></th>
                        <th width="10%"><?php echo _l('status'); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($rows as $row): ?>
                        <tr class="<?php echo $row->status === 'failed' ? 'danger' : 'success'; ?>">
                          <td><?php echo $row->row_number; ?></td>
                          <td>
                            <?php if ($row->project_id): ?>
                              <a href="<?php echo admin_url('projects/view/' . $row->project_id); ?>">
                                <?php echo $row->project_name; ?>
                              </a>
                            <?php else: ?>
                              <?php echo $row->project_name; ?>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if ($row->client_id): ?>
                              <a href="<?php echo admin_url('clients/client/' . $row->client_id); ?>">
                                <?php echo $row->client_raw; ?>
                              </a>
                            <?php else: ?>
                              <?php echo $row->client_raw; ?>
                            <?php endif; ?>
                          </td>
                          <td><?php echo _d($row->start_date); ?></td>
                          <td><?php echo _d($row->end_date); ?></td>
                          <td>
                            <?php echo import_projects_status_badge($row->status); ?>
                            <?php if (!empty($row->error_message)): ?>
                              <small class="text-danger block"><?php echo $row->error_message; ?></small>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 text-right">
                <a href="<?php echo admin_url('import_projects/history'); ?>" class="btn btn-default">
                  <i class="fa fa-arrow-left"></i> <?php echo _l('back_to_history'); ?>
                </a>
                <?php if (staff_can('delete', 'import_projects')): ?>
                  <a href="<?php echo admin_url('import_projects/delete/' . $import->id); ?>" 
                     class="btn btn-danger _delete" 
                     data-toggle="tooltip" 
                     title="<?php echo _l('delete_import'); ?>">
                    <i class="fa fa-trash"></i>
                  </a>
                <?php endif; ?>
                <a href="<?php echo admin_url('import_projects/download_original/' . $import->id); ?>" 
                   class="btn btn-info" 
                   data-toggle="tooltip" 
                   title="<?php echo _l('download_original_file'); ?>">
                  <i class="fa fa-download"></i> <?php echo _l('original_file'); ?>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
  $(function() {
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Confirmación para eliminar
    $('._delete').on('click', function(e) {
      e.preventDefault();
      var url = $(this).attr('href');
      confirm_alert_promise(url)
        .then(function() {
          window.location.href = url;
        });
    });
  });
</script>
</body>
</html>