<?php

namespace GeoIp2\Model;


















































class City extends Country
{



protected $city;



protected $location;



protected $postal;



protected $subdivisions = array();




public function __construct($raw, $locales = array('en'))
{
parent::__construct($raw, $locales);

$this->city = new \GeoIp2\Record\City($this->get('city'), $locales);
$this->location = new \GeoIp2\Record\Location($this->get('location'));
$this->postal = new \GeoIp2\Record\Postal($this->get('postal'));

$this->createSubdivisions($raw, $locales);
}

private function createSubdivisions($raw, $locales)
{
if (!isset($raw['subdivisions'])) {
return;
}

foreach ($raw['subdivisions'] as $sub) {
array_push(
$this->subdivisions,
new \GeoIp2\Record\Subdivision($sub, $locales)
);
}
}




public function __get($attr)
{
if ($attr == 'mostSpecificSubdivision') {
return $this->$attr();
} else {
return parent::__get($attr);
}
}

private function mostSpecificSubdivision()
{
return empty($this->subdivisions) ?
new \GeoIp2\Record\Subdivision(array(), $this->locales) :
end($this->subdivisions);
}
}
