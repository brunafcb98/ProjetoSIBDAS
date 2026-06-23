NOME DO PROJETO: EquipFlow
NOME DO ESTUDANTE: Bruna Barbosa
NÚMERO DO ESTUDANTE: 1241677


INSTRUÇÕES PARA INSTALAÇÃO E EXECUÇÃO DA APLICAÇÃO:
1. Copiar a pasta do projeto para o diretório correspondente no servidor local (ex: C:\laragon\www\sibdas\1241677\equipflow).
2. A base de dados está alojada no servidor remoto da instituição (vsgate-s1.dei.isep.ipp.pt:10464, base de dados db1241677); a ligação é feita automaticamente através das credenciais definidas em config/config.php, não sendo necessário importar a BD localmente.
3. Confirmar que a constante BASE_URL em config/config.php corresponde a /sibdas/1241677/equipflow.
4. Aceder ao projeto através do browser no seguinte URL: http://127.0.0.1/sibdas/1241677/equipflow/public/index.php


INSTRUÇÕES PARA REALIZAÇÃO DOS PRINCIPAIS TESTES DA APLICAÇÃO:
1. Aceder à página inicial pública (Home, Sobre Nós, Serviços, Vantagens, Funcionalidades, Contacto).
2. Autenticar-se na área privada com um dos perfis indicados em "Credenciais de Acesso".
3. Testar a inserção, edição e desativação (soft delete) de Equipamentos, Fornecedores, Localizações, Documentos, Garantias/Contratos, Acessórios e Consumíveis.
4. Testar o botão "Ver Desativados" nas listagens, para confirmar a visualização de registos inativos.
5. Aceder ao Dashboard e validar os indicadores apresentados.
6. Testar a exportação de dados (CSV, JSON e PDF) nas respetivas listagens.
7. Consultar a página de Logs (apenas visível para o perfil Administrador), para confirmar o registo de eventos.


CREDENCIAIS DE ACESSO:

Perfil: Administrador
Utilizador: admin@equipflow.pt
Password: password

Perfil: Técnico
Utilizador: tecnico1@equipflow.pt
Password: password


INFORMAÇÃO ADICIONAL RELEVANTE PARA AVALIAÇÃO:
- Os identificadores (IDs) de equipamentos, fornecedores e localizações são encriptados sempre que circulam em URLs, evitando a exposição direta de IDs sequenciais da base de dados.
- As passwords são armazenadas em texto simples na base de dados — decisão assumida para este projeto. Como melhoria futura, seria recomendável implementar hashing de password (ex: bcrypt, através das funções password_hash()/password_verify() do PHP).
- A auditoria de eventos (login, criação e desativação de registos) é assegurada através de uma tabela de logs própria.
