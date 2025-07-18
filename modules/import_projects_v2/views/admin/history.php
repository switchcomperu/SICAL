<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-heading">
            <div class="row">
              <div class="col-md-6">
                <h4 class="panel-title">
                  <i class="fa fa-history"></i> <?php echo _l('import_history'); ?>
                </h4>
              </div>
              <div class="col-md-6 text-right">
                <?php if (staff_can('create', 'import_projects')): ?>
                  <a href="<?php echo admin_url('import_projects'); ?>" class="btn btn-success">
                    <i class="fa fa-plus"></i> <?php echo _l('new_import'); ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="panel-body">
            <?php if (count($import_history) > 0): ?>
              <div class="table-responsive">
                <table class="table table-hover table-bordered datatable">
                  <thead>
                    <tr>
                      <th width="15%"><?php echo _l('date'); ?></th>
                      <th><?php echo _l('filename'); ?></th>
                      <th width="10%"><?php echo _l('total_rows'); ?></th>
                      <th width="10%"><?php echo _l('imported'); ?></th>
                      <th width="10%"><?php echo _l('failed'); ?></th>
                      <th width="15%"><?php echo _l('user'); ?></th>
                      <th width="15%"><?php echo _l('status'); ?></th>
                      <th width="15%"><?php echo _l('actions'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($import_history as $h): ?>
                      <tr>
                        <td data-order="<?php echo strtotime($h['date']); ?>">
                          <?php echo _dt($h['date']); ?>
                        </td>
                        <td><?php echo html_escape($h['file_name']); ?></td>
                        <td><?php echo $h['total_rows']; ?></td>
                        <td class="text-success"><?php echo $h['imported_rows']; ?></td>
                        <td class="text-danger"><?php echo $h['failed_rows']; ?></td>
                        <td>
                          <a href="<?php echo admin_url('profile/' . $h['staff_id']); ?>">
                            <?php echo get_staff_full_name($h['staff_id']); ?>
                          </a>
                        </td>
                        <td><?php echo import_projects_status_badge($h['status']); ?></td>
                        <td>
                          <div class="btn-group">
                            <a href="<?php echo admin_url('import_projects/detail/' . $h['id']); ?>" 
                               class="btn btn-default btn-sm" 
                               data-toggle="tooltip" 
                               title="<?php echo _l('view_details'); ?>">
                              <i class="fa fa-eye"></i>
                            </a>
                            <?php if (staff_can('delete', 'import_projects')): ?>
                              <a href="<?php echo admin_url('import_projects/delete/' . $h['id']); ?>" 
                                 class="btn btn-danger btn-sm _delete" 
                                 data-toggle="tooltip" 
                                 title="<?php echo _l('delete'); ?>">
                                <i class="fa fa-trash"></i>
                              </a>
                            <?php endif; ?>
                            <a href="<?php echo admin_url('import_projects/download_original/' . $h['id']); ?>" 
                               class="btn btn-info btn-sm" 
                               data-toggle="tooltip" 
                               title="<?php echo _l('download_original_file'); ?>">
                              <i class="fa fa-download"></i>
                            </a>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="alert alert-info">
                <?php echo _l('no_import_history_found'); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
  $(function() {
    // Inicializar DataTable
    initDataTable('.table.datatable', undefined, undefined, undefined, undefined, 1, [0, 'desc']);
    
    // Tooltips
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