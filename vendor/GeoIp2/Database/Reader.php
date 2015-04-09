<?php

namespace GeoIp2\Database;

use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\ProviderInterface;
use MaxMind\Db\Reader as DbReader;


























class Reader implements ProviderInterface
{
private $dbReader;
private $locales;










public function __construct(
$filename,
$locales = array('en')
) {
$this->dbReader = new DbReader($filename);
$this->locales = $locales;
}













public function city($ipAddress)
{
return $this->modelFor('City', 'City', $ipAddress);
}













public function country($ipAddress)
{
return $this->modelFor('Country', 'Country', $ipAddress);
}













public function anonymousIp($ipAddress)
{
return $this->flatModelFor(
'AnonymousIp',
'GeoIP2-Anonymous-IP',
$ipAddress
);
}













public function connectionType($ipAddress)
{
return $this->flatModelFor(
'ConnectionType',
'GeoIP2-Connection-Type',
$ipAddress
);
}













public function domain($ipAddress)
{
return $this->flatModelFor(
'Domain',
'GeoIP2-Domain',
$ipAddress
);
}













public function isp($ipAddress)
{
return $this->flatModelFor(
'Isp',
'GeoIP2-ISP',
$ipAddress
);
}

private function modelFor($class, $type, $ipAddress)
{
$record = $this->getRecord($class, $type, $ipAddress);

$record['traits']['ip_address'] = $ipAddress;
$class = "GeoIp2\\Model\\" . $class;

return new $class($record, $this->locales);
}

private function flatModelFor($class, $type, $ipAddress)
{
$record = $this->getRecord($class, $type, $ipAddress);

$record['ip_address'] = $ipAddress;
$class = "GeoIp2\\Model\\" . $class;

return new $class($record);
}

private function getRecord($class, $type, $ipAddress)
{
if (strpos($this->metadata()->databaseType, $type) === false) {
$method = lcfirst($class);
throw new \BadMethodCallException(
"The $method method cannot be used to open a "
. $this->metadata()->databaseType . " database"
);
}
$record = $this->dbReader->get($ipAddress);
if ($record === null) {
throw new AddressNotFoundException(
"The address $ipAddress is not in the database."
);
}
return $record;
}






public function metadata()
{
return $this->dbReader->metadata();
}




public function close()
{
$this->dbReader->close();
}
}
