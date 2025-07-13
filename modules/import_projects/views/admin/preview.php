<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin">Vista previa de los datos</h4>
            <hr>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Proyecto</th>
                  <th>Cliente</th>
                  <th>Inicio</th>
                  <th>Fin</th>
                  <th>Errores</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $r): ?>
                  <tr class="<?php echo count($r['errors']) ? 'bg-danger text-white' : ''; ?>">
                    <td><?= $r['project']; ?></td>
                    <td><?= $r['client']; ?></td>
                    <td><?= $r['start']; ?></td>
                    <td><?= $r['end']; ?></td>
                    <td>
                      <?php foreach ($r['errors'] as $error): ?>
                        <div><?= $error; ?></div>
                      <?php endforeach; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <hr>
            <a href="<?= admin_url('import_projects/confirm_upload') ?>" class="btn btn-success">Confirmar Importación</a>
            <a href="<?= admin_url('import_projects') ?>" class="btn btn-secondary">Cancelar</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>
