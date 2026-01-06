<?php
require '../config/db.php';
require '../src/Financeiro.php';

$db = (new Database())->connect();
$financeiro = new Financeiro($db);

// Atualizar transação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id_editar'])) {
    $id = $_POST['id_editar'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $tipo = $_POST['tipo'];
    $data = $_POST['data'];
    $cidade = $_POST['cidade'];

    $financeiro->atualizarTransacao($id, $descricao, $valor, $tipo, $data, $cidade);
    header("Location: index.php?cidade=" . urlencode($cidade));
    exit;
}

$mensagem = '';

// Criar primeira cidade caso não existam
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cidade']) && empty($_POST['id_editar'])) {
    $cidade = trim($_POST['cidade']);
    if ($cidade) {
        $sucesso = $financeiro->criarCidade($cidade);
        if ($sucesso) {
            $mensagem = "Cidade '$cidade' criada com sucesso!";
            header("Location: index.php");
            exit;
        } else {
            $mensagem = "Cidade '$cidade' já existe!";
        }
    }
}

// Listar cidades existentes
$cidades = $financeiro->listarCidades();

// Captura cidade selecionada via GET
$cidadeSelecionada = $_GET['cidade'] ?? null;

// Datas (padrão: últimos 7 dias)
$dataFim = $_GET['data_fim'] ?? date('Y-m-d');
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-7 days'));

// mostra tabela e cards só se houver cidades
$mostrarTabela = !empty($cidades); 

if ($mostrarTabela) {

    if (empty($cidadeSelecionada) && $dataInicio && $dataFim) {
        $dados = $financeiro->listarPorData($dataInicio, $dataFim);
        $saldo = $financeiro->saldoPorData($dataInicio, $dataFim);

    } elseif (empty($cidadeSelecionada)) {
        $dados = $financeiro->listar();
        $saldo = $financeiro->saldo();

    } elseif ($cidadeSelecionada && $dataInicio && $dataFim) {
        $dados = $financeiro->listarPorCidadeEData($cidadeSelecionada, $dataInicio, $dataFim);
        $saldo = $financeiro->saldoPorCidadeEData($cidadeSelecionada, $dataInicio, $dataFim);

    } else {
        $dados = $financeiro->listarPorCidade($cidadeSelecionada);
        $saldo = $financeiro->saldoPorCidade($cidadeSelecionada);
    }

    $receitas = $saldo['receitas'] ?? 0;
    $despesas = $saldo['despesas'] ?? 0;
    $total = $receitas - $despesas;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sistema Financeiro</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cidade-filtro {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .cidade-filtro .btn {
            font-size: 14px;
            padding: 10px 18px;
            text-decoration: none;
            background: #396afc;
            color: #fff;
            border-radius: 8px;
            transition: 0.3s;
        }
        .cidade-filtro .btn:hover { background: #2948ff; }
        .cidade-filtro form { display: flex; justify-content: center; gap: 10px; }
        .cidade-filtro input[type="text"] {
            padding: 10px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
        }
        .cidade-filtro button {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            background: #396afc;
            color: #fff;
            cursor: pointer;
        }
        .mensagem {
            text-align: center;
            font-weight: bold;
            color: yellow;
            margin-bottom: 15px;
        }
        .modal {
    display: none;              /* começa fechado */
    position: fixed;
    z-index: 999;
    inset: 0;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: #fff;
    padding: 20px;
    border-radius: 14px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;          /* 👈 limite de altura */
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-content h2 {
    margin-bottom: 15px;
}

/* container da lista */
.lista-cidades {
    overflow-y: auto;          /* 👈 scroll */
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
    padding-right: 5px;
}

/* botões */
.lista-cidades .btn {
    padding: 10px;
    font-size: 14px;
    text-align: center;
    white-space: normal;       /* 👈 quebra linha */
    word-break: break-word;    /* 👈 nome grande */
}
    </style>
</head>
<body>

<h1>💰 Controle Financeiro</h1>

<?php if (!$mostrarTabela): ?>
    <div class="sem-cidade">
        <div class="mensagem">
            Nenhuma cidade cadastrada. Crie a primeira cidade para começar.
        </div>

        <form method="POST">
            <input
                type="text"
                name="cidade"
                placeholder="Digite o nome da cidade"
                required
            >
            <button type="submit">Criar Cidade</button>
        </form>
    </div>
<?php endif; ?>

<?php if ($mostrarTabela): ?>
    <div class="acoes-topo">
        <button type="button" class="btn btn-topo" onclick="abrirModalCidades()">
            Trocar Cidade
        </button>

        <a href="novo.php" class="btn btn-topo">
            + Nova Transação
        </a>
    </div>
<?php endif; ?>

<?php if ($mostrarTabela && empty($cidadeSelecionada)): ?>
    <form method="GET" class="filtro-data">
        <input
            type="date"
            name="data_inicio"
            value="<?= $dataInicio ?>"
            required
        >

        <input
            type="date"
            name="data_fim"
            value="<?= $dataFim ?>"
            required
        >

        <button type="submit" class="btn">Filtrar</button>
    </form>
<?php endif; ?>

<?php if ($mostrarTabela && isset($dados)): ?>
    <div class="cards">
        <div class="card receita">
            Receitas: R$ <?= number_format($receitas,2,',','.') ?>
        </div>
        <div class="card despesa">
            Despesas: R$ <?= number_format($despesas,2,',','.') ?>
        </div>
        <div class="card total">
            Saldo: R$ <?= number_format($total,2,',','.') ?>
        </div>
    </div>

    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; margin-top:15px;">
        <tr>
            <th>Descrição</th>
            <th>Valor</th>
            <th>Tipo</th>
            <th>Data</th>
            <th>Ações</th>
            <th>Cidade</th>
        </tr>

        <?php while ($row = $dados->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= $row['descricao'] ?></td>
                <td>R$ <?= number_format($row['valor'],2,',','.') ?></td>
                <td><?= ucfirst($row['tipo']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
                <td>
                    <a href="excluir.php?id=<?= $row['id'] ?>" class="delete">🗑️</a>
                    <button
                        type="button"
                        class="btn-editar"
                        onclick="abrirModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                        ✏️
                    </button>
                </td>
                <td><?= $row['cidade'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

<?php elseif ($mostrarTabela && !isset($dados)): ?>
    <p style="text-align:center; color:#fff; margin-top:30px;">
        👉 Selecione uma cidade para visualizar os dados.
    </p>
<?php endif; ?>

<!-- MODAL DE EDIÇÃO -->
<div id="modal" class="modal">
    <div class="modal-content">
        <h2>Editar Transação</h2>
        <form method="POST">
            <input type="hidden" name="id_editar" id="id_editar">
            <input type="text" name="descricao" id="descricao" placeholder="Descrição" required><br><br>
            <input type="number" step="0.01" name="valor" id="valor" placeholder="Valor" required><br><br>
            <select name="tipo" id="tipo" required>
                <option value="receita">Receita</option>
                <option value="despesa">Despesa</option>
            </select><br><br>
            <input type="date" name="data" id="data" required><br><br>
            <input type="text" name="cidade" id="cidade" placeholder="Cidade" required><br><br>
            <button type="submit">Salvar</button>
            <button type="button" onclick="fecharModal()">Cancelar</button>
        </form>
    </div>
</div>

<!-- MODAL DE CIDADES -->
<div id="modal-cidades" class="modal">
    <div class="modal-content">
        <h2>Selecione a Cidade</h2>

        <button class="btn" onclick="selecionarCidade('')">📌 Listar Todas</button>

        <div class="lista-cidades">
            <?php foreach($cidades as $cidade): ?>
                <button class="btn" onclick="selecionarCidade('<?= $cidade ?>')">
                    <?= $cidade ?>
                </button>
            <?php endforeach; ?>
        </div>

        <button type="button" onclick="fecharModalCidades()">Cancelar</button>
    </div>
</div>

<script>
function abrirModal(dados) {
    document.getElementById('id_editar').value = dados.id;
    document.getElementById('descricao').value = dados.descricao;
    document.getElementById('valor').value = dados.valor;
    document.getElementById('tipo').value = dados.tipo;
    document.getElementById('data').value = dados.data;
    document.getElementById('cidade').value = dados.cidade;
    document.getElementById('modal').style.display = 'flex';
}

function fecharModal() {
    document.getElementById('modal').style.display = 'none';
}

function abrirModalCidades() {
    document.getElementById('modal-cidades').style.display = 'flex';
}

function fecharModalCidades() {
    document.getElementById('modal-cidades').style.display = 'none';
}

function selecionarCidade(cidade) {

    const params = new URLSearchParams(window.location.search);

    if (cidade === "") {
        // remove cidade, mantém datas
        params.delete('cidade');
    } else {
        params.set('cidade', cidade);
    }

    window.location.href = "index.php?" + params.toString();
}
</script>

</body>
</html>
