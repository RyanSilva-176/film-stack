# 🛣️ Documentação de Rotas - FilmStack

Esta documentação detalha todas as rotas disponíveis na aplicação FilmStack, organizadas por categoria e funcionalidade.

## 📋 Índice

- [Rotas Web (Frontend)](#rotas-web-frontend)
- [Rotas de Autenticação](#rotas-de-autenticação)
- [API TMDB (Protegida)](#api-tmdb-protegida)
- [API TMDB (Pública)](#api-tmdb-pública)
- [API de Listas de Filmes](#api-de-listas-de-filmes)
- [Rotas de Configurações](#rotas-de-configurações)
- [Middlewares Utilizados](#middlewares-utilizados)

---

## 🌐 Rotas Web (Frontend)

### Página Inicial
| Método | URI | Nome | Descrição | Middleware |
|--------|-----|------|-----------|------------|
| `GET` | `/` | `home` | Página inicial pública | - |

### Área Autenticada
| Método | URI | Nome | Descrição | Middleware |
|--------|-----|------|-----------|------------|
| `GET` | `/dashboard` | `dashboard` | Dashboard principal do usuário | `auth, verified` |
| `GET` | `/search` | `search` | Página de busca de filmes | `auth, verified` |
| `GET` | `/genres` | `genres` | Exploração por gêneros | `auth, verified` |

### Listas de Filmes
| Método | URI | Nome | Descrição | Middleware |
|--------|-----|------|-----------|------------|
| `GET` | `/lists/liked` | `lists.liked` | Filmes favoritos do usuário | `auth, verified` |
| `GET` | `/lists/watchlist` | `lists.watchlist` | Lista de desejos | `auth, verified` |
| `GET` | `/lists/watched` | `lists.watched` | Filmes assistidos | `auth, verified` |
| `GET` | `/lists/custom` | `lists.custom` | Listas personalizadas | `auth, verified` |
| `GET` | `/lists/{movieList}` | `lists.detail` | Detalhes de lista personalizada | `auth, verified` |

### Listas Públicas
| Método | URI | Nome | Descrição | Middleware |
|--------|-----|------|-----------|------------|
| `GET` | `/public-movie-lists/{movieList}` | `public-movie-lists.show` | Visualizar lista pública | `public-list` |

### Redirecionamentos
| Método | URI | Nome | Descrição | Middleware |
|--------|-----|------|-----------|------------|
| `GET` | `/movie-lists/{movieList}` | `movie-lists.detail.redirect` | Redirect para nova URL de listas | - |

---

## 🔐 Rotas de Autenticação

### OAuth Google
| Método | URI | Descrição | Middleware |
|--------|-----|-----------|------------|
| `GET` | `/auth/google/redirect` | Redirecionamento para Google OAuth | - |
| `GET` | `/auth/google/callback` | Callback do Google OAuth | - |

### Testes de Conectividade
| Método | URI | Nome | Descrição | Middleware |
|--------|-----|------|-----------|------------|
| `GET` | `/test-connectivity` | `test.connectivity` | Teste de conectividade com APIs | - |

### Rotas Padrão do Laravel (Breeze)
| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| `GET` | `/login` | `login` | Página de login |
| `POST` | `/login` | - | Processar login |
| `GET` | `/register` | `register` | Página de registro |
| `POST` | `/register` | - | Processar registro |
| `POST` | `/logout` | `logout` | Logout do usuário |
| `GET` | `/forgot-password` | `password.request` | Solicitar reset de senha |
| `POST` | `/forgot-password` | `password.email` | Enviar email de reset |
| `GET` | `/reset-password/{token}` | `password.reset` | Formulário de reset |
| `POST` | `/reset-password` | `password.update` | Atualizar senha |
| `GET` | `/verify-email` | `verification.notice` | Aviso de verificação |
| `POST` | `/email/verification-notification` | `verification.send` | Reenviar verificação |

---

## 🎬 API TMDB (Protegida)

**Base URI:** `/api/tmdb`  
**Middleware:** `web, auth, verified`

### Testes e Conectividade
| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| `GET` | `/test-account-details` | `api.tmdb.account.test` | Testa detalhes da conta TMDB |

### Gêneros
| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| `GET` | `/genres` | `api.tmdb.genres` | Lista todos os gêneros de filmes |

### Filmes - Descoberta
| Método | URI | Nome | Descrição | Parâmetros |
|--------|-----|------|-----------|------------|
| `GET` | `/movies/popular` | `api.tmdb.movies.popular` | Filmes populares | `page`, `language` |
| `GET` | `/movies/trending` | `api.tmdb.movies.trending` | Filmes em alta | `page`, `time_window` |
| `GET` | `/movies/discover` | `api.tmdb.movies.discover` | Descobrir filmes com filtros | `genre`, `year`, `sort_by`, etc. |

### Filmes - Busca
| Método | URI | Nome | Descrição | Parâmetros |
|--------|-----|------|-----------|------------|
| `GET` | `/movies/search` | `api.tmdb.movies.search` | Buscar filmes por texto | `query`, `page`, `year` |
| `GET` | `/movies/genre/{genreId}` | `api.tmdb.movies.by-genre` | Filmes por gênero específico | `page`, `sort_by` |

### Filmes - Detalhes
| Método | URI | Nome | Descrição | Parâmetros |
|--------|-----|------|-----------|------------|
| `GET` | `/movies/{movieId}` | `api.tmdb.movies.details` | Detalhes de filme específico | `append_to_response` |
| `POST` | `/movies/batch` | `api.tmdb.movies.batch` | Buscar múltiplos filmes por IDs | `ids[]` |
| `GET` | `/movies/{movieId}/images` | `api.tmdb.movies.images` | Imagens do filme | `include_image_language` |

---

## 🌍 API TMDB (Pública)

**Base URI:** `/api/public/tmdb`  
**Middleware:** Nenhum

### Filmes Públicos
| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| `GET` | `/movies/trending` | `api.public.tmdb.movies.trending` | Filmes em alta (público) |
| `GET` | `/movies/popular` | `api.public.tmdb.movies.popular` | Filmes populares (público) |
| `GET` | `/movies/{movieId}` | `api.public.tmdb.movies.details` | Detalhes do filme (público) |
| `GET` | `/genres` | `api.public.tmdb.genres` | Gêneros de filmes (público) |

---

## 📝 API de Listas de Filmes

**Base URI:** `/api`  
**Middleware:** `web, auth, verified`

### Gerenciamento de Listas
| Método | URI | Nome | Descrição | Parâmetros |
|--------|-----|------|-----------|------------|
| `GET` | `/movie-lists` | `api.movie-lists.index` | Listar todas as listas do usuário | `type`, `page` |
| `POST` | `/movie-lists` | `api.movie-lists.store` | Criar nova lista | `name`, `description`, `is_public` |
| `GET` | `/movie-lists/{movieList}` | `api.movie-lists.show` | Detalhes de lista específica | `include_movies` |
| `PUT` | `/movie-lists/{movieList}` | `api.movie-lists.update` | Atualizar lista | `name`, `description`, `is_public` |
| `DELETE` | `/movie-lists/{movieList}` | `api.movie-lists.destroy` | Deletar lista | - |

### Gerenciamento de Filmes nas Listas
| Método | URI | Nome | Descrição | Parâmetros |
|--------|-----|------|-----------|------------|
| `POST` | `/movie-lists/{movieList}/movies` | `api.movie-lists.add-movie` | Adicionar filme à lista | `tmdb_movie_id`, `movie_data` |
| `DELETE` | `/movie-lists/{movieList}/movies/{tmdbMovieId}` | `api.movie-lists.remove-movie` | Remover filme da lista | - |

### Ações em Filmes
| Método | URI | Nome | Descrição | Parâmetros |
|--------|-----|------|-----------|------------|
| `POST` | `/movies/mark-watched` | `api.movies.mark-watched` | Marcar filme como assistido | `tmdb_movie_id`, `movie_data` |
| `POST` | `/movies/toggle-like` | `api.movies.toggle-like` | Alternar like/unlike no filme | `tmdb_movie_id`, `movie_data` |

### Operações em Lote
| Método | URI | Nome | Descrição | Parâmetros |
|--------|-----|------|-----------|------------|
| `DELETE` | `/movie-lists/{movieList}/bulk-remove` | `api.movie-lists.bulk-remove` | Remover múltiplos filmes | `movie_ids[]` |
| `POST` | `/movie-lists/bulk-move` | `api.movie-lists.bulk-move` | Mover filmes entre listas | `from_list_id`, `to_list_id`, `movie_ids[]` |
| `POST` | `/movies/bulk-mark-watched` | `api.movies.bulk-mark-watched` | Marcar múltiplos como assistidos | `movie_ids[]` |

---

## ⚙️ Rotas de Configurações

**Base URI:** `/settings`  
**Middleware:** `auth`

| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| `GET` | `/settings` | - | Redirect para `/settings/profile` |
| `GET` | `/settings/profile` | `profile.edit` | Página de edição de perfil |
| `PATCH` | `/settings/profile` | `profile.update` | Atualizar dados do perfil |
| `DELETE` | `/settings/profile` | `profile.destroy` | Deletar conta do usuário |

---

## 🛡️ Middlewares Utilizados

### Middleware Padrão
| Nome | Descrição |
|------|-----------|
| `web` | Sessões, CSRF, cookies |
| `auth` | Usuário autenticado |
| `verified` | Email verificado |
| `guest` | Usuário não autenticado |

### Middleware Customizado
| Nome | Descrição |
|------|-----------|
| `public-list` | Valida acesso a listas públicas |

---

## 📊 Códigos de Status HTTP

### Sucesso
| Código | Descrição |
|--------|-----------|
| `200` | OK - Requisição bem-sucedida |
| `201` | Created - Recurso criado com sucesso |
| `204` | No Content - Operação bem-sucedida sem conteúdo |

### Erro do Cliente
| Código | Descrição |
|--------|-----------|
| `400` | Bad Request - Dados inválidos |
| `401` | Unauthorized - Não autenticado |
| `403` | Forbidden - Sem permissão |
| `404` | Not Found - Recurso não encontrado |
| `422` | Unprocessable Entity - Erro de validação |

### Erro do Servidor
| Código | Descrição |
|--------|-----------|
| `500` | Internal Server Error - Erro interno |
| `503` | Service Unavailable - Serviço indisponível |

---

## 🔍 Exemplos de Uso

### Buscar Filmes
```javascript
// Busca por texto
GET /api/tmdb/movies/search?query=avengers&page=1

// Busca por gênero
GET /api/tmdb/movies/genre/28?page=1&sort_by=popularity.desc

// Descobrir filmes
GET /api/tmdb/movies/discover?with_genres=28,12&year=2023
```

### Gerenciar Listas
```javascript
// Criar lista
POST /api/movie-lists
{
  "name": "Melhores Sci-Fi",
  "description": "Meus filmes de ficção científica favoritos",
  "is_public": true
}

// Adicionar filme à lista
POST /api/movie-lists/1/movies
{
  "tmdb_movie_id": 550,
  "movie_data": {
    "title": "Fight Club",
    "poster_path": "/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg"
  }
}
```

### Marcar Filme como Assistido
```javascript
POST /api/movies/mark-watched
{
  "tmdb_movie_id": 550,
  "movie_data": {
    "title": "Fight Club",
    "poster_path": "/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg",
    "release_date": "1999-10-15"
  }
}
```

---

## 📝 Notas Importantes

1. **Autenticação**: A maioria das rotas requer autenticação via sessão Laravel
2. **Rate Limiting**: APIs externas podem ter limitações de taxa
3. **Validação**: Todos os endpoints validam dados de entrada
4. **CSRF**: Rotas POST/PUT/DELETE requerem token CSRF
5. **Pagination**: Resultados de listas são paginados
6. **Cache**: Algumas respostas são cacheadas para melhor performance

---

<div align="center">

**📚 [Voltar ao README Principal](./README.md) | 🐛 [Reportar Bug](https://github.com/seu-usuario/film-stack/issues)**

</div>
