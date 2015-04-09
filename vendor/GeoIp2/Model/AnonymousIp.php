<?php

namespace GeoIp2\Model;























class AnonymousIp extends AbstractModel
{
protected $isAnonymous;
protected $isAnonymousVpn;
protected $isHostingProvider;
protected $isPublicProxy;
protected $isTorExitNode;
protected $ipAddress;




public function __construct($raw)
{
parent::__construct($raw);

$this->isAnonymous = $this->get('is_anonymous');
$this->isAnonymousVpn = $this->get('is_anonymous_vpn');
$this->isHostingProvider = $this->get('is_hosting_provider');
$this->isPublicProxy = $this->get('is_public_proxy');
$this->isTorExitNode = $this->get('is_tor_exit_node');
$this->ipAddress = $this->get('ip_address');
}
}
