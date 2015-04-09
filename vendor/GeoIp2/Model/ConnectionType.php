<?php

namespace GeoIp2\Model;












class ConnectionType extends AbstractModel
{
protected $connectionType;
protected $ipAddress;




public function __construct($raw)
{
parent::__construct($raw);

$this->connectionType = $this->get('connection_type');
$this->ipAddress = $this->get('ip_address');
}
}
