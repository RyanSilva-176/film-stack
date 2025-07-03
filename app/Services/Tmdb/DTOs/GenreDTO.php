<?php

namespace App\Services\Tmdb\DTOs;

use JsonSerializable;

class GenreDTO implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
