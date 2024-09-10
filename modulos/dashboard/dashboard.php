<?php
session_start();
include ('../../assets/bd/conexao.php');

$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'data-desc';
$usuario_id = $_SESSION['user_id'];
$order_by = 'data DESC';

$filter = isset($_GET['filtro']) ? $_GET['filtro'] : '';

switch ($filtro) {
  case 'data-asc':
      $order_by = 'data ASC';
      break;
  case 'data-desc':
      $order_by = 'data DESC';
      break;
  case 'valor-asc':
      $order_by = 'valor ASC';
      break;
  case 'valor-desc':
      $order_by = 'valor DESC';
      break;
  case 'descricao-asc':
      $order_by = 'descricao ASC';
      break;
  case 'descricao-desc':
      $order_by = 'descricao DESC';
      break;
}

$sql = "SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY $order_by";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <title>Gerenciamento de Finanças</title>
</head>
<body class="bg-gray-100">

    <!-- Header -->
    <header class="bg-purple-700 p-4 flex justify-between items-center">
        <button class="bg-gray-400 text-white py-2 px-4 rounded">Voltar</button>
        <h1 class="text-white text-2xl font-bold">Gerenciamento de Finanças</h1>
        <div class="space-x-2">
            <button class="bg-gray-400 text-white py-2 px-4 rounded">Meu Perfil</button>
            <button class="bg-gray-400 text-white py-2 px-4 rounded">Sair</button>
        </div>
    </header>

    <main class="p-6">

        <?php
            // Consultar o banco de dados para obter todas as transações
            $sql = "SELECT SUM(valor) AS total FROM transacoes WHERE usuario_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $row = $resultado->fetch_assoc();
            $saldo = $row['total'];

            // Consultar o banco de dados para obter o total de entradas
            $sql = "SELECT SUM(valor) AS total FROM transacoes WHERE usuario_id = ? AND valor > 0";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $row = $resultado->fetch_assoc();
            $entradas = $row['total'];

            // Consultar o banco de dados para obter o total de saídas
            $sql = "SELECT SUM(valor) AS total FROM transacoes WHERE usuario_id = ? AND valor < 0";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $row = $resultado->fetch_assoc();
            $saidas = abs($row['total']); // Valor absoluto para garantir que seja positivo
        ?>

        <!--div transação-->
        <div class="flex items-center justify-center mb-8">
            <button class="bg-purple-600 text-white justify-center py-2 px-4 rounded hover:bg-purple-500">
                + Nova Transação
            </button>
        </div>

        <div class="flex justify-center items-center">
            <div class="p-4 rounded-lg shadow-md text-center justify-end">
                <p class="font-bold text-green-600">Entradas</p>
                <p class="text-xl font-semibold"><?php echo number_format($saldo, 2);?></p>
            </div>
        </div>


        <!-- Entradas e Saídas -->
        <div class="flex justify-between items-center mb-8">
            <div class="bg-white p-4 rounded-lg shadow-md w-1/3 text-center">
                <p class="font-bold text-green-600">Entradas</p>
                <p class="text-xl font-semibold"><?php echo number_format($entradas, 2);?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-md w-1/3 text-center">
                <p class="font-bold text-red-600">Saídas</p>
                <p class="text-xl font-semibold"><?php echo number_format($saidas, 2);?></p>
            </div>
        </div>


        <!-- Histórico -->

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-bold mb-4">Histórico</h3>
            <div class="flex items-center mb-4">
                <label for="filter" class="mr-2 font-semibold">Filtrar por:</label>
                <select id="filter" class="border border-gray-300 rounded p-2">
                    <option value="data-asc" <?php echo ($filtro == 'data-asc') ? 'selected' : ''; ?>>Data (Mais antigos)</option>
                    <option value="data-desc" <?php echo ($filtro == 'data-desc') ? 'selected' : ''; ?>>Data (Mais recentes)</option>
                    <option value="valor-asc" <?php echo ($filtro == 'valor-asc') ? 'selected' : ''; ?>>Valor (Menor para maior)</option>
                    <option value="valor-desc" <?php echo ($filtro == 'valor-desc') ? 'selected' : ''; ?>>Valor (Maior para menor)</option>
                    <option value="descricao-asc" <?php echo ($filtro == 'descricao-asc') ? 'selected' : ''; ?>>Descrição (A-Z)</option>
                    <option value="descricao-desc" <?php echo ($filtro == 'descricao-desc') ? 'selected' : ''; ?>>Descrição (Z-A)</option>
                </select>
                <div class="filtro-nav">
                    <label for="filtroSearch"></label>
                    <input type="text" placeholder="Procurar" class="ml-4 border border-gray-300 rounded p-2 w-full max-w-xs">
                </div>
            </div>

            <!-- Tabela de Transações -->
            <?php
            include ('../../assets/bd/conexao.php');

            if (isset($_SESSION['user_id'])) {
              $usuario_id = $_SESSION['user_id'];
          
              $sql = "SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY data DESC";
              $stmt = $conn->prepare($sql);
              $stmt->bind_param('i', $usuario_id);
              $stmt->execute();
              $resultado = $stmt->get_result();
              
              // Verificar se há transações
              if ($resultado->num_rows > 0) {
                // Exibir as transações no histórico
                while ($row = $resultado->fetch_assoc()) {
                  echo '<li>';
                  echo '<span id="descricao" class="">' . $row['descricao'] . '</span>';
                  echo '<span id="data" class="data">' . $row['data'] . '</span>';
                  echo '<span id="valor" class="valor">' . $row['valor'] . '</span>';
                  echo '<div>';
                  echo '<button id="" class="editar"><a href="../../modulos/transacoes/editar_transacao.php?id=' . $row['id'] . '">Editar</a></button>';
                  echo '<button id="" class="excluir" data-id="' . $row['id'] . '">Excluir</button>';
                  echo '</div>';
                  echo '</li>';
                }
              } else {
                echo '<li>Nenhuma transação encontrada.</li>';
              }
            }
        ?>
        </div>
    </main>

</body>
</html>