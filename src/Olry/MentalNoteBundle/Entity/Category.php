<?php

namespace Olry\MentalNoteBundle\Entity;


/**
 * @author Tobias Olry (tobias.olry@web.de)
 */
class Category
{

    const READ            = 'read';
    const LOOK_AT         = 'look_at';
    const WATCH           = 'watch';
    const LISTEN          = 'listen';
    const EVALUATE        = 'evaluate';
    const VISIT_REGULARLY = 'visit_regularly';

    private static $data = array(
        self::READ            => array('label'=>'read', 'icon'=>'icon-book', 'default_view'=>'', 'description'=>''),
        self::LOOK_AT         => array('label'=>'look at', 'icon'=>'icon-picture', 'default_view'=>'', 'description'=>''),
        self::WATCH           => array('label'=>'watch', 'icon'=>'icon-film', 'default_view'=>'', 'description'=>''),
        self::LISTEN          => array('label'=>'listen to', 'icon'=>'icon-volume-up', 'default_view'=>'', 'description'=>''),
        self::EVALUATE        => array('label'=>'evaluate', 'icon'=>'icon-question-sign', 'default_view'=>'', 'description'=>''),
        self::VISIT_REGULARLY => array('label'=>'visit regularly', 'icon'=>'icon-star', 'default_view'=>'', 'description'=>''),
    );

    private $key;
    private $label;
    private $icon;
    private $defaultView = 'list';
    private $description;

    public function __construct($key)
    {
        if (!isset(self::$data[$key])){
            throw new \Exception('no category known by identifier ' . $key);
        }

        $row = self::$data[$key];

        $this->key         = $key;
        $this->label       = $row['label'];
        $this->icon        = $row['icon'];
        $this->description = $row['description'];

        if (!empty($row['default_view'])) {
            $this->defaultView = $row['default_view'];
        }
    }

    public function __toString()
    {
        return $this->key;
    }

    public static function getChoiceArray()
    {
        $choices = array();
        foreach (self::$data as $key=>$category) {
            $choices[$key] = $category['label'];
        }
        return $choices;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey( $key )
    {
        $this->key = $key;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel( $label )
    {
        $this->label = $label;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon( $icon )
    {
        $this->icon = $icon;
    }

    public function getDefaultView()
    {
        return $this->defaultView;
    }

    public function setDefaultView( $defaultView )
    {
        $this->defaultView = $defaultView;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription( $description )
    {
        $this->description = $description;
    }


}
