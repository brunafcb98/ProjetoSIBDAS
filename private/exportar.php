<?php
require_once 'includes/funcoes.php';
redirect_if_not_logged();
start_session();

require_once __DIR__ . '/../assets/fpdf/fpdf.php';

$tabela = $_GET['tabela'] ?? '';
$formato = $_GET['formato'] ?? '';

$tabelasPermitidas = ['equipamentos', 'fornecedores', 'localizacoes', 'acessorios', 'consumiveis'];
$formatosPermitidos = ['csv', 'json', 'pdf'];

if (!in_array($tabela, $tabelasPermitidas) || !in_array($formato, $formatosPermitidos)) {
    die('Pedido de exportação inválido.');
}

$titulo = '';
$colunas = [];
$linhas = [];

try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8",
        MYSQL_USERNAME,
        MYSQL_PASSWORD
    );
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ---------- DEFINIÇÃO DAS COLUNAS E QUERY POR TABELA ----------
    switch ($tabela) {

        case 'equipamentos':
            $titulo = 'Equipamentos';
            $colunas = ['Código', 'Designação', 'Categoria', 'Marca', 'Estado', 'Criticidade', 'Localização'];
            $stmt = $ligacao->query("
                SELECT e.codigo_interno, e.designacao, e.categoria, e.marca, e.estado, e.criticidade,
                       l.servico, l.sala_internamento_gabinete
                FROM equipamentos e
                LEFT JOIN localizacoes l ON e.id_localizacao = l.id
                WHERE e.apagado = 0
            ");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $linhas[] = [
                    $r['codigo_interno'], $r['designacao'], $r['categoria'], $r['marca'],
                    $r['estado'], $r['criticidade'], $r['servico'] . ' - ' . $r['sala_internamento_gabinete']
                ];
            }
            break;

        case 'fornecedores':
            $titulo = 'Fornecedores';
            $colunas = ['Nome', 'Tipo', 'Telefone', 'Email', 'Pessoa de Contacto', 'Telefone Contacto'];
            $stmt = $ligacao->query("SELECT nome_empresa, tipo, telefone, email, pessoa_contacto, telefone_pessoa_contacto FROM fornecedores WHERE apagado = 0");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $linhas[] = [
                    $r['nome_empresa'], $r['tipo'], $r['telefone'], $r['email'],
                    $r['pessoa_contacto'], $r['telefone_pessoa_contacto']
                ];
            }
            break;

        case 'localizacoes':
            $titulo = 'Localizações';
            $colunas = ['Edifício', 'Piso', 'Serviço', 'Internamento/Sala/Gabinete'];
            $stmt = $ligacao->query("SELECT edificio, piso, servico, sala_internamento_gabinete FROM localizacoes WHERE apagado = 0");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $linhas[] = [$r['edificio'], $r['piso'], $r['servico'], $r['sala_internamento_gabinete']];
            }
            break;

        case 'acessorios':
            $idEquipamentoEncrypted = $_GET['id_equipamento'] ?? null;
            $idEquipamento = aes_decrypt($idEquipamentoEncrypted);
            if (!$idEquipamento || !is_numeric($idEquipamento)) {
                die('Equipamento inválido.');
            }
            $titulo = 'Acessórios';
            $colunas = ['Código', 'Nome', 'Marca', 'Fabricante', 'Modelo', 'Nº Série', 'Estado', 'Observações'];
            $stmt = $ligacao->prepare("SELECT codigo, nome, marca, fabricante, modelo, numero_serie, estado, observacoes FROM acessorios WHERE id_equipamento_pai = :id AND apagado = 0");
            $stmt->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $linhas[] = [
                    $r['codigo'], $r['nome'], $r['marca'], $r['fabricante'],
                    $r['modelo'], $r['numero_serie'], $r['estado'], $r['observacoes']
                ];
            }
            break;

        case 'consumiveis':
            $idEquipamentoEncrypted = $_GET['id_equipamento'] ?? null;
            $idEquipamento = aes_decrypt($idEquipamentoEncrypted);
            if (!$idEquipamento || !is_numeric($idEquipamento)) {
                die('Equipamento inválido.');
            }
            $titulo = 'Consumíveis';
            $colunas = ['Código', 'Nome', 'Quantidade', 'Fornecedor', 'Observações'];
            $stmt = $ligacao->prepare("
                SELECT c.codigo, c.nome, c.quantidade, f.nome_empresa, c.observacoes
                FROM consumiveis c
                LEFT JOIN fornecedores f ON c.id_fornecedor = f.id
                WHERE c.id_equipamento_pai = :id AND c.apagado = 0
            ");
            $stmt->bindParam(':id', $idEquipamento, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $linhas[] = [$r['codigo'], $r['nome'], $r['quantidade'], $r['nome_empresa'], $r['observacoes']];
            }
            break;

        default:
            die('Tabela não suportada.');
    }

} catch (PDOException $err) {
    die('Erro ao exportar: falha na ligação à base de dados.');
}

$nomeFicheiro = 'equipflow_' . $tabela . '_' . date('Y-m-d');

// ---------- CSV ----------
if ($formato === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nomeFicheiro . '.csv"');

    $saida = fopen('php://output', 'w');
    fputs($saida, "\xEF\xBB\xBF"); // BOM para acentos abrirem bem no Excel
    fputcsv($saida, $colunas, ';');
    foreach ($linhas as $linha) {
        fputcsv($saida, $linha, ';');
    }
    fclose($saida);
    exit;
}

// ---------- JSON ----------
if ($formato === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nomeFicheiro . '.json"');

    $resultadoJson = [];
    foreach ($linhas as $linha) {
        $resultadoJson[] = array_combine($colunas, $linha);
    }

    echo json_encode($resultadoJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------- PDF ----------
if ($formato === 'pdf') {
    $pdf = new FPDF('L', 'mm', 'A4'); // 'L' = paisagem
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, utf8_decode('EquipFlow - ' . $titulo), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 6, utf8_decode('Exportado em: ' . date('d/m/Y H:i')), 0, 1, 'C');
    $pdf->Ln(4);

    // Larguras personalizadas por tabela (em mm, soma = 277 disponíveis em paisagem A4)
    $larguras = [
        'equipamentos'  => [25, 60, 30, 30, 25, 30, 77],
        'fornecedores'  => [55, 45, 30, 55, 50, 42],
        'localizacoes'  => [55, 30, 55, 75],
        'acessorios'    => [30, 50, 30, 30, 30, 35, 25, 47],
        'consumiveis'   => [30, 65, 30, 60, 92],
    ];
    $larguraColuna = $larguras[$tabela] ?? array_fill(0, count($colunas), 277 / count($colunas));

    $alturaLinha = 6;

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(0, 150, 166);
    $pdf->SetTextColor(255, 255, 255);
    foreach ($colunas as $i => $col) {
        $pdf->Cell($larguraColuna[$i], 8, utf8_decode($col), 1, 0, 'C', true);
    }
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(245, 245, 245);
    $preenche = false;

    foreach ($linhas as $linha) {
        // Calcula quantas linhas de texto cada célula vai precisar, para alinhar a altura da linha toda
        $maxLinhasTexto = 1;
        foreach ($linha as $i => $valor) {
            $nLinhas = ceil($pdf->GetStringWidth(utf8_decode((string) $valor)) / ($larguraColuna[$i] - 2));
            $maxLinhasTexto = max($maxLinhasTexto, $nLinhas);
        }
        $alturaTotal = $alturaLinha * max(1, $maxLinhasTexto);

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        foreach ($linha as $i => $valor) {
            $pdf->SetXY($x, $y);
            $pdf->MultiCell($larguraColuna[$i], $alturaLinha, utf8_decode((string) $valor), 1, 'L', $preenche);
            $x += $larguraColuna[$i];
        }

        $pdf->SetXY(10, $y + $alturaTotal);
        $preenche = !$preenche;

        // Quebra de página se necessário
        if ($pdf->GetY() > 190) {
            $pdf->AddPage();
        }
    }

    $pdf->Output('D', $nomeFicheiro . '.pdf');
    exit;
}