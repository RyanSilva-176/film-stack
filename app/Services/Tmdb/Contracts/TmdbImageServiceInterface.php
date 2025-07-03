<?php

namespace App\Services\Tmdb\Contracts;

interface TmdbImageServiceInterface
{
    /**
     * Gera URL completa para imagem do TMDB
     */
    public function getImageUrl(?string $imagePath, string $type = 'poster', ?string $size = null): ?string;

    /**
     * Gera URL do poster do filme
     */
    public function getPosterUrl(?string $posterPath, string $size = 'w500'): ?string;

    /**
     * Gera URL do backdrop do filme
     */
    public function getBackdropUrl(?string $backdropPath, string $size = 'w1280'): ?string;

    /**
     * Gera URL do logo
     */
    public function getLogoUrl(?string $logoPath, string $size = 'w185'): ?string;

    /**
     * Gera URL do perfil (para atores/diretores)
     */
    public function getProfileUrl(?string $profilePath, string $size = 'w185'): ?string;

    /**
     * Gera múltiplas URLs de imagem em diferentes tamanhos
     */
    public function getImageUrls(?string $imagePath, string $type = 'poster', array $sizes = []): array;
}
