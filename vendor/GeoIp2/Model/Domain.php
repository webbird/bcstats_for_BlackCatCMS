<?php

namespace GeoIp2\Model;












class Domain extends AbstractModel
{
protected $domain;
protected $ipAddress;




public function __construct($raw)
{
parent::__construct($raw);

$this->domain = $this->get('domain');
$this->ipAddress = $this->get('ip_address');
}
}
