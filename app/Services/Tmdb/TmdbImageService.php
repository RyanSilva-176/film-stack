<?php

namespace App\Services\Tmdb;

use App\Services\Tmdb\Contracts\TmdbImageServiceInterface;

class TmdbImageService implements TmdbImageServiceInterface
{
    /**
     ** Gera URL completa para imagem do TMDB
     * @param string|null $imagePath
     * @param string $type
     * @param string|null $size
     * @return string|null
     */
    public function getImageUrl(?string $imagePath, string $type = 'poster', ?string $size = null): ?string
    {
        if (!$imagePath) {
            return null;
        }

        $baseUrl = config('services.tmdb.image_base_url');
        $defaultSize = $size ?? config("services.tmdb.default_sizes.{$type}", 'w500');

        return $baseUrl . $defaultSize . $imagePath;
    }

    /**
     ** Gera URL do poster do filme
     * @param string|null $posterPath
     * @param string $size
     * @return string|null
     */
    public function getPosterUrl(?string $posterPath, string $size = 'w500'): ?string
    {
        return $this->getImageUrl($posterPath, 'poster', $size);
    }

    /**
     ** Gera URL do backdrop do filme
     * @param string|null $backdropPath
     * @param string $size
     * @return string|null
     */
    public function getBackdropUrl(?string $backdropPath, string $size = 'w1280'): ?string
    {
        return $this->getImageUrl($backdropPath, 'backdrop', $size);
    }

    /**
     ** Gera URL do logo
     * @param string|null $logoPath
     * @param string $size
     * @return string|null
     */
    public function getLogoUrl(?string $logoPath, string $size = 'w185'): ?string
    {
        return $this->getImageUrl($logoPath, 'logo', $size);
    }

    /**
     ** Gera URL do perfil (para atores/diretores)
     * @param string|null $profilePath
     * @param string $size
     * @return string|null
     */
    public function getProfileUrl(?string $profilePath, string $size = 'w185'): ?string
    {
        return $this->getImageUrl($profilePath, 'profile', $size);
    }

    /**
     ** Gera múltiplas URLs de imagem em diferentes tamanhos
     * @param string|null $imagePath
     * @param string $type
     * @param array $sizes
     * @return array
     */
    public function getImageUrls(?string $imagePath, string $type = 'poster', array $sizes = []): array
    {
        if (!$imagePath) {
            return [];
        }

        if (empty($sizes)) {
            $sizes = config("services.tmdb.image_sizes.{$type}", ['w500']);
        }

        $urls = [];
        foreach ($sizes as $size) {
            $urls[$size] = $this->getImageUrl($imagePath, $type, $size);
        }

        return $urls;
    }

    /**
     ** Gera URLs responsivas para posters
     * @param string|null $posterPath
     * @return array
     */
    public function getResponsivePosterUrls(?string $posterPath): array
    {
        return $this->getImageUrls($posterPath, 'poster', ['w185', 'w342', 'w500', 'w780']);
    }

    /**
     ** Gera URLs responsivas para backdrops
     * @param string|null $backdropPath
     * @return array
     */
    public function getResponsiveBackdropUrls(?string $backdropPath): array
    {
        return $this->getImageUrls($backdropPath, 'backdrop', ['w300', 'w780', 'w1280']);
    }

    /**
     ** Verifica se o caminho da imagem é válido
     * @param string|null $imagePath
     * @return bool
     */
    public function isValidImagePath(?string $imagePath): bool
    {
        return !empty($imagePath) && str_starts_with($imagePath, '/');
    }

    /**
     ** Gera URL de fallback para quando a imagem não existe
     * @param string $type
     * @return string
     */
    public function getFallbackImageUrl(string $type = 'poster'): string
    {
        $fallbacks = [
            'poster' => '/images/no-poster.png',
            'backdrop' => '/images/no-backdrop.png',
            'profile' => '/images/no-profile.png',
            'logo' => '/images/no-logo.png',
        ];

        return asset($fallbacks[$type] ?? $fallbacks['poster']);
    }

    /**
     ** Gera URL da imagem com fallback automático
     * @param string|null $imagePath
     * @param string $type
     */
    public function getImageUrlWithFallback(?string $imagePath, string $type = 'poster', ?string $size = null): string
    {
        $imageUrl = $this->getImageUrl($imagePath, $type, $size);

        return $imageUrl ?? $this->getFallbackImageUrl($type);
    }
}
