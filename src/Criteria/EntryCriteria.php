<?php

declare(strict_types=1);

namespace App\Criteria;

final class EntryCriteria
{
    public const SORT_TIMESTAMP_DESC = 'sort-timestamp-desc';
    public const SORT_TIMESTAMP_ASC = 'sort-timestamp-asc';


    public function __construct(
        public ?string $tag = null,
        public ?string $query = null,
        public ?string $category = null,
        public int $limit = 12,
        public int $page = 1,
        public bool $pendingOnly = true,
        public string $sortOrder = self::SORT_TIMESTAMP_DESC,
    )
    {
    }

    /**
     * returns the array required as url query string.
     */
    public function getQuery(array $changes = []): array
    {
        $query = [
            'category' => $this->category,
            'tag' => $this->tag,
            'page' => $this->page,
            'limit' => $this->limit,
            'query' => $this->query,
            'pendingOnly' => $this->pendingOnly ? 1 : 0,
            'sortOrder' => $this->sortOrder,
        ];

        return array_merge($query, $changes);
    }

    public function getSortOptions(): array
    {
        return [
            self::SORT_TIMESTAMP_DESC => 'newest first',
            self::SORT_TIMESTAMP_ASC => 'oldest first',
        ];
    }
}
