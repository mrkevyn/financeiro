<?php

class Financeiro {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM transacoes ORDER BY data ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function inserir($descricao, $valor, $tipo, $data, $cidade) {

        $cidade = rtrim($cidade); // somente final

        $sql = "INSERT INTO transacoes (descricao, valor, tipo, data, cidade)
                VALUES (:descricao, :valor, :tipo, :data, :cidade)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':descricao' => $descricao,
            ':valor'     => $valor,
            ':tipo'      => $tipo,
            ':data'      => $data,
            ':cidade'    => $cidade
        ]);
    }

    public function criarCidade($cidade) {
        // Aqui verificamos se já existe a cidade
        $sqlCheck = "SELECT 1 FROM transacoes WHERE cidade = :cidade LIMIT 1";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->execute([':cidade' => $cidade]);

        if ($stmtCheck->fetch()) {
            return false; // cidade já existe
        }

        // Inserimos uma transação fictícia com valores nulos só para registrar a cidade
        $sql = "INSERT INTO transacoes (descricao, valor, tipo, data, cidade)
                VALUES (:descricao, :valor, :tipo, :data, :cidade)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':descricao' => 'Registro inicial', // descrição fictícia
            ':valor' => 0,
            ':tipo' => 'receita',
            ':data' => date('Y-m-d'),
            ':cidade' => $cidade
        ]);
    }

    public function excluir($id) {
        $sql = "DELETE FROM transacoes WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function saldo() {
        $sql = "
        SELECT 
        SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as receitas,
        SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as despesas
        FROM transacoes";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarCidades() {
        $sql = "SELECT DISTINCT TRIM(cidade) AS cidade FROM transacoes ORDER BY cidade ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function listarPorCidade($cidade) {
        $sql = "SELECT * FROM transacoes WHERE cidade = :cidade ORDER BY data ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':cidade' => $cidade]);
        return $stmt;
    }

    public function saldoPorCidade($cidade) {
        $sql = "
        SELECT 
            SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as receitas,
            SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as despesas
        FROM transacoes
        WHERE cidade = :cidade";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':cidade' => $cidade]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

     public function atualizarTransacao($id, $descricao, $valor, $tipo, $data, $cidade) {
        $sql = "UPDATE transacoes
                SET descricao = :descricao, valor = :valor, tipo = :tipo, data = :data, cidade = :cidade
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':descricao' => $descricao,
            ':valor' => $valor,
            ':tipo' => $tipo,
            ':data' => $data,
            ':cidade' => $cidade,
            ':id' => $id
        ]);
    }

    public function listarPorCidadeEData($cidade, $inicio, $fim) {
        $sql = "
            SELECT *
            FROM transacoes
            WHERE cidade = :cidade
            AND DATE(data) BETWEEN :inicio AND :fim
            ORDER BY data ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':cidade', $cidade);
        $stmt->bindValue(':inicio', $inicio);
        $stmt->bindValue(':fim', $fim);
        $stmt->execute();

        return $stmt;
    }

    public function saldoPorCidadeEData($cidade, $inicio, $fim) {
        $sql = "
            SELECT
                SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) AS receitas,
                SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) AS despesas
            FROM transacoes
            WHERE cidade = :cidade
            AND DATE(data) BETWEEN :inicio AND :fim
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':cidade', $cidade);
        $stmt->bindValue(':inicio', $inicio);
        $stmt->bindValue(':fim', $fim);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarPorData($inicio, $fim) {
        $sql = "
            SELECT *
            FROM transacoes
            WHERE data BETWEEN :inicio AND :fim
            ORDER BY data ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':inicio', $inicio . ' 00:00:00');
        $stmt->bindValue(':fim', $fim . ' 23:59:59');
        $stmt->execute();

        return $stmt;
    }

    public function saldoPorData($inicio, $fim) {
        $sql = "
            SELECT
                SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) AS receitas,
                SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) AS despesas
            FROM transacoes
            WHERE data BETWEEN :inicio AND :fim
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':inicio', $inicio . ' 00:00:00');
        $stmt->bindValue(':fim', $fim . ' 23:59:59');
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
