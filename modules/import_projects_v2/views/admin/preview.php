<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-heading">
            <h4 class="panel-title">
              <i class="fa fa-search"></i> <?php echo _l('import_preview'); ?>
              <small class="text-muted pull-right">
                <?php echo _l('filename'); ?>: <?php echo html_escape($filename); ?>
              </small>
            </h4>
          </div>
          <div class="panel-body">
            <?php if ($has_errors): ?>
              <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i> 
                <?php echo _l('import_contains_errors_warning'); ?>
              </div>
            <?php endif; ?>
            
            <div class="table-responsive">
              <table class="table table-hover table-bordered">
                <thead>
                  <tr>
                    <th width="5%">#</th>
                    <th width="25%"><?php echo _l('project'); ?></th>
                    <th width="25%"><?php echo _l('client'); ?></th>
                    <th width="15%"><?php echo _l('start_date'); ?></th>
                    <th width="15%"><?php echo _l('end_date'); ?></th>
                    <th width="15%"><?php echo _l('status'); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rows as $index => $row): ?>
                    <tr class="<?php echo !empty($row['errors']) ? 'danger' : 'success'; ?>">
                      <td><?php echo $index + 1; ?></td>
                      <td><?php echo html_escape($row['project']); ?></td>
                      <td>
                        <?php echo html_escape($row['client']); ?>
                        <?php if (!empty($row['client_id'])): ?>
                          <small class="text-success block"><?php echo _l('client_found'); ?>: <?php echo $row['client_id']; ?></small>
                        <?php elseif (!empty($row['client'])): ?>
                          <small class="text-danger block"><?php echo _l('client_not_found'); ?></small>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php echo $row['start']; ?>
                        <?php if (!empty($row['start_date_error'])): ?>
                          <small class="text-danger block"><?php echo $row['start_date_error']; ?></small>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php echo $row['end']; ?>
                        <?php if (!empty($row['end_date_error'])): ?>
                          <small class="text-danger block"><?php echo $row['end_date_error']; ?></small>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (empty($row['errors'])): ?>
                          <span class="label label-success"><?php echo _l('valid'); ?></span>
                        <?php else: ?>
                          <div class="text-danger">
                            <?php foreach ($row['errors'] as $error): ?>
                              <div><small><?php echo $error; ?></small></div>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            
            <div class="row">
              <div class="col-md-12 text-right">
                <a href="<?php echo admin_url('import_projects'); ?>" class="btn btn-default mr-2">
                  <i class="fa fa-times"></i> <?php echo _l('cancel'); ?>
                </a>
                <?php if (!$has_errors): ?>
                  <a href="<?php echo admin_url('import_projects/confirm_upload'); ?>" class="btn btn-success" id="confirm-import">
                    <i class="fa fa-check"></i> <?php echo _l('confirm_import'); ?>
                  </a>
                <?php else: ?>
                  <button type="button" class="btn btn-success" disabled data-toggle="tooltip" 
                          title="<?php echo _l('fix_errors_before_import'); ?>">
                    <i class="fa fa-check"></i> <?php echo _l('confirm_import'); ?>
                  </button>
                <?php endif; ?>
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
    // Confirmación antes de importar
    $('#confirm-import').on('click', function(e) {
      e.preventDefault();
      var url = $(this).attr('href');
      
      confirm_alert_promise(url, '<?php echo _l("confirm_import_message"); ?>')
        .then(function() {
          window.location.href = url;
        });
    });

    // Tooltips
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>
</body>
</html>