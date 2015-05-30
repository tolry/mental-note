<?php

namespace Olry\MentalNoteBundle\Criteria;


class EntryCriteria
{

    public $tag;
    public $query;
    public $category;
    public $limit       = 5;
    public $page        = 1;
    public $pendingOnly = true;

    public function __construct(array $data)
    {
        foreach ($data as $member=>$value) {
            if (property_exists($this, $member)) {
                $this->$member = $value;
            }
        }
    }

    /**
     * returns the array required as url query string
     *
     * @param array $changes
     * @return array
     *
     */
    public function getQuery(array $changes = array())
    {
        $query = array(
            'category'    => $this->category,
            'tag'         => $this->tag,
            'page'        => $this->page,
            'limit'       => $this->limit,
            'query'       => $this->query,
            'pendingOnly' => $this->pendingOnly ? 1 : 0
        );

        return array_merge($query, $changes);
    }
}
