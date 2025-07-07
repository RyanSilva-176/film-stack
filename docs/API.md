# 🚀 API Documentation - FilmStack

Documentação completa da API do FilmStack com exemplos práticos de uso, códigos de resposta e estruturas de dados.

## 📋 Índice

- [Autenticação](#autenticação)
- [API TMDB](#api-tmdb)
  - [Gêneros](#gêneros)
  - [Filmes Populares](#filmes-populares)
  - [Filmes Trending](#filmes-trending)
  - [Buscar Filmes](#buscar-filmes)
  - [Detalhes do Filme](#detalhes-do-filme)
  - [Descobrir Filmes](#descobrir-filmes)
- [API de Listas](#api-de-listas)
  - [Listar Listas](#listar-listas)
  - [Criar Lista](#criar-lista)
  - [Gerenciar Filmes](#gerenciar-filmes)
- [Códigos de Status](#códigos-de-status)
- [Estruturas de Dados](#estruturas-de-dados)

---

## 🔐 Autenticação

A API utiliza autenticação baseada em sessão Laravel. Para rotas protegidas, é necessário estar logado.

### Headers Necessários
```http
Content-Type: application/json
X-CSRF-TOKEN: {csrf_token}
```

### Obter CSRF Token
```javascript
// Via meta tag (recomendado)
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Via API
fetch('/api/csrf-token')
  .then(response => response.json())
  .then(data => console.log(data.token));
```

---

## 🎬 API TMDB

### Gêneros

<details>
<summary><strong>GET</strong> <code>/api/tmdb/genres</code> - Listar Gêneros</summary>

#### Descrição
Retorna todos os gêneros de filmes disponíveis.

#### Parâmetros
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `language` | string | ❌ | Idioma (padrão: pt-BR) |

#### Exemplo de Requisição
```javascript
fetch('/api/tmdb/genres?language=pt-BR', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  }
})
```

#### Exemplo de Resposta
```json
{
  "success": true,
  "data": {
    "genres": [
      {
        "id": 28,
        "name": "Ação"
      },
      {
        "id": 12,
        "name": "Aventura"
      },
      {
        "id": 16,
        "name": "Animação"
      }
    ]
  }
}
```

#### Códigos de Status
- `200` - Sucesso
- `401` - Não autenticado
- `500` - Erro na API TMDB

</details>

### Filmes Populares

<details>
<summary><strong>GET</strong> <code>/api/tmdb/movies/popular</code> - Filmes Populares</summary>

#### Descrição
Retorna lista de filmes populares atualmente.

#### Parâmetros
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `page` | integer | ❌ | Página (padrão: 1) |
| `language` | string | ❌ | Idioma (padrão: pt-BR) |

#### Exemplo de Requisição
```javascript
fetch('/api/tmdb/movies/popular?page=1&language=pt-BR', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  }
})
```

#### Exemplo de Resposta
```json
{
  "success": true,
  "data": {
    "page": 1,
    "total_pages": 500,
    "total_results": 10000,
    "results": [
      {
        "id": 550,
        "title": "Clube da Luta",
        "original_title": "Fight Club",
        "overview": "Um funcionário deprimido...",
        "poster_path": "/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg",
        "backdrop_path": "/87hTDiay2N2qWyX4Ds7ybXi9h8I.jpg",
        "release_date": "1999-10-15",
        "vote_average": 8.4,
        "vote_count": 26280,
        "popularity": 61.416,
        "genre_ids": [18, 53],
        "genres": [
          {"id": 18, "name": "Drama"},
          {"id": 53, "name": "Thriller"}
        ],
        "adult": false,
        "video": false,
        "original_language": "en"
      }
    ]
  }
}
```

</details>

### Filmes Trending

<details>
<summary><strong>GET</strong> <code>/api/tmdb/movies/trending</code> - Filmes em Alta</summary>

#### Descrição
Retorna filmes que estão em alta (trending).

#### Parâmetros
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `page` | integer | ❌ | Página (padrão: 1) |
| `time_window` | string | ❌ | `day` ou `week` (padrão: day) |
| `language` | string | ❌ | Idioma (padrão: pt-BR) |

#### Exemplo de Requisição
```javascript
fetch('/api/tmdb/movies/trending?page=1&time_window=week', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  }
})
```

#### Estrutura de resposta similar a [Filmes Populares](#filmes-populares)

</details>

### Buscar Filmes

<details>
<summary><strong>GET</strong> <code>/api/tmdb/movies/search</code> - Buscar por Texto</summary>

#### Descrição
Busca filmes por título ou palavras-chave.

#### Parâmetros
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `query` | string | ✅ | Termo de busca |
| `page` | integer | ❌ | Página (padrão: 1) |
| `year` | integer | ❌ | Ano de lançamento |
| `language` | string | ❌ | Idioma (padrão: pt-BR) |

#### Exemplo de Requisição
```javascript
fetch('/api/tmdb/movies/search?query=avengers&page=1&year=2019', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  }
})
```

#### Exemplo de Resposta
```json
{
  "success": true,
  "data": {
    "page": 1,
    "total_pages": 5,
    "total_results": 87,
    "results": [
      {
        "id": 299534,
        "title": "Vingadores: Ultimato",
        "original_title": "Avengers: Endgame",
        "overview": "Após os eventos devastadores...",
        "poster_path": "/or06FN3Dka5tukK1e9sl16pB3iy.jpg",
        "release_date": "2019-04-24",
        "vote_average": 8.3,
        "genre_ids": [12, 878, 28],
        "genres": [
          {"id": 12, "name": "Aventura"},
          {"id": 878, "name": "Ficção Científica"},
          {"id": 28, "name": "Ação"}
        ]
      }
    ]
  }
}
```

</details>

<details>
<summary><strong>GET</strong> <code>/api/tmdb/movies/genre/{genreId}</code> - Buscar por Gênero</summary>

#### Descrição
Retorna filmes de um gênero específico.

#### Parâmetros de URL
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `genreId` | integer | ✅ | ID do gênero |

#### Parâmetros de Query
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `page` | integer | ❌ | Página (padrão: 1) |
| `sort_by` | string | ❌ | Ordenação (ex: popularity.desc) |
| `language` | string | ❌ | Idioma (padrão: pt-BR) |

#### Exemplo de Requisição
```javascript
// Buscar filmes de ação (ID 28)
fetch('/api/tmdb/movies/genre/28?page=1&sort_by=popularity.desc', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  }
})
```

</details>

### Detalhes do Filme

<details>
<summary><strong>GET</strong> <code>/api/tmdb/movies/{movieId}</code> - Detalhes Completos</summary>

#### Descrição
Retorna informações detalhadas de um filme específico.

#### Parâmetros de URL
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `movieId` | integer | ✅ | ID do filme no TMDB |

#### Parâmetros de Query
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `append_to_response` | string | ❌ | Dados extras (credits,videos,similar) |
| `language` | string | ❌ | Idioma (padrão: pt-BR) |

#### Exemplo de Requisição
```javascript
fetch('/api/tmdb/movies/550?append_to_response=credits,videos', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  }
})
```

#### Exemplo de Resposta
```json
{
  "success": true,
  "data": {
    "id": 550,
    "title": "Clube da Luta",
    "original_title": "Fight Club",
    "overview": "Um funcionário deprimido de uma grande corporação...",
    "tagline": "Mischief. Mayhem. Soap.",
    "runtime": 139,
    "budget": 63000000,
    "revenue": 100853753,
    "poster_path": "/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg",
    "backdrop_path": "/87hTDiay2N2qWyX4Ds7ybXi9h8I.jpg",
    "release_date": "1999-10-15",
    "vote_average": 8.433,
    "vote_count": 26280,
    "popularity": 61.416,
    "status": "Released",
    "original_language": "en",
    "spoken_languages": [
      {
        "english_name": "English",
        "iso_639_1": "en",
        "name": "English"
      }
    ],
    "production_countries": [
      {
        "iso_3166_1": "US",
        "name": "United States of America"
      }
    ],
    "genres": [
      {"id": 18, "name": "Drama"},
      {"id": 53, "name": "Thriller"}
    ],
    "production_companies": [
      {
        "id": 508,
        "logo_path": "/7PzJdsLGlR7oW4J0J5Xcd0pHGRg.png",
        "name": "Regency Enterprises",
        "origin_country": "US"
      }
    ],
    "credits": {
      "cast": [
        {
          "id": 819,
          "name": "Edward Norton",
          "character": "The Narrator",
          "profile_path": "/5XBzD5WuTyVQZeS4VI25z2moMeY.jpg"
        }
      ],
      "crew": [
        {
          "id": 7467,
          "name": "David Fincher",
          "job": "Director",
          "profile_path": "/tpEczFclQZeKAiCeKZZ0adRvtfz.jpg"
        }
      ]
    }
  }
}
```

</details>

<details>
<summary><strong>POST</strong> <code>/api/tmdb/movies/batch</code> - Buscar Múltiplos Filmes</summary>

#### Descrição
Busca informações de múltiplos filmes por seus IDs.

#### Corpo da Requisição
```json
{
  "ids": [550, 299534, 157336]
}
```

#### Exemplo de Requisição
```javascript
fetch('/api/tmdb/movies/batch', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  },
  body: JSON.stringify({
    ids: [550, 299534, 157336]
  })
})
```

#### Exemplo de Resposta
```json
{
  "success": true,
  "data": {
    "movies": [
      {
        "id": 550,
        "title": "Clube da Luta",
        "poster_path": "/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg"
      },
      {
        "id": 299534,
        "title": "Vingadores: Ultimato",
        "poster_path": "/or06FN3Dka5tukK1e9sl16pB3iy.jpg"
      }
    ]
  }
}
```

</details>

### Descobrir Filmes

<details>
<summary><strong>GET</strong> <code>/api/tmdb/movies/discover</code> - Descobrir com Filtros</summary>

#### Descrição
Descobre filmes usando filtros avançados.

#### Parâmetros
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `with_genres` | string | ❌ | IDs de gêneros separados por vírgula |
| `year` | integer | ❌ | Ano de lançamento |
| `primary_release_year` | integer | ❌ | Ano de lançamento primário |
| `sort_by` | string | ❌ | Ordenação (popularity.desc, release_date.desc, etc.) |
| `vote_average.gte` | float | ❌ | Nota mínima |
| `vote_count.gte` | integer | ❌ | Quantidade mínima de votos |
| `with_runtime.gte` | integer | ❌ | Duração mínima em minutos |
| `with_runtime.lte` | integer | ❌ | Duração máxima em minutos |
| `page` | integer | ❌ | Página (padrão: 1) |

#### Exemplo de Requisição
```javascript
fetch('/api/tmdb/movies/discover?with_genres=28,12&year=2023&sort_by=popularity.desc&vote_average.gte=7.0', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  }
})
```

#### Estrutura de resposta similar a [Filmes Populares](#filmes-populares)

</details>

---

## 📝 API de Listas

### Listar Listas

<details>
<summary><strong>GET</strong> <code>/api/movie-lists</code> - Listas do Usuário</summary>

#### Descrição
Retorna todas as listas de filmes do usuário autenticado.

#### Parâmetros
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `type` | string | ❌ | Tipo da lista (custom, liked, watchlist, watched) |
| `page` | integer | ❌ | Página (padrão: 1) |
| `per_page` | integer | ❌ | Itens por página (padrão: 15) |

#### Exemplo de Requisição
```javascript
fetch('/api/movie-lists?type=custom&page=1', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  }
})
```

#### Exemplo de Resposta
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Melhores Sci-Fi",
        "description": "Meus filmes de ficção científica favoritos",
        "type": "custom",
        "is_public": true,
        "movies_count": 15,
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-20T14:25:00Z",
        "user": {
          "id": 1,
          "name": "João Silva"
        }
      }
    ],
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

</details>

### Criar Lista

<details>
<summary><strong>POST</strong> <code>/api/movie-lists</code> - Criar Nova Lista</summary>

#### Descrição
Cria uma nova lista personalizada de filmes.

#### Corpo da Requisição
```json
{
  "name": "Melhores Sci-Fi",
  "description": "Meus filmes de ficção científica favoritos",
  "is_public": true
}
```

#### Validação
| Campo | Regras |
|-------|--------|
| `name` | Obrigatório, string, máx 255 caracteres |
| `description` | Opcional, string, máx 1000 caracteres |
| `is_public` | Opcional, boolean (padrão: false) |

#### Exemplo de Requisição
```javascript
fetch('/api/movie-lists', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  },
  body: JSON.stringify({
    name: 'Melhores Sci-Fi',
    description: 'Meus filmes de ficção científica favoritos',
    is_public: true
  })
})
```

#### Exemplo de Resposta
```json
{
  "success": true,
  "message": "Lista criada com sucesso",
  "data": {
    "id": 5,
    "name": "Melhores Sci-Fi",
    "description": "Meus filmes de ficção científica favoritos",
    "type": "custom",
    "is_public": true,
    "movies_count": 0,
    "created_at": "2024-01-25T15:30:00Z",
    "updated_at": "2024-01-25T15:30:00Z"
  }
}
```

</details>

<details>
<summary><strong>PUT</strong> <code>/api/movie-lists/{id}</code> - Atualizar Lista</summary>

#### Descrição
Atualiza uma lista existente do usuário.

#### Parâmetros de URL
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `id` | integer | ✅ | ID da lista |

#### Corpo da Requisição
```json
{
  "name": "Sci-Fi Clássicos",
  "description": "Filmes clássicos de ficção científica",
  "is_public": false
}
```

#### Exemplo de Requisição
```javascript
fetch('/api/movie-lists/5', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  },
  body: JSON.stringify({
    name: 'Sci-Fi Clássicos',
    description: 'Filmes clássicos de ficção científica',
    is_public: false
  })
})
```

</details>

<details>
<summary><strong>DELETE</strong> <code>/api/movie-lists/{id}</code> - Deletar Lista</summary>

#### Descrição
Remove uma lista personalizada do usuário.

#### Parâmetros de URL
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `id` | integer | ✅ | ID da lista |

#### Exemplo de Requisição
```javascript
fetch('/api/movie-lists/5', {
  method: 'DELETE',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  }
})
```

#### Exemplo de Resposta
```json
{
  "success": true,
  "message": "Lista deletada com sucesso"
}
```

</details>

### Gerenciar Filmes

<details>
<summary><strong>POST</strong> <code>/api/movie-lists/{listId}/movies</code> - Adicionar Filme</summary>

#### Descrição
Adiciona um filme a uma lista específica.

#### Parâmetros de URL
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `listId` | integer | ✅ | ID da lista |

#### Corpo da Requisição
```json
{
  "tmdb_movie_id": 550,
  "movie_data": {
    "title": "Clube da Luta",
    "original_title": "Fight Club",
    "poster_path": "/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg",
    "release_date": "1999-10-15",
    "vote_average": 8.4,
    "overview": "Um funcionário deprimido...",
    "genres": [
      {"id": 18, "name": "Drama"},
      {"id": 53, "name": "Thriller"}
    ]
  }
}
```

#### Exemplo de Requisição
```javascript
fetch('/api/movie-lists/1/movies', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  },
  body: JSON.stringify({
    tmdb_movie_id: 550,
    movie_data: {
      title: 'Clube da Luta',
      poster_path: '/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg',
      release_date: '1999-10-15',
      vote_average: 8.4
    }
  })
})
```

#### Exemplo de Resposta
```json
{
  "success": true,
  "message": "Filme adicionado à lista com sucesso",
  "data": {
    "movie_list_item_id": 123,
    "movie_list": {
      "id": 1,
      "name": "Melhores Sci-Fi",
      "movies_count": 16
    }
  }
}
```

</details>

<details>
<summary><strong>DELETE</strong> <code>/api/movie-lists/{listId}/movies/{movieId}</code> - Remover Filme</summary>

#### Descrição
Remove um filme de uma lista específica.

#### Parâmetros de URL
| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `listId` | integer | ✅ | ID da lista |
| `movieId` | integer | ✅ | ID do filme no TMDB |

#### Exemplo de Requisição
```javascript
fetch('/api/movie-lists/1/movies/550', {
  method: 'DELETE',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  }
})
```

#### Exemplo de Resposta
```json
{
  "success": true,
  "message": "Filme removido da lista com sucesso"
}
```

</details>

<details>
<summary><strong>POST</strong> <code>/api/movies/mark-watched</code> - Marcar como Assistido</summary>

#### Descrição
Marca um filme como assistido pelo usuário.

#### Corpo da Requisição
```json
{
  "tmdb_movie_id": 550,
  "movie_data": {
    "title": "Clube da Luta",
    "poster_path": "/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg",
    "release_date": "1999-10-15",
    "vote_average": 8.4
  }
}
```

#### Exemplo de Requisição
```javascript
fetch('/api/movies/mark-watched', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  },
  body: JSON.stringify({
    tmdb_movie_id: 550,
    movie_data: {
      title: 'Clube da Luta',
      poster_path: '/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg'
    }
  })
})
```

</details>

<details>
<summary><strong>POST</strong> <code>/api/movies/toggle-like</code> - Alternar Favorito</summary>

#### Descrição
Adiciona ou remove um filme dos favoritos do usuário.

#### Corpo da Requisição
```json
{
  "tmdb_movie_id": 550,
  "movie_data": {
    "title": "Clube da Luta",
    "poster_path": "/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg",
    "release_date": "1999-10-15",
    "vote_average": 8.4
  }
}
```

#### Exemplo de Resposta
```json
{
  "success": true,
  "message": "Filme adicionado aos favoritos",
  "data": {
    "is_liked": true
  }
}
```

</details>

---

## 📊 Códigos de Status

### Sucesso (2xx)
| Código | Significado | Uso |
|--------|-------------|-----|
| `200` | OK | Requisição bem-sucedida |
| `201` | Created | Recurso criado com sucesso |
| `204` | No Content | Operação bem-sucedida sem conteúdo de resposta |

### Erro do Cliente (4xx)
| Código | Significado | Causa Comum |
|--------|-------------|-------------|
| `400` | Bad Request | Dados de entrada inválidos |
| `401` | Unauthorized | Não autenticado |
| `403` | Forbidden | Sem permissão para acessar o recurso |
| `404` | Not Found | Recurso não encontrado |
| `422` | Unprocessable Entity | Erro de validação |
| `429` | Too Many Requests | Rate limit excedido |

### Erro do Servidor (5xx)
| Código | Significado | Causa Comum |
|--------|-------------|-------------|
| `500` | Internal Server Error | Erro interno da aplicação |
| `502` | Bad Gateway | Erro na API externa (TMDB) |
| `503` | Service Unavailable | Serviço temporariamente indisponível |

---

## 📋 Estruturas de Dados

### Filme (Movie)
```typescript
interface Movie {
  id: number;
  title: string;
  original_title: string;
  overview: string;
  poster_path: string | null;
  backdrop_path: string | null;
  release_date: string;
  vote_average: number;
  vote_count: number;
  popularity: number;
  genre_ids: number[];
  genres: Genre[];
  adult: boolean;
  video: boolean;
  original_language: string;
}
```

### Filme Detalhado (Detailed Movie)
```typescript
interface DetailedMovie extends Movie {
  tagline: string;
  runtime: number;
  budget: number;
  revenue: number;
  status: string;
  spoken_languages: Language[];
  production_countries: Country[];
  production_companies: Company[];
  credits?: {
    cast: CastMember[];
    crew: CrewMember[];
  };
  videos?: {
    results: Video[];
  };
  similar?: {
    results: Movie[];
  };
}
```

### Lista de Filmes (Movie List)
```typescript
interface MovieList {
  id: number;
  name: string;
  description: string | null;
  type: 'custom' | 'liked' | 'watchlist' | 'watched';
  is_public: boolean;
  movies_count: number;
  created_at: string;
  updated_at: string;
  user: {
    id: number;
    name: string;
  };
  movies?: MovieListItem[];
}
```

### Item da Lista (Movie List Item)
```typescript
interface MovieListItem {
  id: number;
  tmdb_movie_id: number;
  movie_data: Movie;
  added_at: string;
  notes: string | null;
}
```

### Gênero (Genre)
```typescript
interface Genre {
  id: number;
  name: string;
}
```

### Resposta Paginada
```typescript
interface PaginatedResponse<T> {
  current_page: number;
  data: T[];
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  links: PaginationLink[];
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number;
  total: number;
}
```

### Resposta de Erro
```typescript
interface ErrorResponse {
  success: false;
  message: string;
  errors?: {
    [field: string]: string[];
  };
  code?: string;
}
```

---

## 🧪 Exemplos de Uso Completos

### Buscar e Adicionar Filme à Lista
```javascript
async function addMovieToList(movieId, listId) {
  try {
    // 1. Buscar detalhes do filme
    const movieResponse = await fetch(`/api/tmdb/movies/${movieId}`, {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    });
    
    const movieData = await movieResponse.json();
    
    if (!movieData.success) {
      throw new Error('Filme não encontrado');
    }
    
    // 2. Adicionar à lista
    const addResponse = await fetch(`/api/movie-lists/${listId}/movies`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        tmdb_movie_id: movieId,
        movie_data: movieData.data
      })
    });
    
    const addResult = await addResponse.json();
    
    if (addResult.success) {
      console.log('Filme adicionado com sucesso!');
    }
    
  } catch (error) {
    console.error('Erro:', error.message);
  }
}
```

### Buscar Filmes com Filtros Avançados
```javascript
async function searchAdvanced(filters) {
  const params = new URLSearchParams();
  
  if (filters.query) params.append('query', filters.query);
  if (filters.genres?.length) params.append('with_genres', filters.genres.join(','));
  if (filters.year) params.append('year', filters.year);
  if (filters.minRating) params.append('vote_average.gte', filters.minRating);
  if (filters.page) params.append('page', filters.page);
  
  const endpoint = filters.query 
    ? `/api/tmdb/movies/search?${params}`
    : `/api/tmdb/movies/discover?${params}`;
  
  const response = await fetch(endpoint, {
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  });
  
  return await response.json();
}

// Uso
const results = await searchAdvanced({
  query: 'batman',
  genres: [28, 80], // Ação e Crime
  year: 2020,
  minRating: 7.0,
  page: 1
});
```

---

<div align="center">

**📚 [Voltar ao README Principal](../README.md) | 🛣️ [Ver Todas as Rotas](./ROUTES.md)**

</div>
