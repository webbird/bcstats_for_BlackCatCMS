<?php

namespace GeoIp2\Exception;





class HttpException extends GeoIp2Exception
{



public $uri;

public function __construct(
$message,
$httpStatus,
$uri,
\Exception $previous = null
) {
$this->uri = $uri;
parent::__construct($message, $httpStatus, $previous);
}
}
