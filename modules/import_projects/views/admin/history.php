<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin">Historial de Importaciones</h4>
            <hr>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Archivo</th>
                  <th>Total</th>
                  <th>Usuario</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($import_history as $h): ?>
                  <tr>
                    <td><?= $h['date']; ?></td>
                    <td><?= $h['file_name']; ?></td>
                    <td><?= $h['total_imported']; ?></td>
                    <td><?= get_staff_full_name($h['staff_id']); ?></td>
                    <td>
                      <a href="<?= admin_url('import_projects/detail/' . $h['id']) ?>" class="btn btn-sm btn-info">Ver Detalle</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>
