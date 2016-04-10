<?php

namespace Sirian\YMLParser;

class Category
{
    /**
     * Идентификатор категории
     *
     * @var integer
     */
    protected $id;

    /**
     * @var Category
     */
    protected $parent;

    /**
     * Название категории
     *
     * @var string
     */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Category $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function hasParent()
    {
        return null !== $this->parent;
    }

    public function toArray()
    {
        $arr = [];
        $arr['id'] = $this->getId();
        $arr['name'] = $this->getName();
        $arr['parent'] = ($this->getParent() instanceof Category) ? $this->getParent()->getId() : 0;
        return $arr;
    }
}
