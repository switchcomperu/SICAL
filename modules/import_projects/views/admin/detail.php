<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin">Detalle de Registros Importados</h4>
            <hr>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Proyecto</th>
                  <th>Cliente</th>
                  <th>Fecha Inicio</th>
                  <th>Fecha Fin</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $r): ?>
                  <tr>
                    <td><?= $r['project_name']; ?></td>
                    <td><?= $r['client']; ?></td>
                    <td><?= $r['start_date']; ?></td>
                    <td><?= $r['end_date']; ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <a href="<?= admin_url('import_projects/history') ?>" class="btn btn-default">Volver</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>
