<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="panel_s">
          <div class="panel-heading">
            <h4 class="panel-title">
              <i class="fa fa-file-import"></i> <?php echo _l('import_projects_from_csv'); ?>
            </h4>
          </div>
          <div class="panel-body">
            <?php echo form_open_multipart(admin_url('import_projects/preview_upload'), ['id' => 'import_form', 'class' => 'import-form']); ?>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label for="file_csv" class="control-label">
                      <?php echo _l('select_csv_file'); ?>
                      <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                      <input type="file" name="file_csv" id="file_csv" class="form-control" 
                             accept=".csv" required 
                             data-max-size="<?php echo $max_file_size; ?>">
                      <span class="input-group-btn">
                        <button type="button" class="btn btn-info btn-file-preview" 
                                data-toggle="popover" 
                                data-placement="left" 
                                data-content="<?php echo html_escape(_l('csv_file_requirements')); ?>">
                          <i class="fa fa-question-circle"></i>
                        </button>
                      </span>
                    </div>
                    <small class="text-muted">
                      <?php echo sprintf(_l('max_file_size_help'), $max_file_size); ?>
                    </small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="alert alert-info">
                    <h5><?php echo _l('file_requirements'); ?></h5>
                    <ul class="mb-0">
                      <li><?php echo _l('required_columns'); ?>: 
                        <strong><?php echo _l('project'); ?>, <?php echo _l('client'); ?>, 
                        <?php echo _l('start_date'); ?> (YYYY-MM-DD), <?php echo _l('end_date'); ?> (YYYY-MM-DD)</strong>
                      </li>
                      <li><?php echo _l('encoding_requirement'); ?></li>
                      <li><?php echo _l('first_row_headers'); ?></li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 text-right">
                  <a href="<?php echo module_dir_url('import_projects', 'uploads/plantilla_proyectos.csv'); ?>" 
                     class="btn btn-default mr-2" download>
                    <i class="fa fa-file-download"></i> <?php echo _l('download_template'); ?>
                  </a>
                  <button type="submit" class="btn btn-primary" id="submit-import">
                    <i class="fa fa-upload"></i> <?php echo _l('upload_and_preview'); ?>
                  </button>
                </div>
              </div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
  $(function() {
    // Inicializar popover
    $('[data-toggle="popover"]').popover({
      trigger: 'hover',
      html: true
    });

    // Validación del formulario
    $('#import_form').on('submit', function(e) {
      var fileInput = $('#file_csv')[0];
      var maxSize = $('#file_csv').data('max-size') * 1024 * 1024;
      
      if (fileInput.files.length > 0) {
        if (fileInput.files[0].size > maxSize) {
          alert_float('danger', '<?php echo _l('file_size_exceeds_limit'); ?>');
          e.preventDefault();
          return false;
        }
        
        // Mostrar spinner
        $('#submit-import').prop('disabled', true).prepend('<i class="fa fa-spinner fa-spin mr-1"></i>');
      }
    });

    // Mostrar nombre del archivo seleccionado
    $('#file_csv').on('change', function() {
      var fileName = $(this).val().split('\\').pop();
      $(this).next('.custom-file-label').html(fileName);
    });
  });
</script>
</body>
</html>