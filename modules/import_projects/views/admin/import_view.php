<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin">Importar desde Excel</h4>
            <hr>
            <?php echo form_open_multipart(admin_url('import_projects/preview_upload')); ?>
              <div class="form-group">
                <label for="file_excel">Selecciona un archivo Excel (.xlsx)</label>
                <input type="file" class="form-control" name="file_excel" required accept=".xlsx">
              </div>
              <button type="submit" class="btn btn-primary">Vista Previa</button>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>
