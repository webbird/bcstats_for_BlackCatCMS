<?php

namespace GeoIp2\Record;























class Country extends AbstractPlaceRecord
{



protected $validAttributes = array(
'confidence',
'geonameId',
'isoCode',
'names'
);
}
