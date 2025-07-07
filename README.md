# 🎬 FilmStack

Uma aplicação web moderna para organização e descoberta de filmes, desenvolvida com Laravel e Vue.js, integrada com a API do The Movie Database (TMDB).

## 📋 Sobre o Projeto

**FilmStack** é uma plataforma completa para amantes de cinema que permite:

- 🔍 **Buscar filmes** por título, gênero ou filtros avançados
- 📝 **Criar listas personalizadas** de filmes organizadas por tema
- ❤️ **Marcar filmes como favoritos** e gerenciar lista de desejos
- ✅ **Acompanhar filmes assistidos** com sistema de marcação
- 🌟 **Descobrir novos filmes** através de trending e populares
- 🎭 **Explorar por gêneros** com navegação intuitiva
- 👥 **Compartilhar listas públicas** com outros usuários
- 🔐 **Autenticação segura** com Google OAuth

## 🛠️ Stack Tecnológica

### Backend
- **PHP 8.4+** - Linguagem principal
- **Laravel 12** - Framework web robusto
- **Inertia.js** - Stack moderno para SPAs
- **Laravel Socialite** - Autenticação social (Google)
- **SQLite/MySQL** - Banco de dados
- **Pest** - Framework de testes

### Frontend
- **Vue.js 3** - Framework JavaScript reativo
- **TypeScript** - Tipagem estática
- **Tailwind CSS 4** - Framework CSS utilitário
- **Pinia** - Gerenciamento de estado
- **Vite** - Build tool e dev server
- **FontAwesome** - Ícones
- **GSAP** - Animações

### Ferramentas e Integração
- **Docker & Laravel Sail** - Containerização
- **TMDB API** - Base de dados de filmes
- **ESLint & Prettier** - Qualidade de código
- **Vite** - Hot reload e bundling

## 🎯 API Externa

O projeto utiliza a **The Movie Database (TMDB) API** para:
- Buscar informações detalhadas de filmes
- Obter imagens (posters, backdrops)
- Descobrir filmes trending e populares
- Filtrar por gêneros e categorias
- Acessar metadados completos

## 📋 Requisitos do Sistema

### Para Desenvolvimento Local

#### Opção 1: Docker (Recomendado)
- **Docker** 20.10+
- **Docker Compose** 2.0+
- **Git**

#### Opção 2: Instalação Manual
- **PHP** 8.4+ com extensões:
  - BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- **Composer** 2.0+
- **Node.js** 18+ 
- **npm** 9+
- **MySQL** 8.0+ ou **SQLite** 3.8+

### Para Deploy em Produção
- **Servidor Linux** (Ubuntu 20.04+ recomendado)
- **PHP 8.4+** com PHP-FPM
- **Nginx** ou **Apache**
- **MySQL** 8.0+ ou **PostgreSQL** 13+
- **Redis** (opcional, para cache)
- **SSL Certificate** (recomendado)

## 🚀 Instalação e Configuração

> **⚡ Quer começar rapidamente?** Veja nosso [Guia de Instalação Rápida](./docs/QUICK-START.md)

### 📦 Opção 1: Docker com Laravel Sail (Recomendado)

Laravel Sail é uma interface de linha de comando leve para interagir com o ambiente Docker padrão do Laravel, tornando o desenvolvimento muito mais simples.

#### Windows
```bash
# Instalar WSL2 e Docker Desktop
# 1. Habilitar WSL2: https://docs.microsoft.com/en-us/windows/wsl/install
# 2. Instalar Docker Desktop: https://docs.docker.com/desktop/windows/install/

# Clonar o repositório
git clone https://github.com/RyanSilva-176/film-stack
cd film-stack

# Copiar arquivo de ambiente
cp .env.example .env

# Instalar dependências do Composer via Docker
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# (Opcional) Criar um alias para o Sail
# Adicione esta linha ao final do seu arquivo ~/.bashrc ou ~/.zshrc no WSL2:
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc

# Iniciar containers
./vendor/bin/sail up -d
# Com alias: sail up -d

# Gerar chave da aplicação
./vendor/bin/sail artisan key:generate
# Com alias: sail artisan key:generate

# Executar migrações
./vendor/bin/sail artisan migrate
# Com alias: sail artisan migrate

# Instalar dependências do Node.js
./vendor/bin/sail npm install
# Com alias: sail npm install

# Build dos assets
./vendor/bin/sail npm run build
# Com alias: sail npm run build
```

#### Linux/macOS
```bash
# Clonar o repositório
git clone https://github.com/RyanSilva-176/film-stack
cd film-stack

# Copiar arquivo de ambiente
cp .env.example .env

# Instalar dependências do Composer
composer install

# (Opcional) Criar um alias para o Sail
# Para facilitar o uso do Sail, você pode criar um alias no terminal:
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.bashrc
# ou para zsh:
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.zshrc

# Recarregue o terminal
source ~/.bashrc
# ou
source ~/.zshrc

# Agora você pode usar 'sail' ao invés de './vendor/bin/sail' nos comandos

# Iniciar containers
./vendor/bin/sail up -d
# Com alias: sail up -d

# Gerar chave da aplicação
./vendor/bin/sail artisan key:generate
# Com alias: sail artisan key:generate

# Executar migrações
./vendor/bin/sail artisan migrate
# Com alias: sail artisan migrate

# Instalar dependências do Node.js
./vendor/bin/sail npm install
# Com alias: sail npm install

# Build dos assets
./vendor/bin/sail npm run build
# Com alias: sail npm run build
```

> **💡 Dica sobre Laravel Sail:**
> 
> O Laravel Sail é uma interface de linha de comando leve para interagir com o ambiente Docker do Laravel. Com o alias configurado, você pode usar comandos mais curtos:
> - `sail up -d` ao invés de `./vendor/bin/sail up -d`
> - `sail artisan migrate` ao invés de `./vendor/bin/sail artisan migrate`
> - `sail npm run dev` ao invés de `./vendor/bin/sail npm run dev`
> 
> Isso torna o desenvolvimento muito mais ágil e prático! O alias funciona tanto no Windows (WSL2) quanto no Linux/macOS.

### 🔧 Opção 2: Instalação Manual

#### Windows (XAMPP)
```bash
# 1. Instalar XAMPP: https://www.apachefriends.org/download.html
# 2. Instalar Composer: https://getcomposer.org/download/
# 3. Instalar Node.js: https://nodejs.org/

# Clonar projeto na pasta htdocs do XAMPP
cd C:\xampp\htdocs
git clone https://github.com/RyanSilva-176/film-stack
cd film-stack

# Copiar arquivo de ambiente
copy .env.example .env

# Instalar dependências
composer install
npm install

# Gerar chave da aplicação
php artisan key:generate

# Configurar banco de dados no .env (usar MySQL do XAMPP)
# Executar migrações
php artisan migrate

# Build dos assets
npm run build

# Iniciar servidor
php artisan serve
```

#### Linux/macOS
```bash
# Instalar dependências do sistema (Ubuntu)
sudo apt update
sudo apt install php8.4 php8.4-cli php8.4-fpm php8.4-mysql php8.4-zip php8.4-gd php8.4-mbstring php8.4-curl php8.4-xml php8.4-bcmath mysql-server nodejs npm

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Clonar repositório
git clone https://github.com/RyanSilva-176/film-stack
cd film-stack

# Copiar arquivo de ambiente
cp .env.example .env

# Instalar dependências
composer install
npm install

# Gerar chave da aplicação
php artisan key:generate

# Configurar banco de dados
php artisan migrate

# Build dos assets
npm run build

# Iniciar servidor
php artisan serve
```

## 🔑 Configuração da API TMDB

### 1. Obter API Key

1. Acesse [https://www.themoviedb.org/](https://www.themoviedb.org/)
2. Crie uma conta gratuita
3. Vá para **Configurações** → **API**
4. Solicite uma chave de API
5. Preencha as informações do projeto
6. Copie sua **API Key (v3 auth)**

### 2. Configurar no .env

```bash
# Adicionar no arquivo .env
TMDB_API_KEY=sua_api_key_aqui
TMDB_API_BASE_URL=https://api.themoviedb.org/3
TMDB_ACCOUNT_ID=seu_account_id_opcional
```

**Nota:** O `TMDB_ACCOUNT_ID` é opcional e usado apenas para testes avançados de conectividade com a API.

## 🔐 Configuração Google OAuth

### 1. Criar Projeto no Google Console

1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Clique em **"Novo Projeto"** ou selecione um existente
3. Nomeie o projeto (ex: "FilmStack App")

### 2. Configurar OAuth 2.0

```bash
# No Google Cloud Console:
# 1. Vá para "APIs e Serviços" → "Credenciais"
# 2. Clique em "+ CRIAR CREDENCIAIS" → "ID do cliente OAuth 2.0"
# 3. Tipo de aplicativo: "Aplicativo da Web"
# 4. Nome: "FilmStack"
# 5. URIs de redirecionamento autorizados:
#    - http://localhost/auth/google/callback (desenvolvimento)
#    - https://seudominio.com/auth/google/callback (produção)
```

### 3. Configurar no .env

```bash
# Adicionar no arquivo .env
GOOGLE_CLIENT_ID=seu_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=seu_client_secret
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

### 4. Configurar Tela de Consentimento

```bash
# No Google Cloud Console:
# 1. "APIs e Serviços" → "Tela de consentimento OAuth"
# 2. Tipo: "Externo" (para uso público)
# 3. Preencher informações obrigatórias:
#    - Nome do app: FilmStack
#    - Email de suporte: seu@email.com
#    - Domínio autorizado: localhost (dev) / seudominio.com (prod)
```

## ⚙️ Variáveis de Ambiente

### Obrigatórias

```bash
# Aplicação
APP_NAME=FilmStack
APP_ENV=production # ou local para desenvolvimento
APP_KEY=base64:sua_chave_gerada_automaticamente
APP_DEBUG=false # true para desenvolvimento
APP_URL=https://seudominio.com

# Banco de Dados
DB_CONNECTION=mysql # ou sqlite para desenvolvimento
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=filmstack
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# TMDB API (OBRIGATÓRIO)
TMDB_API_KEY=sua_api_key_do_tmdb
TMDB_API_BASE_URL=https://api.themoviedb.org/3

# Google OAuth (OBRIGATÓRIO para login social)
## Mas caso deixe vazio, o acesso será via email e senha
GOOGLE_CLIENT_ID=seu_client_id_google
GOOGLE_CLIENT_SECRET=seu_client_secret_google
GOOGLE_REDIRECT_URI=https://seudominio.com/auth/google/callback
```

### Opcionais

```bash
# TMDB Account ID (para testes avançados)
TMDB_ACCOUNT_ID=seu_account_id_tmdb

# Cache
CACHE_STORE=redis # ou database
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Email
MAIL_MAILER=smtp
MAIL_HOST=seu_smtp_host
MAIL_PORT=587
MAIL_USERNAME=seu_email
MAIL_PASSWORD=sua_senha
MAIL_FROM_ADDRESS=noreply@seudominio.com
MAIL_FROM_NAME="FilmStack"

# Armazenamento de arquivos
FILESYSTEM_DISK=s3 # ou local
AWS_ACCESS_KEY_ID=sua_access_key
AWS_SECRET_ACCESS_KEY=sua_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=seu_bucket
```

## 🗃️ Seeder de Dados

O projeto inclui seeders para popular o banco com dados de exemplo:

```bash
# Com Docker/Sail
./vendor/bin/sail artisan db:seed
# Com alias: sail artisan db:seed

# Instalação manual
php artisan db:seed
```

### Dados Criados

- **Usuário de teste**: 
  - Email: `test@example.com`
  - Senha: `password`
- **Listas de exemplo** com filmes populares
- **Dados básicos** para demonstração

## 🎯 Comandos Úteis

### Docker/Sail

```bash
# Iniciar containers
./vendor/bin/sail up -d
# Com alias: sail up -d

# Parar containers
./vendor/bin/sail down
# Com alias: sail down

# Acessar container
./vendor/bin/sail shell
# Com alias: sail shell

# Executar comandos Artisan
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh
./vendor/bin/sail artisan queue:work
# Com alias: sail artisan migrate e sail artisan queue:work

# NPM commands
./vendor/bin/sail npm run dev
./vendor/bin/sail npm run build
# Com alias: sail npm run dev e sail npm run build

# Testes
./vendor/bin/sail test
# Com alias: sail test
```

### Instalação Manual

```bash
# Desenvolvimento
php artisan serve
npm run dev

# Produção
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build

# Testes
php artisan test
```

## 🎨 Desenvolvimento

### Hot Reload (Desenvolvimento)

```bash
# Com Docker
./vendor/bin/sail npm run dev
# Com alias: sail npm run dev

# Manual
npm run dev
```

### Build para Produção

```bash
# Com Docker
./vendor/bin/sail npm run build
# Com alias: sail npm run build

# Manual
npm run build
```

## 📱 Recursos e Funcionalidades

### ✨ Principais Features

- **Sistema de Busca Avançada**: Busque por título, gênero, ano, etc.
- **Listas Personalizadas**: Crie e organize suas coleções de filmes
- **Sistema de Favoritos**: Marque filmes como favoritos
- **Lista de Desejos**: Filmes que você quer assistir
- **Histórico de Assistidos**: Acompanhe o que já assistiu
- **Exploração por Gêneros**: Navegue por categorias
- **Trending e Populares**: Descubra filmes em alta
- **Listas Públicas**: Compartilhe suas listas com outros usuários
- **Interface Responsiva**: Funciona perfeitamente em mobile e desktop
- **Autenticação Social**: Login rápido com Google
- **Tema Escuro**: Interface otimizada para longas sessões

### 🔧 Funcionalidades Técnicas

- **Cache Inteligente**: Reduz chamadas à API TMDB
- **Lazy Loading**: Carregamento otimizado de imagens
- **Infinite Scroll**: Navegação fluida em listas longas
- **Busca em Tempo Real**: Resultados instantâneos conforme você digita
- **Filtros Avançados**: Combine múltiplos critérios de busca
- **Sistema de Notifications**: Toast notifications responsivas
- **Gerenciamento de Estado**: Pinia para estado global reativo

## 🤝 Formas de Acesso

### 1. Registro Tradicional
- Email e senha
- Verificação de email
- Perfil customizável

### 2. Login com Google
- OAuth 2.0 seguro
- Acesso instantâneo
- Sincronização de dados

### 3. Usuário de Teste
```bash
Email: test@example.com
Senha: password
```

## 🔍 Testes

```bash
# Executar todos os testes
./vendor/bin/sail test
# Com alias: sail test
```

## 🚀 Deploy em Produção

### 1. Preparar Servidor

```bash
# Ubuntu 20.04+
sudo apt update
sudo apt install nginx mysql-server php8.4-fpm redis-server certbot

# Configurar PHP-FPM
sudo systemctl enable php8.4-fpm
sudo systemctl start php8.4-fpm
```

### 2. Configurar Nginx

```nginx
server {
    listen 80;
    server_name seudominio.com;
    root /var/www/filmstack/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 3. Deploy da Aplicação

```bash
# Clone do repositório
git clone https://github.com/RyanSilva-176/film-stack /var/www/filmstack
cd /var/www/filmstack

# Instalação de dependências
composer install --optimize-autoloader --no-dev
npm install --production
npm run build

# Configuração
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissões
sudo chown -R www-data:www-data /var/www/filmstack
sudo chmod -R 755 /var/www/filmstack
sudo chmod -R 775 /var/www/filmstack/storage
sudo chmod -R 775 /var/www/filmstack/bootstrap/cache
```

## 📞 Suporte e Contribuição

### 🐛 Reportar Bugs
- Abra uma [issue no GitHub](https://github.com/seu-usuario/film-stack/issues)
- Inclua logs e passos para reproduzir

### 💡 Sugestões
- Use as [GitHub Discussions](https://github.com/seu-usuario/film-stack/discussions)
- Descreva claramente sua ideia

### 🤝 Contribuir
1. Fork o repositório
2. Crie uma branch: `git checkout -b feature/nova-funcionalidade`
3. Commit suas mudanças: `git commit -m 'Adiciona nova funcionalidade'`
4. Push para a branch: `git push origin feature/nova-funcionalidade`
5. Abra um Pull Request

📋 **Leia nosso [Guia de Contribuição](./docs/CONTRIBUTING.md) para mais detalhes**

## 📚 Documentação Completa

| Documento | Descrição |
|-----------|-----------|
| [📖 README](./README.md) | Documentação principal (este arquivo) |
| [⚡ Instalação Rápida](./docs/QUICK-START.md) | Guia express para começar em minutos |
| [🛣️ Rotas](./docs/ROUTES.md) | Todas as rotas da aplicação |
| [🚀 API](./docs/API.md) | Documentação completa da API com exemplos |
| [🤝 Contribuição](./docs/CONTRIBUTING.md) | Como contribuir com o projeto |
| [📋 Changelog](./docs/CHANGELOG.md) | Histórico de versões e mudanças |

## 📄 Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

## 🙏 Agradecimentos

- **The Movie Database (TMDB)** - Dados de filmes
- **Laravel Community** - Framework incrível
- **Vue.js Team** - Framework frontend reativo
- **Tailwind CSS** - Design system consistente

---

<div align="center">

**⭐ Se este projeto foi útil para você, considere dar uma estrela!**

[🌟 Star no GitHub](https://github.com/seu-usuario/film-stack) | [📚 Documentação Completa](./docs/) | [🐛 Reportar Bug](https://github.com/seu-usuario/film-stack/issues)

</div>
