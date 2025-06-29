<?php
$conexion = new mysqli("localhost", "root", "", "datos_buscador");
if ($conexion->connect_error) {
  die("Error de conexión: " . $conexion->connect_error);
}

$ciudades = [];
$result_ciudades = $conexion->query("SELECT DISTINCT Ciudad FROM datos_generales ORDER BY Ciudad ASC");
while ($row = $result_ciudades->fetch_assoc()) {
  $ciudades[] = $row['Ciudad'];
}

$tipos = [];
$result_tipos = $conexion->query("SELECT DISTINCT Tipo FROM datos_generales ORDER BY Tipo ASC");
while ($row = $result_tipos->fetch_assoc()) {
  $tipos[] = $row['Tipo'];
}

$ciudadSeleccionada = $_POST['ciudad'] ?? '';
$tipoSeleccionado = $_POST['tipo'] ?? '';
$precioMin = $_POST['precio_min'] ?? '';
$precioMax = $_POST['precio_max'] ?? '';

$resultados = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
  $sql = "SELECT * FROM (
            SELECT *,
              CAST(REPLACE(REPLACE(Precio, '$', ''), ',', '') AS DECIMAL(10,2)) AS PrecioNum
            FROM datos_generales
          ) AS filtrado
          WHERE 1=1";
  $params = [];
  $tipos_datos = '';

  if ($ciudadSeleccionada !== '') {
    $sql .= " AND Ciudad = ?";
    $params[] = $ciudadSeleccionada;
    $tipos_datos .= 's';
  }

  if ($tipoSeleccionado !== '') {
    $sql .= " AND Tipo = ?";
    $params[] = $tipoSeleccionado;
    $tipos_datos .= 's';
  }

  if ($precioMin !== '' && $precioMax !== '') {
    $sql .= " AND PrecioNum BETWEEN ? AND ?";
    $params[] = floatval($precioMin);
    $params[] = floatval($precioMax);
    $tipos_datos .= 'dd';
  }

  $stmt = $conexion->prepare($sql);
  if ($params) {
    $stmt->bind_param($tipos_datos, ...$params);
  }
  $stmt->execute();
  $res = $stmt->get_result();

  while ($fila = $res->fetch_assoc()) {
    $resultados[] = $fila;
  }

  $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mostrar_todos'])) {
  $res = $conexion->query("SELECT * FROM datos_generales");
  while ($fila = $res->fetch_assoc()) {
    $resultados[] = $fila;
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link type="text/css" rel="stylesheet" href="css/materialize.min.css"/>
  <link type="text/css" rel="stylesheet" href="css/customColors.css"/>
  <link type="text/css" rel="stylesheet" href="css/ion.rangeSlider.css"/>
  <link type="text/css" rel="stylesheet" href="css/ion.rangeSlider.skinFlat.css"/>
  <link type="text/css" rel="stylesheet" href="css/index.css"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Formulario</title>
</head>
<body>
  <video src="img/video.mp4" id="vidFondo"></video>

  <div class="contenedor">
    <div class="card rowTitulo">
      <h1>Buscador</h1>
    </div>

    <div class="colFiltros">
      <form action="" method="post" id="formulario">
        <div class="filtrosContenido">
          <div class="tituloFiltros">
            <h5>Realiza una búsqueda personalizada</h5>
          </div>

          <div class="filtroCiudad input-field">
            <select name="ciudad" id="selectCiudad" class="browser-default">
              <option value="" <?= ($ciudadSeleccionada === '') ? 'selected' : '' ?>>Ciudad</option>
              <?php foreach ($ciudades as $ciudad): ?>
                <option value="<?= htmlspecialchars($ciudad) ?>" <?= ($ciudadSeleccionada === $ciudad) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($ciudad) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="filtroTipo input-field">
            <select name="tipo" id="selectTipo" class="browser-default">
              <option value="" <?= ($tipoSeleccionado === '') ? 'selected' : '' ?>>Tipo</option>
              <?php foreach ($tipos as $tipo): ?>
                <option value="<?= htmlspecialchars($tipo) ?>" <?= ($tipoSeleccionado === $tipo) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($tipo) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="filtroPrecio">
            <label for="rangoPrecio">Precio:</label>
            <input type="text" id="rangoPrecio" name="precio_rango" value="" />
            <input type="hidden" id="precio_min" name="precio_min" value="<?= htmlspecialchars($precioMin) ?>">
            <input type="hidden" id="precio_max" name="precio_max" value="<?= htmlspecialchars($precioMax) ?>">
          </div>

          <div class="botonField">
            <input type="submit" class="btn white" value="Buscar" id="submitButton" name="buscar">
          </div>
        </div>
      </form>
    </div>

    <div class="colContenido">
      <div class="tituloContenido card">
        <h5>Resultados de la búsqueda:</h5>
        <div class="divider"></div>

        <!-- Botón para mostrar todos -->
        <form action="" method="post" id="formulario_mostrar_todos">
          <button type="submit" name="mostrar_todos" value="1" class="btn-flat waves-effect">Mostrar Todos</button>
        </form>

        <!-- Resultados -->
        <?php if (count($resultados) > 0): ?>
          <?php foreach ($resultados as $res): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px; background-color:#f9f9f9">
              <p><strong>Dirección:</strong> <?= htmlspecialchars($res['Direccion']) ?></p>
              <p><strong>Ciudad:</strong> <?= htmlspecialchars($res['Ciudad']) ?></p>
              <p><strong>Teléfono:</strong> <?= htmlspecialchars($res['Telefono']) ?></p>
              <p><strong>Código Postal:</strong> <?= htmlspecialchars($res['Codigo_Postal']) ?></p>
              <p><strong>Tipo:</strong> <?= htmlspecialchars($res['Tipo']) ?></p>
              <p><strong>Precio:</strong> <?= htmlspecialchars($res['Precio']) ?></p>
            </div>
          <?php endforeach; ?>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['buscar']) || isset($_POST['mostrar_todos']))): ?>
          <p>No se encontraron resultados con esos filtros.</p>
        <?php else: ?>
          <p>Aquí aparecerán los resultados de la búsqueda.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script type="text/javascript" src="js/jquery-3.0.0.js"></script>
  <script type="text/javascript" src="js/ion.rangeSlider.min.js"></script>
  <script type="text/javascript" src="js/materialize.min.js"></script>
  <script>
    $(document).ready(function () {
      $("#rangoPrecio").ionRangeSlider({
        type: "double",
        min: 0,
        max: 100000,
        from: <?= isset($_POST['precio_min']) ? (int)$_POST['precio_min'] : 200 ?>,
        to: <?= isset($_POST['precio_max']) ? (int)$_POST['precio_max'] : 80000 ?>,
        prefix: "$",
        onFinish: function (data) {
          $("#precio_min").val(data.from);
          $("#precio_max").val(data.to);
        }
      });
    });
  </script>
</body>
</html>
