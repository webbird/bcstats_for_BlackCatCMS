<?php

namespace GeoIp2\Model;



















class Isp extends AbstractModel
{
protected $autonomousSystemNumber;
protected $autonomousSystemOrganization;
protected $isp;
protected $organization;
protected $ipAddress;




public function __construct($raw)
{
parent::__construct($raw);
$this->autonomousSystemNumber = $this->get('autonomous_system_number');
$this->autonomousSystemOrganization =
$this->get('autonomous_system_organization');
$this->isp = $this->get('isp');
$this->organization = $this->get('organization');

$this->ipAddress = $this->get('ip_address');
}
}
