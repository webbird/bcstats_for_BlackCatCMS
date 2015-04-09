<?php

namespace GeoIp2\Model;































class Country extends AbstractModel
{
protected $continent;
protected $country;
protected $locales;
protected $maxmind;
protected $registeredCountry;
protected $representedCountry;
protected $traits;




public function __construct($raw, $locales = array('en'))
{
parent::__construct($raw);

$this->continent = new \GeoIp2\Record\Continent(
$this->get('continent'),
$locales
);
$this->country = new \GeoIp2\Record\Country(
$this->get('country'),
$locales
);
$this->maxmind = new \GeoIp2\Record\MaxMind($this->get('maxmind'));
$this->registeredCountry = new \GeoIp2\Record\Country(
$this->get('registered_country'),
$locales
);
$this->representedCountry = new \GeoIp2\Record\RepresentedCountry(
$this->get('represented_country'),
$locales
);
$this->traits = new \GeoIp2\Record\Traits($this->get('traits'));

$this->locales = $locales;
}
}
