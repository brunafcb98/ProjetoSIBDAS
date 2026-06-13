<?php  
// -------------------------------------------------------------------- 
// SEGURANÇA: Proteção de acesso à página. 
// Este ficheiro deve ser acedido apenas por utilizadores autenticados. 
// Caso não exista sessão iniciada, o utilizador será redirecionado para o login.
require_once __DIR__ . '/../../includes/funcoes.php'; 
redirect_if_not_logged(); // Inicia a sessão (se necessário) e verifica se o utilizador está autenticado 
?> 

<?php include '../../includes/header.php'; ?> 
<?php include '../../includes/nav.php'; ?> 

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <main class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-center mt-4">
                <div class="card w-100 shadow rounded" style="max-width: 1200px;">
                    <div class="card-body">
                        <h2 class="mb-4"><strong><i class="fa-solid fa-stethoscope me-2"></i> Inserir novo equipamento</strong></h2>
                        <hr>
                        <form action="#" method="post" novalidate>

                            <!-- Identificação do equipamento -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_codigo" class="form-label">Código Interno de Inventário</label>
                                    <input type="text" class="form-control" name="codigo_equipamento" id="texto_codigo" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_designacao" class="form-label">Designação do Equipamento</label>
                                    <input type="text" class="form-control" name="designacao_equipamento" id="texto_designacao" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="select_categoria" class="form-label">Categoria</label>
                                    <select class="form-select" name="categoria_equipamento" id="select_categoria">
                                        <option selected>Escolha uma opção</option>
                                        <option value="monitorizacao">Monitorização</option>
                                        <option value="suporte_vida">Suporte de Vida</option>
                                        <option value="terapia">Terapia</option>
                                        <option value="diagnostico">Diagnóstico</option>
                                        <option value="laboratorio">Laboratório</option>
                                        <option value="esterilizacao">Esterilização</option>
                                        <option value="reabilitacao">Reabilitação</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_marca" class="form-label">Marca</label>
                                    <input type="text" class="form-control" name="marca_equipamento" id="texto_marca" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_modelo" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" name="modelo_equipamento" id="texto_modelo" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_nserie" class="form-label">Número de Série</label>
                                    <input type="text" class="form-control" name="nserie_equipamento" id="texto_nserie" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="texto_fabricante" class="form-label">Fabricante</label>
                                    <input type="text" class="form-control" name="fabricante_equipamento" id="texto_fabricante" list="fabricantes">
                                    <datalist id="fabricantes">
                                        <option value="Philips">
                                        <option value="B. Braun">
                                        <option value="Siemens">
                                        <option value="Dräger">
                                        <option value="Medtronic">
                                        <option value="Baxter">
                                    </datalist>
                                </div>
                                <div class="col-md-6">
                                    <label for="texto_dataquisicao" class="form-label">Data de Aquisição</label>
                                    <input type="text" class="form-control" name="dataquisicao_equipamento" id="texto_dataquisicao" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="texto_anofabrico" class="form-label">Ano de Fabrico</label>
                                    <input type="text" class="form-control" name="anofabrico_equipamento" id="texto_anofabrico">
                                </div>
                                <div class="col-md-4">
                                    <label for="texto_custo" class="form-label">Custo de Aquisição <small>(€)</small></label>
                                    <input type="text" class="form-control" name="custo_equipamento" id="texto_custo">
                                </div>
                                <div class="col-md-4">
                                    <label for="select_tipoentrada" class="form-label">Tipo de Entrada</label>
                                    <select class="form-select" name="tipoentrada_equipamento" id="select_tipoentrada">
                                        <option selected>Escolha uma opção</option>
                                        <option value="compra">Compra</option>
                                        <option value="doacao">Doação</option>
                                        <option value="aluguer">Aluguer</option>
                                        <option value="emprestimo">Empréstimo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="select_estado" class="form-label">Estado</label>
                                    <select class="form-select" name="estado_equipamento" id="select_estado">
                                        <option selected>Escolha uma opção</option>
                                        <option value="ativo">Ativo</option>
                                        <option value="manutencao">Em Manutenção</option>
                                        <option value="inativo">Inativo</option>
                                        <option value="calibracao">Em Calibração</option>
                                        <option value="quarentena">Em Quarentena</option>
                                        <option value="abatido">Abatido</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="select_criticidade" class="form-label">Criticidade</label>
                                    <select class="form-select" name="criticidade_equipamento" id="select_criticidade">
                                        <option selected>Escolha uma opção</option>
                                        <option value="baixa">Baixa</option>
                                        <option value="media">Média</option>
                                        <option value="alta">Alta</option>
                                        <option value="suporte_vida">Suporte de Vida</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="select_localizacao" class="form-label">Localização</label>
                                    <select class="form-select" name="localizacao_equipamento" id="select_localizacao" required>
                                        <option selected>Escolha uma opção</option>
                                        <option value="1">[Localização 1]</option>
                                        <option value="2">[Localização 2]</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="texto_observacoes" class="form-label">Observações</label>
                                    <input type="text" class="form-control" name="observacoes_equipamento" id="texto_observacoes">
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="d-flex justify-content-end gap-2 mb-4">
                                <a href="equipamentos.php" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-xmark me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-regular fa-floppy-disk me-1"></i> Guardar
                                </button>
                            </div>

                            <!-- Mensagem de erro -->
                            <div class="alert alert-danger text-center" role="alert">
                                Erro simples
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </main>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>