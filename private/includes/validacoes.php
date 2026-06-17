<?php

// ============================================================
// Equipamentos
// ============================================================

// Validação do Código Interno
function validar_codigo(string $codigo): array {
    $erros = [];
    if (empty(trim($codigo))) {
        $erros[] = "O campo Código Interno é obrigatório.";
    } elseif (!preg_match('/^\d+\.\d{3}\.\d{2}$/', $codigo)) {
        $erros[] = "O campo Código Interno é inválido. Use o formato XX.XXX.XX (ex: 04.002.01).";
    }
    return $erros;
}

// Validação da Designação
function validar_designacao(string $designacao): array {
    $erros = [];
    if (empty(trim($designacao))) {
        $erros[] = "O campo Designação é obrigatório.";
    } elseif (preg_match('/^\d+$/', $designacao)) {
        $erros[] = "O campo Designação não pode conter apenas números.";
    }
    return $erros;
}

// Validação da Categoria
function validar_categoria(string $categoria): array {
    $erros = [];
    if (empty($categoria) || $categoria == "Escolha uma opção") {
        $erros[] = "O campo Categoria é obrigatório.";
    }
    return $erros;
}

// Validação da Marca
function validar_marca(string $marca): array {
    $erros = [];
    if (empty(trim($marca))) {
        $erros[] = "O campo Marca é obrigatório.";
    }
    return $erros;
}

// Validação do Modelo
function validar_modelo(string $modelo): array {
    $erros = [];
    if (empty(trim($modelo))) {
        $erros[] = "O campo Modelo é obrigatório.";
    }
    return $erros;
}

// Validação do Número de Série
function validar_nserie(string $nserie): array {
    $erros = [];
    if (empty(trim($nserie))) {
        $erros[] = "O campo Número de Série é obrigatório.";
    }
    return $erros;
}

// Validação do Fabricante
function validar_fabricante(string $fabricante): array {
    $erros = [];
    if (empty(trim($fabricante))) {
        $erros[] = "O campo Fabricante é obrigatório.";
    } elseif (preg_match('/\d/', $fabricante)) {
        $erros[] = "O campo Fabricante não pode conter números.";
    }
    return $erros;
}

// Validação da Data de Aquisição
function validar_data_aquisicao(string $dataquisicao, string $anofabrico = ''): array {
    $erros = [];
    if (empty(trim($dataquisicao))) {
        $erros[] = "O campo Data de Aquisição é obrigatório.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataquisicao)) {
        $erros[] = "Formato de data inválido. Use AAAA-MM-DD.";
    } else {
        $partes = explode('-', $dataquisicao);
        if (!checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0])) {
            $erros[] = "Data de aquisição inválida.";
        } elseif ($dataquisicao > date('Y-m-d')) {
            $erros[] = "A data de aquisição não pode ser posterior à data atual.";
        } elseif (!empty($anofabrico) && (int)date('Y', strtotime($dataquisicao)) < (int)$anofabrico) {
            $erros[] = "A data de aquisição não pode ser anterior ao ano de fabrico.";
        }
    }
    return $erros;
}

// Validação do Ano de Fabrico
function validar_ano_fabrico(string $anofabrico): array {
    $erros = [];
    if (empty(trim($anofabrico))) {
        $erros[] = "O campo Ano de Fabrico é obrigatório.";
    } elseif (!preg_match('/^\d{4}$/', $anofabrico)) {
        $erros[] = "Ano de fabrico inválido. Use o formato AAAA.";
    } elseif ((int)$anofabrico > (int)date('Y')) {
        $erros[] = "O ano de fabrico não pode ser posterior ao ano atual.";
    }
    return $erros;
}

// Validação do Custo de Aquisição (opcional)
function validar_custo(string $custo): array {
    $erros = [];
    if (!empty($custo) && !is_numeric($custo)) {
        $erros[] = "O custo de aquisição deve ser um valor numérico.";
    } elseif (!empty($custo) && (float)$custo <= 0) {
        $erros[] = "O custo de aquisição deve ser maior que 0.";
    }
    return $erros;
}

// Validação do Tipo de Entrada
function validar_tipo_entrada(string $tipoentrada): array {
    $erros = [];
    if (empty($tipoentrada) || $tipoentrada == "Escolha uma opção") {
        $erros[] = "O campo Tipo de Entrada é obrigatório.";
    }
    return $erros;
}

// Validação do Estado
function validar_estado(string $estado): array {
    $erros = [];
    if (empty($estado) || $estado == "Escolha uma opção") {
        $erros[] = "O campo Estado é obrigatório.";
    }
    return $erros;
}

// Validação da Criticidade
function validar_criticidade(string $criticidade): array {
    $erros = [];
    if (empty($criticidade) || $criticidade == "Escolha uma opção") {
        $erros[] = "O campo Criticidade é obrigatório.";
    }
    return $erros;
}

// Validação da Localização
function validar_localizacao(string $localizacao): array {
    $erros = [];
    if (empty($localizacao) || $localizacao == "Escolha uma opção") {
        $erros[] = "O campo Localização é obrigatório.";
    }
    return $erros;
}

// Validação das Observações (opcional)
function validar_observacoes(string $observacoes): array {
    $erros = [];
    if (!empty($observacoes) && strlen($observacoes) > 500) {
        $erros[] = "As observações não podem exceder 500 caracteres.";
    }
    return $erros;
}


// ============================================================
// Fornecedores
// ============================================================

// Validação do Nome da Empresa
function validar_nome_fornecedor(string $nome): array {
    $erros = [];
    if (empty(trim($nome))) {
        $erros[] = "O campo Nome da Empresa é obrigatório.";
    }
    return $erros;
}

// Validação do NIF
function validar_nif(string $nif): array {
    $erros = [];
    if (empty(trim($nif))) {
        $erros[] = "O campo NIF é obrigatório.";
    } elseif (!preg_match('/^\d{9}$/', $nif)) {
        $erros[] = "O NIF é inválido. Deve ter exactamente 9 dígitos.";
    }
    return $erros;
}

// Validação do Tipo de Fornecedor
function validar_tipo_fornecedor(string $tipo): array {
    $erros = [];
    if (empty($tipo) || $tipo == "Escolha uma opção") {
        $erros[] = "O campo Tipo de Fornecedor é obrigatório.";
    }
    return $erros;
}

// Validação da Morada
function validar_morada_fornecedor(string $morada): array {
    $erros = [];
    if (empty(trim($morada))) {
        $erros[] = "O campo Morada é obrigatório.";
    }
    return $erros;
}

// Validação do Telefone
function validar_telefone(string $telefone): array {
    $erros = [];
    if (empty(trim($telefone))) {
        $erros[] = "O campo Telefone é obrigatório.";
    } elseif (!preg_match('/^[29][0-9]{8}$/', $telefone)) {
        $erros[] = "O Telefone é inválido. Deve começar por 2 ou 9 e ter 9 dígitos.";
    }
    return $erros;
}

// Validação do Email
function validar_email(string $email): array {
    $erros = [];
    if (empty(trim($email))) {
        $erros[] = "O campo Email é obrigatório.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O endereço de email não é válido.";
    }
    return $erros;
}

// Validação do Website (opcional)
function validar_website(string $website): array {
    $erros = [];
    if (!empty($website)) {
        $url = preg_match('/^https?:\/\//', $website) ? $website : 'https://' . $website;
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $erros[] = "O website não é válido. Use o formato www.exemplo.com.";
        }
    }
    return $erros;
}

// Validação da Pessoa de Contacto
function validar_pessoa_contacto(string $pessoa_contacto): array {
    $erros = [];
    if (empty(trim($pessoa_contacto))) {
        $erros[] = "O campo Pessoa de Contacto é obrigatório.";
    } elseif (preg_match('/\d/', $pessoa_contacto)) {
        $erros[] = "O campo Pessoa de Contacto não pode conter números.";
    }
    return $erros;
}

// Validação do Telefone da Pessoa de Contacto
function validar_telefone_contacto(string $telefone_contacto): array {
    $erros = [];
    if (empty(trim($telefone_contacto))) {
        $erros[] = "O campo Telefone da Pessoa de Contacto é obrigatório.";
    } elseif (!preg_match('/^[29][0-9]{8}$/', $telefone_contacto)) {
        $erros[] = "O Telefone da Pessoa de Contacto é inválido. Deve começar por 2 ou 9 e ter 9 dígitos.";
    }
    return $erros;
}

// Validação das Observações do Fornecedor (opcional)
function validar_observacoes_fornecedor(string $observacoes): array {
    $erros = [];
    if (!empty($observacoes) && strlen($observacoes) > 500) {
        $erros[] = "As observações não podem exceder 500 caracteres.";
    }
    return $erros;
}


// ============================================================
// Localizações
// ============================================================

// Validação do Edifício
function validar_edificio(string $edificio): array {
    $erros = [];
    if (empty($edificio) || $edificio == "Escolha uma opção") {
        $erros[] = "O campo Edifício é obrigatório.";
    }
    return $erros;
}

// Validação do Piso
function validar_piso(string $piso): array {
    $erros = [];
    if (empty($piso) || $piso == "Escolha uma opção") {
        $erros[] = "O campo Piso é obrigatório.";
    }
    return $erros;
}

// Validação do Serviço
function validar_servico(string $servico): array {
    $erros = [];
    if (empty(trim($servico))) {
        $erros[] = "O campo Serviço é obrigatório.";
    } elseif (preg_match('/^\d+$/', $servico)) {
        $erros[] = "O campo Serviço não pode conter apenas números.";
    }
    return $erros;
}

// Validação da Sala / Gabinete
function validar_sala(string $sala): array {
    $erros = [];
    if (empty(trim($sala))) {
        $erros[] = "O campo Sala / Gabinete é obrigatório.";
    } elseif (strlen($sala) > 100) {
        $erros[] = "O campo Sala não pode exceder 100 caracteres.";
    }
    return $erros;
}