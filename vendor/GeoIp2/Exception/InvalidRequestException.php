<?php

namespace GeoIp2\Exception;





class InvalidRequestException extends HttpException
{



public $error;

public function __construct(
$message,
$error,
$httpStatus,
$uri,
\Exception $previous = null
) {
$this->error = $error;
parent::__construct($message, $httpStatus, $uri, $previous);
}
}
