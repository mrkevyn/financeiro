<?php
require '../config/db.php';
require '../src/Financeiro.php';

$db = (new Database())->connect();
$financeiro = new Financeiro($db);

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

/* Se confirmou → exclui */
if (isset($_POST['confirmar'])) {
    $financeiro->excluir($id);
    header("Location: index.php");
    exit;
}

/* Se cancelou → volta */
if (isset($_POST['cancelar'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirmar exclusão</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>⚠️ Confirmar exclusão</h1>

<div style="
    max-width: 400px;
    margin: 50px auto;
    background: white;
    padding: 30px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 10px 20px rgba(0,0,0,.4);
">

    <p style="font-size:18px; margin-bottom:25px;">
        Tem certeza que deseja excluir esta transação?
    </p>

    <form method="POST" style="display:flex; flex-direction:column; gap:15px;">
        <button name="confirmar" style="background:#e84118;color:white;padding:14px;border:none;border-radius:10px;font-size:16px">
            ✅ Sim, apagar
        </button>

        <button name="cancelar" style="background:#718093;color:white;padding:14px;border:none;border-radius:10px;font-size:16px">
            ❌ Cancelar
        </button>
    </form>

</div>

</body>
</html>
