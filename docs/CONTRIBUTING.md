# 🤝 Guia de Contribuição - FilmStack

Obrigado por considerar contribuir com o FilmStack! Este documento fornece diretrizes para contribuições efetivas.

## 📋 Índice

- [Como Contribuir](#como-contribuir)
- [Configuração do Ambiente](#configuração-do-ambiente)
- [Padrões de Código](#padrões-de-código)
- [Processo de Contribuição](#processo-de-contribuição)
- [Relatando Bugs](#relatando-bugs)
- [Sugerindo Melhorias](#sugerindo-melhorias)
- [Pull Requests](#pull-requests)

## 🚀 Como Contribuir

Existem várias maneiras de contribuir com o FilmStack:

### 🐛 Reportando Bugs
- Verifique se o bug já foi reportado
- Crie uma issue detalhada com passos para reproduzir
- Inclua screenshots, logs ou código relevante

### 💡 Sugerindo Funcionalidades
- Abra uma discussion para discutir a ideia
- Explique o problema que a funcionalidade resolveria
- Descreva a solução proposta

### 📝 Melhorando Documentação
- Corrija erros de digitação ou gramática
- Adicione exemplos ou clarificações
- Traduza documentação para outros idiomas

### 💻 Contribuindo com Código
- Implemente novas funcionalidades
- Corrija bugs existentes
- Melhore performance
- Adicione testes

## ⚙️ Configuração do Ambiente

### 1. Fork e Clone
```bash
# Fork o repositório no GitHub
# Clone seu fork
git clone https://github.com/SEU-USUARIO/film-stack.git
cd film-stack

# Adicione o repositório original como upstream
git remote add upstream https://github.com/USUARIO-ORIGINAL/film-stack.git
```

### 2. Configuração com Docker (Recomendado)
```bash
# Copie o arquivo de ambiente
cp .env.example .env

# Instale dependências
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# Inicie os containers
./vendor/bin/sail up -d

# Configure a aplicação
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate

# Instale dependências do frontend
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

### 3. Configuração Manual
```bash
# Instale dependências
composer install
npm install

# Configure a aplicação
php artisan key:generate
php artisan migrate

# Inicie o servidor
php artisan serve
npm run dev
```

## 🎨 Padrões de Código

### Backend (PHP/Laravel)

#### PSR-12 e Laravel Pint
```bash
# Verificar estilo de código
./vendor/bin/sail pint --test

# Corrigir automaticamente
./vendor/bin/sail pint
```

#### Convenções de Nomenclatura
- **Classes**: PascalCase (`MovieListController`)
- **Métodos**: camelCase (`addMovieToList`)
- **Variáveis**: camelCase (`$movieList`)
- **Constantes**: UPPER_SNAKE_CASE (`TMDB_API_KEY`)

#### Estrutura de Controllers
```php
<?php

namespace App\Http\Controllers;

class MovieListController extends Controller
{
    public function index()
    {
        // Implementação
    }
    
    public function store(Request $request)
    {
        // Validação
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        
        // Lógica de negócio
        
        // Resposta
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
```

### Frontend (Vue.js/TypeScript)

#### ESLint e Prettier
```bash
# Verificar linting
./vendor/bin/sail npm run lint

# Corrigir automaticamente
./vendor/bin/sail npm run lint -- --fix

# Formatar código
./vendor/bin/sail npm run format
```

#### Convenções de Nomenclatura
- **Componentes**: PascalCase (`MovieListCard.vue`)
- **Props**: camelCase (`movieData`)
- **Events**: kebab-case (`movie-added`)
- **CSS Classes**: kebab-case (`movie-list-item`)

#### Estrutura de Componentes Vue
```vue
<template>
  <div class="movie-list-card">
    <!-- Template -->
  </div>
</template>

<script setup lang="ts">
// Imports
import { ref, computed } from 'vue';

// Props
interface Props {
  movieData: Movie;
  isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
});

// Emits
const emit = defineEmits<{
  movieAdded: [movie: Movie];
  movieRemoved: [movieId: number];
}>();

// Reactive data
const isVisible = ref(true);

// Computed
const formattedDate = computed(() => {
  return new Date(props.movieData.releaseDate).toLocaleDateString();
});

// Methods
const handleClick = () => {
  emit('movieAdded', props.movieData);
};
</script>

<style scoped>
.movie-list-card {
  /* Estilos com Tailwind CSS */
}
</style>
```

### Tailwind CSS
- Use classes utilitárias sempre que possível
- Crie componentes customizados para padrões repetidos
- Mantenha classes responsivas (`sm:`, `md:`, `lg:`)
- Use variáveis de tema consistentes

## 🔄 Processo de Contribuição

### 1. Preparação
```bash
# Sincronize com o upstream
git fetch upstream
git checkout main
git merge upstream/main

# Crie uma branch para sua feature
git checkout -b feature/nome-da-funcionalidade
# ou
git checkout -b fix/nome-do-bug
```

### 2. Desenvolvimento
```bash
# Faça suas alterações
# Teste localmente
./vendor/bin/sail test
./vendor/bin/sail npm run build

# Commit suas mudanças
git add .
git commit -m "feat: adiciona funcionalidade X"
```

### 3. Envio
```bash
# Push para seu fork
git push origin feature/nome-da-funcionalidade

# Abra um Pull Request no GitHub
```

## 🐛 Relatando Bugs

### Template de Issue para Bugs
```markdown
**Descrição do Bug**
Uma descrição clara e concisa do que é o bug.

**Como Reproduzir**
Passos para reproduzir o comportamento:
1. Vá para '...'
2. Clique em '....'
3. Role para baixo até '....'
4. Veja o erro

**Comportamento Esperado**
Uma descrição clara e concisa do que você esperava que acontecesse.

**Screenshots**
Se aplicável, adicione screenshots para ajudar a explicar seu problema.

**Ambiente:**
 - OS: [ex: iOS]
 - Browser [ex: chrome, safari]
 - Versão [ex: 22]

**Contexto Adicional**
Adicione qualquer outro contexto sobre o problema aqui.
```

### Informações Importantes
- Versão do PHP, Node.js, navegador
- Logs de erro (Laravel logs, browser console)
- Passos exatos para reproduzir
- Screenshots ou vídeos se possível

## 💡 Sugerindo Melhorias

### Template de Discussion para Features
```markdown
**O problema que sua feature resolveria**
Uma descrição clara e concisa do problema.

**Solução que você gostaria**
Uma descrição clara e concisa do que você quer que aconteça.

**Alternativas consideradas**
Uma descrição clara e concisa de qualquer solução alternativa ou feature que você considerou.

**Contexto adicional**
Adicione qualquer outro contexto ou screenshots sobre a solicitação de feature aqui.
```

## 📝 Pull Requests

### Checklist antes de enviar
- [ ] Código segue os padrões estabelecidos
- [ ] Testes passam (`./vendor/bin/sail test`)
- [ ] Build funciona (`./vendor/bin/sail npm run build`)
- [ ] Documentação atualizada se necessário
- [ ] Commits seguem convenção de mensagens
- [ ] Branch está atualizada com main

### Convenção de Commits
Usamos [Conventional Commits](https://www.conventionalcommits.org/):

```bash
# Tipos principais
feat: nova funcionalidade
fix: correção de bug
docs: mudanças na documentação
style: formatação, sem mudança de código
refactor: refatoração de código
test: adição ou correção de testes
chore: tarefas de manutenção

# Exemplos
feat: adiciona busca por gênero na API
fix: corrige erro de validação em listas
docs: atualiza README com instruções Docker
refactor: extrai lógica de filmes para service
test: adiciona testes para MovieListController
```

### Template de Pull Request
```markdown
## Descrição
Breve descrição das mudanças.

## Tipo de mudança
- [ ] Bug fix (mudança que corrige um problema)
- [ ] Nova feature (mudança que adiciona funcionalidade)
- [ ] Breaking change (mudança que quebra compatibilidade)
- [ ] Documentação

## Como testar
Descreva como testar suas mudanças:
1. ...
2. ...

## Checklist
- [ ] Meu código segue os padrões do projeto
- [ ] Realizei auto-review do meu código
- [ ] Comentei partes complexas do código
- [ ] Fiz mudanças correspondentes na documentação
- [ ] Minhas mudanças não geram novos warnings
- [ ] Adicionei testes que provam que minha correção é efetiva
- [ ] Testes novos e existentes passam localmente

## Screenshots (se aplicável)
```

## 🧪 Testes

### Executando Testes
```bash
# Todos os testes
./vendor/bin/sail test

# Testes específicos
./vendor/bin/sail test --filter=MovieListTest

# Com coverage
./vendor/bin/sail test --coverage

# Testes do frontend
./vendor/bin/sail npm run test
```

### Escrevendo Testes
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MovieList;

class MovieListTest extends TestCase
{
    public function test_user_can_create_movie_list(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/movie-lists', [
                'name' => 'Test List',
                'description' => 'Test description',
            ]);
        
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Test List',
                ]
            ]);
        
        $this->assertDatabaseHas('movie_lists', [
            'name' => 'Test List',
            'user_id' => $user->id,
        ]);
    }
}
```

## 📚 Recursos Úteis

### Documentação
- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Guide](https://vuejs.org/guide/)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [TMDB API](https://developers.themoviedb.org/3)

### Ferramentas
- [Laravel Pint](https://laravel.com/docs/pint) - PHP Code Style
- [ESLint](https://eslint.org/) - JavaScript Linting
- [Prettier](https://prettier.io/) - Code Formatting
- [Pest](https://pestphp.com/) - PHP Testing

## 🙋‍♀️ Precisa de Ajuda?

- 💬 [GitHub Discussions](https://github.com/seu-usuario/film-stack/discussions)
- 🐛 [Abrir Issue](https://github.com/seu-usuario/film-stack/issues/new)
- 📧 Email: dev@filmstack.com

## 🎉 Reconhecimento

Contribuidores são reconhecidos no README principal. Obrigado por fazer o FilmStack melhor! 🚀

---

<div align="center">

**📚 [Voltar ao README Principal](../README.md) | 🛣️ [Ver Documentação da API](./API.md)**

</div>
