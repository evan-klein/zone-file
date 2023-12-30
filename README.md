# Zone File

A simple PHP class for generating DNS [zone files](https://en.wikipedia.org/wiki/Zone_file).

## Features

- Supports [A](/docs/ZoneFile.php.md#adda-method), [AAAA](/docs/ZoneFile.php.md#addaaaa-method), [CNAME](/docs/ZoneFile.php.md#addcname-method), [TXT](/docs/ZoneFile.php.md#addtxt-method), [MX](/docs/ZoneFile.php.md#addmx-method), and [NS](/docs/ZoneFile.php.md#addns-method) records
- Compatible with:
	- [AWS Route 53](https://aws.amazon.com/route53/)
	- [DNS Made Easy](https://dnsmadeeasy.com/)
- [Shell script to deploy to Route 53](/docs/push-to-route-53.sh.md)
- [RFC 1035](https://tools.ietf.org/html/rfc1035)/[RFC 1034](https://tools.ietf.org/html/rfc1034) compliant-*ish*

## Example

```php
<?php

require('ZoneFile.php');

$zone_file = new \evan_klein\zone_file\ZoneFile('example.com.', 180);

$zone_file->addA('www', '93.184.216.34', 120);
$zone_file->addAAAA('www', '2606:2800:220:1:248:1893:25c8:1946', 120);

echo $zone_file->output();

?>
```

The code above generates the output below:

```text
$ORIGIN example.com.
$TTL 180
;example.com.
www		120		IN		A		93.184.216.34
www		120		IN		AAAA		2606:2800:220:1:248:1893:25c8:1946
```

You can also chain commands like this:

```php
<?php

require('ZoneFile.php');

$zone_file = new \evan_klein\zone_file\ZoneFile('example.com.', 180);

echo $zone_file->addA('www', '93.184.216.34', 120)
	->addAAAA('www', '2606:2800:220:1:248:1893:25c8:1946', 120)
	->output();

?>
```

## Documentation

- [ZoneFile.php](/docs/ZoneFile.php.md)
	- [ZoneFile()](/docs/ZoneFile.php.md#zonefile-class)
	- [addA()](/docs/ZoneFile.php.md#adda-method)
	- [addAAAA()](/docs/ZoneFile.php.md#addaaaa-method)
	- [addCNAME()](/docs/ZoneFile.php.md#addcname-method)
	- [addTXT()](/docs/ZoneFile.php.md#addtxt-method)
	- [addMX()](/docs/ZoneFile.php.md#addmx-method)
	- [addNS()](/docs/ZoneFile.php.md#addns-method)
	- [output()](/docs/ZoneFile.php.md#output-method)
- [generate-zone-file.sh](/docs/generate-zone-file.sh.md)
- [push-to-route-53.sh](/docs/push-to-route-53.sh.md)