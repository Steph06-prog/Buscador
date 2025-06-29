<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "datos_buscador";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

$json = file_get_contents("data-1.json");
$datos = json_decode($json, true);

foreach ($datos as $fila) {
  $id = $conn->real_escape_string($fila['Id']);
  $direccion = $conn->real_escape_string($fila['Direccion']);
  $ciudad = $conn->real_escape_string($fila['Ciudad']);
  $telefono = $conn->real_escape_string($fila['Telefono']);
  $codigo = $conn->real_escape_string($fila['Codigo_Postal']);
  $tipo = $conn->real_escape_string($fila['Tipo']);
  $precio = $conn->real_escape_string($fila['Precio']);

  $sql = "INSERT INTO datos_generales (Id, Direccion, Ciudad, Telefono, Codigo_Postal, Tipo, Precio)
          VALUES ('$id', '$direccion', '$ciudad', '$telefono', '$codigo', '$tipo', '$precio')";

  $conn->query($sql);
}

echo "Datos insertados correctamente.";
$conn->close();
?>
