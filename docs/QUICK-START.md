# ⚡ Instalação Rápida - FilmStack

Guia de instalação express para começar a usar o FilmStack em poucos minutos.

## 🚀 Opção 1: Docker (Mais Rápido)

### Pré-requisitos
- Docker e Docker Compose instalados
- Git

### Passos

```bash
# 1. Clone o repositório
git clone https://github.com/RyanSilva-176/film-stack
cd film-stack

# 2. Configure ambiente
cp .env.example .env

# 3. Instale dependências PHP
docker run --rm -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs

# 4. (Opcional) Configure alias para Sail
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc

# 5. Inicie containers
./vendor/bin/sail up -d
# Ou com alias: sail up -d

# 6. Configure aplicação
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
# Com alias: sail artisan key:generate && sail artisan migrate

# 7. Instale dependências frontend
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
# Com alias: sail npm install && sail npm run build

# 8. Acesse http://localhost
```

**💡 Dica:** Com o alias configurado, você pode usar `sail` ao invés de `./vendor/bin/sail` em todos os comandos!

**⚠️ IMPORTANTE:** Configure suas API keys no `.env` antes de usar!

## 🔑 Configuração Mínima Obrigatória

Edite o arquivo `.env` e adicione:

```bash
# TMDB API (OBRIGATÓRIO)
TMDB_API_KEY=sua_chave_aqui

# Google OAuth (OBRIGATÓRIO para login)
GOOGLE_CLIENT_ID=seu_client_id
GOOGLE_CLIENT_SECRET=seu_client_secret
```

### Como obter as chaves:

1. **TMDB API Key**: [themoviedb.org](https://www.themoviedb.org/settings/api)
2. **Google OAuth**: [console.cloud.google.com](https://console.cloud.google.com/)

## 🧪 Teste a Instalação

```bash
# Criar usuário de teste
./vendor/bin/sail artisan db:seed
# Com alias: sail artisan db:seed

# Login de teste:
# Email: test@example.com
# Senha: password
```

## 🆘 Resolução de Problemas

### Docker não inicia
```bash
# Verifique se Docker está rodando
docker --version
docker compose --version

# Limpe containers antigos
./vendor/bin/sail down
docker system prune -f
./vendor/bin/sail up -d
# Com alias: sail down && sail up -d
```

### Erro de permissão (Linux)
```bash
sudo chown -R $USER:$USER .
chmod -R 755 storage bootstrap/cache
```

### API TMDB não funciona
- Verifique se `TMDB_API_KEY` está correto no `.env`
- Teste a chave em: https://api.themoviedb.org/3/movie/550?api_key=SUA_CHAVE

### Login Google não funciona
- Verifique as credenciais no `.env`
- Confirme a URL de callback no Google Console
- URL deve ser: `http://localhost/auth/google/callback`

## 🔄 Comandos Úteis

```bash
# Parar aplicação
./vendor/bin/sail down
# Com alias: sail down

# Ver logs
./vendor/bin/sail logs
# Com alias: sail logs

# Executar comandos Laravel
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan queue:work
# Com alias: sail artisan migrate

# Desenvolvimento frontend
./vendor/bin/sail npm run dev
# Com alias: sail npm run dev
```

**💡 Lembre-se:** Se você configurou o alias, pode usar `sail` em vez de `./vendor/bin/sail`!

## ✅ Próximos Passos

1. 📖 Leia a [documentação completa](../README.md)
2. 🛣️ Explore as [rotas da aplicação](./ROUTES.md)
3. 🚀 Veja a [documentação da API](./API.md)
4. 🤝 Contribua seguindo o [guia de contribuição](./CONTRIBUTING.md)

---

<div align="center">

**🎉 Pronto! Sua aplicação FilmStack está rodando em [http://localhost](http://localhost)**

[📚 Documentação Completa](../README.md) | [🐛 Reportar Problema](https://github.com/seu-usuario/film-stack/issues)

</div>
