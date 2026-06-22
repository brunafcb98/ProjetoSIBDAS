<?php

// ============================================================
// Equipamentos (e acess - designaçao/nome, nrserie, estado, obs + cons - desig e obs)
// ============================================================

// Validação da Designação
function validar_designacao(string $designacao): array {
    $erros = [];
    if (empty(trim($designacao))) {
        $erros[] = "O campo Designação/Nome é obrigatório.";
    } elseif (preg_match('/^\d+$/', $designacao)) {
        $erros[] = "O campo Designação/Nome não pode conter apenas números.";
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
// Número de Série deve ser único entre os equipamentos ativos.
// $idAtual serve para ignorar o próprio registo quando se está a editar.
function validar_nserie_unico(string $nserie, $ligacao, $idAtual = null): array {
    $erros = [];
    $nserie = trim($nserie);

    if (empty($nserie)) {
        return $erros;
    }

    if ($idAtual !== null) {
        $stmt = $ligacao->prepare("SELECT id FROM equipamentos WHERE numero_serie = :nserie AND apagado = 0 AND id != :id_atual");
        $stmt->bindParam(':nserie', $nserie, PDO::PARAM_STR);
        $stmt->bindParam(':id_atual', $idAtual, PDO::PARAM_INT);
    } else {
        $stmt = $ligacao->prepare("SELECT id FROM equipamentos WHERE numero_serie = :nserie AND apagado = 0");
        $stmt->bindParam(':nserie', $nserie, PDO::PARAM_STR);
    }

    $stmt->execute();

    if ($stmt->fetchColumn()) {
        $erros[] = "Já existe um equipamento com este Número de Série.";
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

//Fornecedor - associado a equipamentos
// Validação: pelo menos um Fornecedor Associado (Fabricante, Distribuidor ou Assistência) deve ser preenchido
function validar_fornecedores_associados(string $fabricante, string $distribuidor, string $assistencia): array {
    $erros = [];
    if (empty($fabricante) && empty($distribuidor) && empty($assistencia)) {
        $erros[] = "Deve selecionar pelo menos um Fornecedor Associado (Fabricante, Distribuidor ou Assistência Técnica).";
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
    } elseif (preg_match('/^\d+$/', $nome)) {
        $erros[] = "O campo Nome da Empresa não pode conter apenas números.";
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
    } elseif (preg_match('/^\d+$/', $morada)) {
        $erros[] = "O campo Morada não pode conter apenas números.";
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
    } elseif (preg_match('/^\d+$/', $sala)) {
        $erros[] = "O campo Internamento/Sala/Gabinete não pode conter apenas números.";
    } elseif (strlen($sala) > 35) {
        $erros[] = "O campo Sala não pode exceder 35 caracteres.";
    }
    return $erros;
}

// ============================================================
// Documentos
// ============================================================

// Validação do Tipo de Documento
function validar_tipo_documento(string $tipo_documento): array {
    $erros = [];
    if (empty($tipo_documento) || $tipo_documento == "Escolha uma opção") {
        $erros[] = "O campo Tipo de Documento é obrigatório.";
    }
    return $erros;
}

// Validação do Nome do Documento
function validar_nome_documento(string $nome_documento): array {
    $erros = [];
    if (empty(trim($nome_documento))) {
        $erros[] = "O campo Nome do Documento é obrigatório.";
    }
    return $erros;
}

// Validação da Data do Documento
function validar_data_documento(string $data_documento): array {
    $erros = [];
    if (empty(trim($data_documento))) {
        $erros[] = "O campo Data do Documento é obrigatório.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_documento)) {
        $erros[] = "Formato de data inválido. Use AAAA-MM-DD.";
    } else {
        $partes = explode('-', $data_documento);
        if (!checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0])) {
            $erros[] = "Data do documento inválida.";
        } elseif ($data_documento > date('Y-m-d')) {
            $erros[] = "A data do documento não pode ser posterior à data atual.";
        }
    }
    return $erros;
}

// Validação da Data de Validade (opcional)
function validar_data_validade(string $data_validade, string $data_documento = ''): array {
    $erros = [];
    if (!empty(trim($data_validade))) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_validade)) {
            $erros[] = "Formato de data de validade inválido. Use AAAA-MM-DD.";
        } else {
            $partes = explode('-', $data_validade);
            if (!checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0])) {
                $erros[] = "Data de validade inválida.";
            } elseif (!empty($data_documento) && $data_validade < $data_documento) {
                $erros[] = "A data de validade não pode ser anterior à data do documento.";
            }
        }
    }
    return $erros;
}

// Validação do Ficheiro carregado (upload)
function validar_ficheiro_upload(array $ficheiro, bool $obrigatorio = false): array {
    $erros = [];
    $extensoesPermitidas = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    if (empty($ficheiro['name'])) {
        if ($obrigatorio) {
            $erros[] = "É obrigatório selecionar um ficheiro.";
        }
        return $erros;
    }

    $extensao = strtolower(pathinfo($ficheiro['name'], PATHINFO_EXTENSION));

    if (!in_array($extensao, $extensoesPermitidas)) {
        $erros[] = "O tipo de ficheiro não é permitido. Use PDF, JPG, PNG, DOC ou DOCX.";
    } elseif ($ficheiro['size'] > 10 * 1024 * 1024) {
        $erros[] = "O ficheiro não pode exceder 10MB.";
    }

    return $erros;
}

// ============================================================
// Garantias e Contratos
// ============================================================

// Validação das Datas de Garantia (devem vir ambas ou nenhuma)
function validar_datas_garantia(string $dataInicio, string $dataFim): array {
    $erros = [];
    $dataInicio = trim($dataInicio);
    $dataFim = trim($dataFim);

    if (empty($dataInicio) && empty($dataFim)) {
        return $erros; // nenhuma preenchida, ok
    }

    if (empty($dataInicio) || empty($dataFim)) {
        $erros[] = "Se preencher uma data de garantia, deve preencher também a outra.";
        return $erros;
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataInicio)) {
        $erros[] = "Formato da Data de Início da Garantia inválido. Use AAAA-MM-DD.";
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFim)) {
        $erros[] = "Formato da Data de Fim da Garantia inválido. Use AAAA-MM-DD.";
    }

    if (empty($erros)) {
        $partesInicio = explode('-', $dataInicio);
        $partesFim = explode('-', $dataFim);

        if (!checkdate((int)$partesInicio[1], (int)$partesInicio[2], (int)$partesInicio[0])) {
            $erros[] = "Data de Início da Garantia inválida.";
        }
        if (!checkdate((int)$partesFim[1], (int)$partesFim[2], (int)$partesFim[0])) {
            $erros[] = "Data de Fim da Garantia inválida.";
        }
        if (empty($erros) && $dataFim < $dataInicio) {
            $erros[] = "A Data de Fim da Garantia não pode ser anterior à Data de Início.";
        }
        if (empty($erros)) {
            $inicio = new DateTime($dataInicio);
            $fim = new DateTime($dataFim);
            $diferenca = $inicio->diff($fim);
            $mesesTotais = ($diferenca->y * 12) + $diferenca->m;

            if ($mesesTotais < 6) {
                $erros[] = "A garantia deve ter uma duração mínima de 6 meses.";
            }
        }
    }

    return $erros;
}

// Validação: a Data de Início da Garantia não pode ser anterior à Data de Aquisição do equipamento
function validar_data_inicio_vs_aquisicao(string $dataInicioGarantia, string $dataAquisicao): array {
    $erros = [];
    $dataInicioGarantia = trim($dataInicioGarantia);

    if (!empty($dataInicioGarantia) && !empty($dataAquisicao) && $dataInicioGarantia < $dataAquisicao) {
        $erros[] = "A Data de Início da Garantia não pode ser anterior à Data de Aquisição do equipamento.";
    }

    return $erros;
}

// Validação do Tipo de Contrato (obrigatório se tem_contrato_manutencao = true; não deve ser preenchido se for false)
function validar_tipo_contrato(string $tipoContrato, bool $temContrato): array {
    $erros = [];
    if ($temContrato && (empty($tipoContrato) || $tipoContrato == "Escolha uma opção")) {
        $erros[] = "O campo Tipo de Contrato é obrigatório quando existe contrato de manutenção.";
    } elseif (!$temContrato && !empty($tipoContrato) && $tipoContrato != "Escolha uma opção") {
        $erros[] = "Não pode preencher o Tipo de Contrato sem marcar 'Existe contrato de manutenção'.";
    }
    return $erros;
}

// Validação da Periodicidade (obrigatória se tem_contrato_manutencao = true; não deve ser preenchida se for false)
function validar_periodicidade(string $periodicidade, bool $temContrato): array {
    $erros = [];
    if ($temContrato && (empty($periodicidade) || $periodicidade == "Escolha uma opção")) {
        $erros[] = "O campo Periodicidade é obrigatório quando existe contrato de manutenção.";
    } elseif (!$temContrato && !empty($periodicidade) && $periodicidade != "Escolha uma opção") {
        $erros[] = "Não pode preencher a Periodicidade sem marcar 'Existe contrato de manutenção'.";
    }
    return $erros;
}

// Validação das Observações da Garantia (opcional)
function validar_observacoes_garantia(string $observacoes): array {
    $erros = [];
    if (!empty($observacoes) && strlen($observacoes) > 500) {
        $erros[] = "As observações não podem exceder 500 caracteres.";
    }
    return $erros;
}

// Validação: Entidade Responsável é obrigatória se houver garantia ou contrato de manutenção;
// e só pode ser preenchida nesses casos
function validar_entidade_responsavel(string $entidadeResponsavel, string $dataInicio, string $dataFim, bool $temContrato): array {
    $erros = [];
    $temGarantia = !empty(trim($dataInicio)) && !empty(trim($dataFim));
    $temEntidade = !empty(trim($entidadeResponsavel));

    if ($temEntidade && !$temGarantia && !$temContrato) {
        $erros[] = "Só pode indicar uma Entidade Responsável se existir garantia ou contrato de manutenção.";
    }

    if (!$temEntidade && ($temGarantia || $temContrato)) {
        $erros[] = "A Entidade Responsável é obrigatória quando existe garantia ou contrato de manutenção.";
    }

    return $erros;
}

// Validação: deve existir garantia (datas) ou contrato de manutenção associado — não pode ser um registo "vazio".
function validar_contexto_garantia(string $dataInicio, string $dataFim, bool $temContrato): array {
    $erros = [];
    $temGarantia = !empty(trim($dataInicio)) && !empty(trim($dataFim));

    if (!$temGarantia && !$temContrato) {
        $erros[] = "Deve preencher as Datas de Garantia ou marcar 'Existe contrato de manutenção'.";
    }

    return $erros;
}

// ============================================================
// Consumíveis
// ============================================================

// Validação da Quantidade
function validar_quantidade(string $quantidade): array {
    $erros = [];
    if (empty(trim($quantidade))) {
        $erros[] = "O campo Quantidade é obrigatório.";
    } elseif (!preg_match('/^-?\d+$/', $quantidade)) {
        // Aceita o regex (incluindo negativos) só para conseguir distinguir "não é número" de "é negativo"
        $erros[] = "A Quantidade deve ser um número inteiro.";
    } elseif ((int)$quantidade <= 0) {
        $erros[] = "A Quantidade deve ser maior que 0.";
    }
    return $erros;
}

// Validação do Fornecedor (obrigatório para consumíveis)
function validar_fornecedor_consumivel(string $idFornecedor): array {
    $erros = [];
    if (empty($idFornecedor) || !is_numeric($idFornecedor)) {
        $erros[] = "O campo Fornecedor é obrigatório.";
    }
    return $erros;
}
