<?php

namespace Olry\MentalNoteBundle\Criteria;

final class EntryCriteria
{
    const SORT_TIMESTAMP_DESC = 'sort-timestamp-desc';
    const SORT_TIMESTAMP_ASC = 'sort-timestamp-asc';
    const SORT_LAST_VISIT = 'sort-last-visit';

    const MODE_NO_REGULARLY = 'normal';
    const MODE_ONLY_REGULARLY = 'regularly';

    public $tag;
    public $query;
    public $category;
    public $limit = 10;
    public $page = 1;
    public $pendingOnly = true;
    public $sortOrder = self::SORT_TIMESTAMP_DESC;
    public $mode = self::MODE_NO_REGULARLY;

    private $sortOptions = [
        self::SORT_TIMESTAMP_DESC => 'newest first',
        self::SORT_TIMESTAMP_ASC => 'oldest first',
    ];

    public function __construct(array $data)
    {
        foreach ($data as $member => $value) {
            if (property_exists($this, $member)) {
                $this->$member = $value;
            }
        }
    }

    public static function createForVisitRegularly()
    {
        return new self([
            'mode' => self:: MODE_ONLY_REGULARLY,
            'sortOrder' => self::SORT_LAST_VISIT,
        ]);
    }

    /**
     * returns the array required as url query string
     *
     * @param array $changes
     * @return array
     *
     */
    public function getQuery(array $changes = [])
    {
        $query = [
            'category'    => $this->category,
            'tag'         => $this->tag,
            'page'        => $this->page,
            'limit'       => $this->limit,
            'query'       => $this->query,
            'pendingOnly' => $this->pendingOnly ? 1 : 0,
            'sortOrder'   => $this->sortOrder,
        ];

        return array_merge($query, $changes);
    }

    public function getSortOptions()
    {
        return $this->sortOptions;
    }
}
