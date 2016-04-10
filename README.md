# php yml parser
Парсер больших yml-файлов, который кушает при любом объеме файла не более 15 мегабайт оперативной памяти

## Usage

### Создаем парсер

Понадобится неймспейс

    use \Sirian\YMLParser\Parser\Parser;

Создаем экземпляр парсера 

    $parser = new Parser();

#### Парсим товары

    $parser->addListener('offer', function (OfferEvent $event) use (&$offers) 
    {
        try {
        	$arr = $event->getOffer()->toArray();
        } catch(Exception $e) {}
    });
    
#### Парсим категории товаров

    $parser->addListener('categories', function (CategoriesEvent $event) use (&$categories)
    {
        $categories = $event->getCategories();
    });

#### Парсим параметры магизина и дату формирования прайс-листа

    $parser->addListener('shop', function (ShopEvent $event) use (&$shop)
    {
        $shop = $event->getShop()->toArray();
    });

#### Парсим валюты

    $parser->addListener('currencies', function (CurrenciesEvent $event) use (&$currencies) 
    {
      $currencies = $event->getCurrencies();
    });

#### Запускаем парсер
    try {
    	$parser->parse($file);
    } catch (FileNotFoundException $e)	{
    	echo $e->getMessage().PHP_EOL;
    } catch (YMLException $e) {
    	echo $e->getMessage().PHP_EOL;
    }
