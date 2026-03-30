# 💰 Sistema de Controle Financeiro

Sistema web para gerenciamento de transações financeiras, permitindo controle de receitas, despesas, saldo e análise por cidade e período.

O projeto foi desenvolvido com foco em organização de dados, consultas SQL e geração de relatórios dinâmicos.

---

## 🚀 Funcionalidades

* 📌 Cadastro de transações (receita e despesa)
* ✏️ Edição de transações
* 🗑️ Exclusão de registros
* 🏙️ Organização por cidades
* 📊 Cálculo automático de saldo:

  * Total de receitas
  * Total de despesas
  * Saldo final
* 📅 Filtros por:

  * Período (data inicial e final)
  * Cidade
  * Cidade + período
* 📋 Listagem completa de transações
* 🔍 Consulta dinâmica com múltiplos filtros

---

## 🧠 Regras de Negócio

### 💸 Tipos de Transação

| Tipo    | Descrição           |
| ------- | ------------------- |
| Receita | Entrada de dinheiro |
| Despesa | Saída de dinheiro   |

---

### 🏙️ Controle de Cidades

* Cada cidade é registrada no sistema
* Não permite duplicação de cidades
* Cidades são utilizadas como base para filtragem de dados

---

### 📊 Cálculo de Saldo

O saldo é calculado diretamente no banco usando SQL:

```sql
SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END)
SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END)
```

👉 Isso garante:

* Performance
* Precisão nos dados
* Evita cálculos no backend

---

### 📅 Filtros de Dados

O sistema permite consultas avançadas:

#### ➤ Por período

* Filtra transações entre duas datas

#### ➤ Por cidade

* Exibe apenas registros da cidade selecionada

#### ➤ Por cidade + período

* Combina múltiplos filtros

---

## 📡 Fluxos do Sistema

### ➤ Criar Cidade

1. Usuário informa o nome da cidade
2. Sistema valida duplicidade
3. Registra cidade no banco

---

### ➤ Registrar Transação

1. Usuário informa:

   * Descrição
   * Valor
   * Tipo
   * Data
   * Cidade
2. Sistema salva no banco

---

### ➤ Visualizar Dados

* Lista transações
* Calcula saldo automaticamente
* Permite filtragem dinâmica

---

### ➤ Editar Transação

* Atualiza dados existentes via modal
* Persistência imediata no banco

---

## 🏗️ Estrutura do Projeto

```
config/
 └── db.php

src/
 └── Financeiro.php

public/
 └── index.php
```

---

## 🧩 Classe Principal

### `Financeiro`

Responsável por toda lógica de acesso a dados:

* Inserção de transações
* Atualização e exclusão
* Consultas por cidade
* Consultas por período
* Cálculo de saldo
* Listagem dinâmica

---

## 🛠️ Tecnologias Utilizadas

* PHP (PDO)
* SQL (MySQL ou compatível)
* HTML + CSS
* JavaScript (interações e modais)

---

## 📊 Exemplos de Consultas

### 🔹 Saldo geral

```sql
SELECT 
  SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as receitas,
  SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as despesas
FROM transacoes
```

---

### 🔹 Filtro por cidade e período

```sql
SELECT *
FROM transacoes
WHERE cidade = :cidade
AND DATE(data) BETWEEN :inicio AND :fim
```

---

## 💡 Diferenciais Técnicos

* Uso de **SQL avançado com agregações (SUM + CASE)**
* Filtros dinâmicos combinados (cidade + data)
* Separação de responsabilidades (classe Financeiro)
* Uso de **Prepared Statements (PDO)** → segurança contra SQL Injection
* Interface com modais para melhor experiência do usuário
* Organização dos dados para análise financeira simples

---

## 🎯 Possíveis Melhorias

* Autenticação de usuários
* Dashboard com gráficos (Chart.js)
* Exportação para Excel/PDF
* API REST para integração externa
* Controle de categorias financeiras

---

## 👨‍💻 Autor

Sistema desenvolvido como projeto de portfólio com foco em backend PHP e manipulação de dados, demonstrando uso de SQL para análise e organização de informações financeiras.
