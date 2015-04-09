<?php

namespace GeoIp2\Record;



























class RepresentedCountry extends Country
{
protected $validAttributes = array(
'confidence',
'geonameId',
'isoCode',
'names',
'type'
);
}
