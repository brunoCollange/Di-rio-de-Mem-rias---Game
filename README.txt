╔══════════════════════════════════════════════════════════════╗
║              DIÁRIO DE MEMÓRIAS — README                     ║
║              Developed by Bruno Collange                     ║
║              ©Colliveir Development                          ║
╚══════════════════════════════════════════════════════════════╝

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  SOBRE O PROJETO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  Diário de Memórias é um jogo 2D de mundo aberto onde você
  controla um personagem que caminha por um mapa com biomas
  variados e visita "locais de memória" — pontos no mundo onde
  você pode guardar fotos, textos e músicas como um diário interativo.

  Tudo é salvo em banco de dados MySQL. Qualquer máquina que 
  apontar para o mesmo servidor verá o mesmo mundo, os mesmos 
  locais e as mesmas memórias.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  ESTRUTURA DE ARQUIVOS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  diario/
  ├── index.html          → frontend do jogo (canvas + UI)
  ├── style.css           → estilos de todos os modais e UI
  ├── README.txt          → este arquivo
  │
  ├── assets/
  │   └── musica.mp3      → música de fundo principal
  │
  ├── api/
  │   ├── config.php      → configurações do banco de dados
  │   ├── index.php       → API REST (todos os endpoints do jogo)
  │   └── auth.php        → autenticação (login/logout/check)
  │
  ├── uploads/
  │   ├── *.jpg/png/webp  → fotos das memórias
  │   ├── sticker_*.png   → adesivos do mapa
  │   └── spot_*_music.*  → músicas dos locais
  │
  └── diario.sql          → schema completo do banco de dados

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  INSTALAÇÃO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  1. BANCO DE DADOS
     Importe o schema no MySQL:
       mysql -u root -p < diario.sql
     Ou cole o conteúdo de diario.sql no phpMyAdmin.

  2. CONFIGURAÇÃO
     Edite api/config.php com seus dados:
       define('DB_HOST', 'localhost');
       define('DB_NAME', 'diario');
       define('DB_USER', 'seu_usuario');
       define('DB_PASS', 'sua_senha');

  3. ACESSO
     Abra o projeto pelo servidor web (não como arquivo local,
     pois a API PHP precisa estar rodando):
       http://localhost/diario/

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  LOGIN PADRÃO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  Usuário : admin
  Senha   : admin

  IMPORTANTE: troque a senha após a instalação.
  Para gerar um novo hash, crie um arquivo temporário no servidor:

    <?php echo password_hash('sua_nova_senha', PASSWORD_DEFAULT);

  Acesse pelo browser, copie o hash e atualize no banco:

    UPDATE users SET password = 'HASH_AQUI' WHERE username = 'admin';

  Depois delete o arquivo temporário.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  CONTROLES DO JOGO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  WASD / Setas      → mover o personagem
  Shift + direção   → correr
  F                 → abrir local próximo
  Espaço            → criar novo local na posição atual
  X                 → colocar adesivo / remover adesivo próximo
  " (aspas)         → criar local secreto protegido por senha
  Esc               → abrir / fechar painel

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  FUNCIONALIDADES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  MUNDO
  • Mapa 3200×2000px com câmera que segue o personagem
  • 5 biomas: Planície, Floresta, Neve, Nether e Pântano
  • Bordas orgânicas entre biomas (algoritmo de Voronoi + noise)
  • Minimapa no canto superior direito com biomas em cores reais
  • Emojis decorativos por bioma (flores, árvores, fogo, etc.)

  PERSONAGEM
  • Sprite pixel art com animação de caminhada e corrida
  • Braços animados em 4 direções
  • Cor personalizável pelo painel (salva no navegador)

  LOCAIS DE MEMÓRIA
  • Criados com Espaço na posição do personagem
  • Halo dourado pulsante e anel tracejado animado
  • Ícone ou foto circular exibido no mapa
  • Nome visível abaixo do local
  • Indicador [ F ] ao se aproximar

  DENTRO DE UM LOCAL
  • Múltiplas memórias por local (foto + título + descrição)
  • Layout alternado (foto à esquerda / direita)
  • Upload de foto com recorte interativo antes de enviar
  • Trocar ou remover foto individualmente
  • Edição inline de título e descrição
  • Excluir memórias individualmente
  • Músicas por local com fade automático com a música principal
  • Controle de mute e volume da música do local
  • Renomear o local clicando no lápis
  • Excluir o local inteiro (remove fotos e música do servidor)

  LOCAIS SECRETOS
  • Criados com a tecla " (aspas duplas)
  • Protegidos por senha (hash bcrypt no banco)
  • Visual diferente no mapa: cadeado dourado com anel giratório
  • Não aparecem no minimapa
  • Sem nome visível no mapa
  • Acesso exige digitação de senha a cada visita

  ADESIVOS
  • Colocados no mapa com a tecla X
  • Upload de qualquer imagem (PNG com transparência funciona)
  • Ficam fixados nas coordenadas do mundo
  • Aumentam de tamanho ao passar por cima
  • Removidos pressionando X quando próximo

  PAINEL (Esc)
  • Referência de controles
  • Slider de volume da música principal
  • Slider de volume da música dos locais
  • Seletor de cor do personagem
  • Botão reiniciar posição (volta ao centro do mapa)
  • Botão logout

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  API — ENDPOINTS (api/index.php)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  GET  ?action=spots               → lista spots + memórias
  POST ?action=spot_create         → cria local (normal ou secreto)
  POST ?action=spot_update         → renomeia / move local
  POST ?action=spot_delete         → exclui local, memórias e arquivos
  POST ?action=spot_unlock         → verifica senha de local secreto
  POST ?action=spot_music_upload   → upload de música do local
  POST ?action=spot_music_remove   → remove música do local

  POST ?action=memory_create       → adiciona memória a um local
  POST ?action=memory_update       → edita título/descrição
  POST ?action=memory_delete       → exclui memória e imagem
  POST ?action=memory_upload       → upload de foto da memória
  POST ?action=memory_remove_img   → remove foto de uma memória

  GET  ?action=stickers            → lista adesivos
  POST ?action=sticker_upload      → adiciona adesivo no mapa
  POST ?action=sticker_delete      → remove adesivo e arquivo

  GET  ?action=debug               → diagnóstico do servidor

  API de autenticação (api/auth.php):
  POST ?action=login               → inicia sessão
  GET  ?action=logout              → encerra sessão
  GET  ?action=check               → verifica se está logado

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  BANCO DE DADOS — TABELAS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  spots
    id, name, world_x, world_y, created_at,
    music_path, music_name,
    is_secret, secret_password

  memories
    id, spot_id, position, title, body,
    icon, reversed, image_path, created_at

  stickers
    id, world_x, world_y, image_path, created_at

  users
    id, username, password (bcrypt)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  ARMAZENAMENTO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  • Textos, posições e metadados → MySQL
  • Imagens e músicas → pasta uploads/ no servidor
  • Caminhos dos arquivos salvos no banco
  • Tamanho máximo por arquivo: 50MB (configurável em config.php)
  • Ao excluir um local ou memória, os arquivos são removidos
    automaticamente do servidor

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  MULTIPLAYER / REDE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  Como tudo é salvo no MySQL e os arquivos ficam no servidor,
  qualquer máquina que acessar o mesmo endereço verá o mesmo
  mundo em tempo real — os mesmos locais, memórias, adesivos
  e músicas.

  Para acesso externo (internet):
  1. Hospede em um servidor com domínio
  2. Ajuste ALLOWED_ORIGIN em config.php para seu domínio
  3. Compartilhe a URL com quem deve ter acesso

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  CONFIGURAÇÕES AVANÇADAS (config.php)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  MAX_UPLOAD_MB   → tamanho máximo de upload (padrão: 50MB)
  ALLOWED_ORIGIN  → domínio permitido no CORS (padrão: *)
  UPLOAD_DIR      → caminho físico da pasta de uploads
  UPLOAD_URL      → detectada automaticamente pelo servidor

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  AJUSTES RÁPIDOS NO CÓDIGO (index.html)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  Velocidade do personagem:
    const WALK = 5, RUN = 10;

  Escala geral do jogo (tamanho de tudo no canvas):
    const S = 2;   ← aumente para maior, diminua para menor

  Tamanho do mundo:
    const WORLD_W = 3200, WORLD_H = 2000;

  Posição inicial do personagem:
    const player = { x: 1600, y: 1000, ... };

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
