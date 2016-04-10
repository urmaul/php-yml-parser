<?php

namespace Sirian\YMLParser\Offer;

use Sirian\YMLParser\Category;
use Sirian\YMLParser\Currency;
use Sirian\YMLParser\Shop;
use Sirian\YMLParser\Exception\CurrencyNotFoundException;
use Sirian\YMLParser\Exception\CategoryNotFoundException;

class Offer
{
    /**
     * @var integer|string
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    protected $name = '';
    protected $description = '';

    /**
     * Доступность товара
     * «false» — товарное предложение на заказ. Магазин готов принять заказ и осуществить поставку товара в течение согласованного с покупателем срока, не превышающего двух месяцев (за исключением товаров, изготавливаемых на заказ, ориентировочный срок поставки которых оговаривается с покупателем во время заказа).
     * «true» — товарное предложение в наличии. Магазин готов сразу договариваться с покупателем о доставке/покупке товара.
     *
     * @var bool
     */
    protected $available = true;

    protected $url = '';

    protected $price = 0;
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var Shop
     */
    protected $shop;

    protected $typePrefix;

    protected $pictures = [];
    
    protected $params = [];

    protected $attributes = [];

    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = (string)$type;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = (string)$name;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = (string)$description;
        return $this;
    }

    public function isAvailable()
    {
        return $this->available;
    }

    public function setAvailable($available)
    {
        $this->available = (bool)$available;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = (string)$url;
        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = (float)$price;
        return $this;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    public function getShop()
    {
        return $this->shop;
    }

    public function setShop($shop)
    {
        $this->shop = $shop;
        return $this;
    }

    public function getPictures()
    {
        return $this->pictures;
    }

    public function setPictures($pictures)
    {
        $this->pictures = [];
        if (is_array($pictures) || $pictures instanceof \Traversable) {
            foreach ($pictures as $picture) {
                $this->addPicture($picture);
            }
        }

        return $this;
    }

    public function addPicture($picture)
    {
        $this->pictures[] = (string)$picture;
        return $this;
    }

    public function addParam($param)
    {
        $key = (string)$param->attributes()['name'];
        $value = (string)$param;
        $this->params[$key] = (string)$param;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getTypePrefix()
    {
        return $this->typePrefix;
    }

    public function setTypePrefix($typePrefix)
    {
        $this->typePrefix = $typePrefix;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function setAttribute($key, $param)
    {
        $this->attributes[$key] = $param;
        return $this;
    }

    public function hasAttribute($key)
    {
        return isset($this->attributes[$key]);
    }

    public function getAttribute($key, $defaultValue = null)
    {
        return $this->hasAttribute($key) ? $this->attributes[$key] : $defaultValue;
    }

    public function getXml()
    {
        return $this->xml;
    }

    public function setXml($xml)
    {
        $this->xml = $xml;

        return $this;
    }

    public function toArray()
    {
        $arr = [];
        $arr['id']              = (int) $this->getId();
        $arr['type']            = $this->getType();
        $arr['name']            = $this->getName();
        $arr['description']     = $this->getDescription();
        $arr['isAvailable']     = (bool)  $this->isAvailable();
        $arr['pictures']        = $this->getPictures();
        $arr['typePrefix']      = $this->getTypePrefix();
        $arr['model']           = $this->getModel();
        $arr['price']           = (float) $this->getPrice();
        $arr['currency']        = [];
        $arr['params']          = $this->getParams();
        // $arr['attributes']      = $this->getAttributes();

        if($this->getCurrency() instanceof \Sirian\YMLParser\Currency) {
            $arr['currency']['id'] = $this->getCurrency()->getId();
            $arr['currency']['rate'] = $this->getCurrency()->getRate();
        } else {
            throw new CurrencyNotFoundException("Currency not found in offer ".$arr['id']);
        }

        if($this->getCategory() instanceof \Sirian\YMLParser\Category) {
            $arr['category'] = $this->getCategory()->getId();
        } else {
            throw new CategoryNotFoundException("Category not found in offer ".$arr['id']);
        }
        return $arr;
    }
}
