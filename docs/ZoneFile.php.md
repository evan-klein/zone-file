# ZoneFile.php

## Requirements

- [php-cli](https://www.php.net/manual/en/features.commandline.php) (v7.0 or newer)

## ZoneFile() class

This creates a new instance of the ZoneFile object.

#### Parameters

- `domain` - the domain the zone file is being generated for. This must be a fully qualified domain name that ends with a period (i.e. `example.com.`)
- `ttl` (optional) - the time to live (TTL), in seconds, that will be used for records where a TTL is not specified. The default value is `60`

#### Example

```php
<?php

require('ZoneFile.php');

$zone_file = new \evan_klein\zone_file\ZoneFile('example.com.', 240);

?>
```

## addA() method

This method adds an A record to the zone.

#### Parameters

- `name` - the host name. This can be a relative host name (i.e. `www`) or a fully qualified domain name that ends with a period (i.e. `www.example.com.`)
- `ipv4_addr` - the IPv4 address
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used

#### Example

```php
<?php

require('ZoneFile.php');

$zone_file = new \evan_klein\zone_file\ZoneFile('example.com.', 240);
$zone_file->addA('example.com.', '93.184.216.34', 120);
$zone_file->addA('www', '93.184.216.34', 180);
$zone_file->addA('www1.example.com.', '93.184.216.34');

?>
```

## addAAAA() method

This method adds an AAAA record to the zone.

#### Parameters

- `name` - the host name. This can be a relative host name (i.e. `www`) or a fully qualified domain name that ends with a period (i.e. `www.example.com.`)
- `ipv6_addr` - the IPv6 address
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used

#### Example

```php
<?php

require('ZoneFile.php');

$zone_file = new \evan_klein\zone_file\ZoneFile('example.com.', 240);
$zone_file->addAAAA('example.com.', '2606:2800:220:1:248:1893:25c8:1946', 120);
$zone_file->addAAAA('www', '2606:2800:220:1:248:1893:25c8:1946', 180);
$zone_file->addAAAA('www1.example.com.', '2606:2800:220:1:248:1893:25c8:1946');

?>
```

## addCNAME() method

This method adds a CNAME record to the zone.

#### Parameters

- `name` - the host name. This can be a relative host name (i.e. `www`) or a fully qualified domain name that ends with a period (i.e. `www.example.com.`)
- `cname` - the host name. This can be a relative host name (i.e. `www`) or a fully qualified domain name that ends with a period (i.e. `www.example.com.`)
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used

#### Example

```php
<?php

require('ZoneFile.php');

$zone_file = new \evan_klein\zone_file\ZoneFile('example.com.', 240);
$zone_file->addCNAME('www', 'www1', 180);
$zone_file->addCNAME('www2.example.com.', 'www3');
$zone_file->addCNAME('www4', 'www5.example.com.');

?>
```

## addTXT() method

This method adds a TXT record to the zone.

#### Parameters

- `name` - the host name. This can be a relative host name (i.e. `www`) or a fully qualified domain name that ends with a period (i.e. `www.example.com.`)
- `data` - the data
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used

#### Example

```php
<?php

require('ZoneFile.php');

$zone_file = new \evan_klein\zone_file\ZoneFile('example.com.', 240);
$zone_file->addTXT('example.com.', 'key=value', 120);
$zone_file->addTXT('www', 'key=value', 180);
$zone_file->addTXT('www1.example.com.', 'key=value');

?>
```

## addMX() method

This method adds a MX record to the zone.

#### Parameters

- `name` - the host name. This can be a relative host name (i.e. `mail`) or a fully qualified domain name that ends with a period (i.e. `example.com.`)
- `pri` - the MX record's priority
- `server` - the host name of the mail server. This can be a relative host name (i.e. `mail`) or a fully qualified domain name that ends with a period (i.e. `mail.example.com.`)
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used

#### Example

```php
<?php

require('ZoneFile.php');

$zone_file = new \evan_klein\zone_file\ZoneFile('example.com.', 240);
$zone_file->addMX('example.com.', 10, 'mail', 120);
$zone_file->addMX('example.com.', 10, 'mail1.example.com.');
$zone_file->addMX('example.com.', 20, 'mail2.example.com.');

?>
```

## addNS() method

This method adds a NS record to the zone.

#### Parameters

- `ns` - the host name of the name server. This must be a fully qualified domain name that ends with a period (i.e. `ns.nameserver.com.`)
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used

#### Example

```php
<?php

require('ZoneFile.php');

$zone_file = new \evan_klein\zone_file\ZoneFile('example.com.', 240);
$zone_file->addNS('example.com.', 'ns.nameserver.com.', 120);

?>
```

## output() method

Outputs the zone file.

#### Example

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