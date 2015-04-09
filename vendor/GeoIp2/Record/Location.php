<?php

namespace GeoIp2\Record;






























class Location extends AbstractRecord
{



protected $validAttributes = array(
'accuracyRadius',
'latitude',
'longitude',
'metroCode',
'postalCode',
'postalConfidence',
'timeZone'
);
}
