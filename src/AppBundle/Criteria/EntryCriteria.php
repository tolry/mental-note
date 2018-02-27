<?php

declare(strict_types=1);

namespace AppBundle\Criteria;

final class EntryCriteria
{
    public const SORT_TIMESTAMP_DESC = 'sort-timestamp-desc';
    public const SORT_TIMESTAMP_ASC = 'sort-timestamp-asc';

    public $tag;
    public $query;
    public $category;
    public $limit = 12;
    public $page = 1;
    public $pendingOnly = true;
    public $sortOrder = self::SORT_TIMESTAMP_DESC;

    public function __construct(array $data)
    {
        foreach ($data as $member => $value) {
            if (property_exists($this, $member)) {
                $this->{$member} = $value;
            }
        }
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
