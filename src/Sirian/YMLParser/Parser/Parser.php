<?php

namespace Sirian\YMLParser\Parser;

use Sirian\YMLParser\Exception\UnsupportedOfferTypeException;
use Sirian\YMLParser\Factory\Factory;
use Sirian\YMLParser\Shop;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Parser extends EventDispatcher
{
    protected $xmlReader;
    protected $factory;
    protected $file = '';
    protected $encoding = '';

    private $path = [];

    public function __construct(Factory $factory = null)
    {
        if (null == $factory) {
            $factory = new Factory();
        }

        $this->xmlReader = new \XMLReader();
        $this->factory = $factory;
    }

    public function parse($file)
    {
    	$this->file = $file;
        $this->path = [];

        $this->xmlReader->open($file);
        $this->read();
        $this->xmlReader->close();
    }
    
    public function strBetween($string = '', $start, $end)
    {
	$string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        $result = substr($string, $ini, $len);
        return $result;
    }
    
    protected function getEncoding()
    {
    	$forRead 	  = 500;
    	$handle       = fopen($this->file, "r");
	    $fileContents = fread($handle, $forRead);
	    fclose($handle);

        $fileContents   = $this->strBetween($fileContents, "<?xml","?>");
        $this->encoding = $this->strBetween($fileContents, 'encoding="','"');
        return $this->encoding;
    }

    protected function read()
    {
    	$this->getEncoding();
        $shop = null;
        $xml = $this->xmlReader;
        while ($xml->read()) {
            if ($xml->nodeType == \XMLReader::END_ELEMENT) {
                array_pop($this->path);
                continue;
            }


            if ($xml->nodeType == \XMLReader::ELEMENT) {
                array_push($this->path, $xml->name);
                $path = implode('/', $this->path);

                if ($xml->isEmptyElement) {
                    array_pop($this->path);
                }


                switch ($path) {
                    case 'yml_catalog/shop':
                        $shop = $this->factory->createShop();
                        $shop = $this->parseShop($shop);
                        $this->dispatch('shop', new ShopEvent($shop));
                        break;
                    case 'yml_catalog/shop/currencies':
                        $currencies = $this->parseCurrencies($shop);
                        $this->dispatch('currencies', new CurrenciesEvent($currencies));
                        break;
                    case 'yml_catalog/shop/categories':
                        $categories = $this->parseCategories($shop);
                        $this->dispatch('categories', new CategoriesEvent($categories));
                        break;
                    case 'yml_catalog/shop/offers/offer':
                        try {
                            $offer = $this->parseOffer($shop);
                        } catch (UnsupportedOfferTypeException $e) {
                            break;
                        }

                        $this->dispatch('offer', new OfferEvent($offer));
                        break;
                    default:
                }
            }
        }
    }

    protected function parseCurrencies(Shop $shop)
    {
        $xml = $this->loadElementXml();
        foreach ($xml->currency as $elem) {
            $shop->addCurrency($this->createCurrency($elem, $shop));
        }
        return $shop->getCurrencies();
    }

    protected function parseCategories(Shop $shop)
    {
        $xml = $this->loadElementXml();

        $parents = [];
        foreach ($xml->category as $elem) {
            $shop->addCategory($this->createCategory($elem, $shop));

            foreach (['parentId', 'parent_id'] as $field) {
                if (isset($elem[$field])) {
                    $parents[(string)$elem['id']] = (string)$elem[$field];
                    break;
                }
            }
        }

        foreach ($parents as $id => $parentId) {
            if ($id != $parentId) {
                $parent = $shop->getCategory($parentId);
            } else {
                $parent = null;
            }
            $shop
                ->getCategory($id)
                ->setParent($parent)
            ;
        }
        return $shop->getCategories();
    }

    protected function parseOffer(Shop $shop)
    {
        $xml = $this->loadElementXml();

        $offer = $this->createOffer($xml, $shop);

        return $offer;
    }

    protected function createCurrency(\SimpleXMLElement $elem, Shop $shop)
    {
        $id = $this->fixCurrency((string)$elem['id']);

        $currency = $this->factory->createCurrency();
        $currency
            ->setId($id)
            ->setRate((string)$elem['rate'])
            ->setPlus((int)$elem['plus'])
        ;

        return $currency;
    }

    protected function createCategory(\SimpleXMLElement $elem, Shop $shop)
    {
        $id = (string)$elem['id'];

        $parents[$id] = (string)$elem['parentId'];

        $category = $this->factory->createCategory();

        $category
            ->setId($id)
            ->setName((string)$elem)
        ;

        return $category;
    }

    protected function createOffer(\SimpleXMLElement $elem, Shop $shop)
    {
        $type = (string)$elem['type'];

        if (!$type) {
            $type = 'vendor.model';
        }

        $offer = $this->factory->createOffer($type);
        foreach ($elem->attributes() as $key => $value) {
            $offer->setAttribute($key, (string)$value);
        }

        $offer
            ->setId((string)$elem['id'])
            ->setAvailable(((string)$elem['available']) == 'true' ? true : false)
            ->setType($type)
            ->setXml($elem)
        ;

        foreach ($elem as $field => $value) {
            foreach (['add', 'set'] as $method) {
                $method .= $this->camelize($field);
                if (method_exists($offer, $method)) {
                    call_user_func([$offer, $method], count($value->children()) ? $value : (string)$value);
                    break;
                }
            }
        }

        $currencyId = $this->fixCurrency((string)$elem->currencyId);

        if ($shop->getCurrency($currencyId)) {
            $offer->setCurrency($shop->getCurrency($currencyId));
        }

        $categoryId = (string)$elem->categoryId;

        if ($shop->getCategory($categoryId)) {
            $offer->setCategory($shop->getCategory($categoryId));
        }
        return $offer;
    }
    
    protected function parseShop(Shop $shop)
    {
    	$forRead 	  = 9000;
    	$handle       = fopen($this->file, "r");
	    $fileContents = fread($handle, $forRead);
	    fclose($handle);

	    $posShop = strpos($fileContents,"<shop>");
	    $posCurr = strpos($fileContents,"<currencies>");
	    $posCats = strpos($fileContents,"<categories>");

	    $posCurr = ($posCurr===false) ? 999999 : $posCurr;
	    $posCats = ($posCats===false) ? 999999 : $posCats;

	    $posStart= $posShop;
	    $posStop = ($posCats<$posCurr) ? $posCats : $posCurr;

	    $xml = substr($fileContents, $posStart, $posStop-$posStart);
        if(strtolower($this->encoding) != 'utf-8') $xml = iconv($this->encoding, 'utf-8', $xml);
	    $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>' . $xml . '</shop>');

	    $shop->setName((string)$xml->name);
	    $shop->setUrl((string)$xml->url);
	    $shop->setPlatform((string)$xml->platform);
	    $shop->setAgency((string)$xml->agency);
	    $shop->setVersion((string)$xml->version);
	    $shop->setEmail((string)$xml->email);

	    return $shop;
    }

    protected function loadElementXml()
    {
        $xml = $this->xmlReader->readOuterXml();

        return simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>' . $xml);
    }

    private function camelize($field)
    {
        return strtr(ucwords(strtr($field, array('_' => ' ', '.' => '_ '))), array(' ' => ''));
    }

    private function fixCurrency($id)
    {
        $id = strtoupper($id);
        if ('RUR' === $id) {
            $id = 'RUB';
        }
        return $id;
    }
}
