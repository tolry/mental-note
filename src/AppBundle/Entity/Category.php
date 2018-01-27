<?php

declare(strict_types=1);

namespace AppBundle\Entity;

/**
 * @author Tobias Olry (tobias.olry@web.de)
 */
class Category
{
    public const READ = 'read';
    public const LOOK_AT = 'look_at';
    public const WATCH = 'watch';
    public const LISTEN = 'listen';
    public const EVALUATE = 'evaluate';
    public const VISIT_REGULARLY = 'visit_regularly';
    public const PURCHASE = 'purchase';

    private static $data = [
        self::READ => ['label' => 'read', 'icon' => 'fa fa-book', 'default_view' => '', 'description' => '', 'active' => true],
        self::LOOK_AT => ['label' => 'look at', 'icon' => 'fa fa-picture-o', 'default_view' => '', 'description' => '', 'active' => true],
        self::WATCH => ['label' => 'watch', 'icon' => 'fa fa-film', 'default_view' => '', 'description' => '', 'active' => true],
        self::LISTEN => ['label' => 'listen to', 'icon' => 'fa fa-volume-up', 'default_view' => '', 'description' => '', 'active' => true],
        self::EVALUATE => ['label' => 'evaluate', 'icon' => 'fa fa-question-circle', 'default_view' => '', 'description' => '', 'active' => true],
        self::VISIT_REGULARLY => ['label' => 'visit regularly', 'icon' => 'icon-star', 'default_view' => '', 'description' => '', 'active' => false],
        self::PURCHASE => ['label' => 'purchase', 'icon' => 'fa fa-shopping-cart', 'default_view' => '', 'description' => '', 'active' => true],
    ];

    private $key;
    private $label;
    private $icon;
    private $defaultView = 'list';
    private $description;

    public function __construct(string $key)
    {
        if (!isset(self::$data[$key])) {
            throw new \Exception('no category known by identifier ' . $key);
        }

        $row = self::$data[$key];

        $this->key = $key;
        $this->label = $row['label'];
        $this->icon = $row['icon'];
        $this->description = $row['description'];

        if (!empty($row['default_view'])) {
            $this->defaultView = $row['default_view'];
        }
    }

    public function __toString()
    {
        return $this->key;
    }

    public static function getChoiceArray(): array
    {
        $choices = [];
        foreach (self::$data as $key => $category) {
            if (!$category['active']) {
                continue;
            }

            $choices[$category['label']] = $key;
        }

        return $choices;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey($key): void
    {
        $this->key = $key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel($label): void
    {
        $this->label = $label;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon($icon): void
    {
        $this->icon = $icon;
    }

    public function getDefaultView(): ?string
    {
        return $this->defaultView;
    }

    public function setDefaultView($defaultView): void
    {
        $this->defaultView = $defaultView;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription($description): void
    {
        $this->description = $description;
    }
}
