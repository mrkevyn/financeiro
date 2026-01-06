<?php
require '../config/db.php';
require '../src/Financeiro.php';

$db = (new Database())->connect();
$financeiro = new Financeiro($db);

// Listar cidades existentes para o datalist
$cidades = $financeiro->listarCidades();

if ($_POST) {
    $financeiro->inserir(
        $_POST['descricao'],
        $_POST['valor'],
        $_POST['tipo'],
        $_POST['data'],
        $_POST['cidade']  // cidade
    );

    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Nova Transação</title>
    <link rel="stylesheet" href="style.css">
    <style>
form {
    max-width: 480px;
    margin: 40px auto;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(15px);
    padding: 35px 30px;
    border-radius: 18px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.4);
    display: flex;
    flex-direction: column;
    gap: 18px;
}
input, select {
    padding: 14px 15px;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    outline: none;
    background: #ffffff;
    transition: 0.3s;
}
input:focus, select:focus {
    transform: scale(1.02);
    box-shadow: 0 0 0 2px #396afc;
}
button {
    padding: 14px;
    border: none;
    border-radius: 14px;
    font-size: 16px;
    font-weight: bold;
    letter-spacing: .5px;
    background: linear-gradient(135deg, #11998e, #38ef7d);
    color: #fff;
    cursor: pointer;
    transition: 0.3s;
}
button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.35);
}
h1 { margin-top: 40px; }
input::placeholder { color: #888; }
    </style>
</head>
<body>

<h1>Nova Transação</h1>

<form method="POST">

    <input type="text" name="descricao" placeholder="Descrição" required>

    <input type="number" step="0.01" name="valor" placeholder="Valor" required>

    <select name="tipo">
        <option value="receita">Receita</option>
        <option value="despesa">Despesa</option>
    </select>

    <input type="date" name="data" value="<?= date('Y-m-d') ?>" required>

    <!-- Select com opção de digitar nova cidade -->
    <input list="cidades-list" name="cidade" placeholder="Cidade" required>
    <datalist id="cidades-list">
        <?php foreach($cidades as $cidade): ?>
            <option value="<?= htmlspecialchars($cidade) ?>">
        <?php endforeach; ?>
    </datalist>

    <button type="submit">Salvar</button>

</form>

</body>
</html>
