<?php

class ZoneFile {
	private $domain = 'example.com.';
	private $ttl = 60; // The default TTL, used when one is not specified for a record
	private $records = [];
	private $strlen_maxes = [
		'name' => 0,
		'ttl' => 0,
		'class' => 0,
		'type' => 0
	];
	private $spf = [
		'params' => NULL,
		'_includes' => []
	];


	public function __construct($domain=NULL, $ttl=NULL){
		// $this->domain
		if( !is_null($domain) ){
			// Throw an exception if it's not a string
			if( !is_string($domain) ){
				throw new Exception('"domain" must be a string');
			}

			// Trim whitespace
			$domain = trim($domain);

			// Throw an exception if it's blank
			if( strlen($domain)==0 ){
				throw new Exception('"domain" cannot be blank');
			}

			// Throw an exception if it doesn't end with a period
			if( substr($domain, -1)!=='.' ){
				throw new Exception('"domain" must end with a period');
			}

			$this->domain = $domain;
		}

		// $this->ttl
		if( !is_null($ttl) ){
			// Throw an exception if it's not an int
			if( !is_int($ttl) ) throw new Exception('"ttl" must be an int');

			$this->ttl = $ttl;
		}

		return $this;
	}


	private function addRecord($name, $ttl, $class, $type, $data){
		$ttl = $ttl ?? $this->ttl;

		$this->records[]=[
			'name' => $name,
			'ttl' => $ttl,
			'class' => $class,
			'type' => $type,
			'data' => $data
		];
	}


	// This function searches the zone file for the record type(s) specified and returns true if at least one record with that/those type(s) exists, or false otherwise. $types can be an array with one or more types, or a string with a single type
	private function hasRecordType($types){
		// If $types is not an array, convert it to one
		if( !is_array($types) ) $types = [$types];

		// Prevents case mismatch issues
		$types = array_map(
			function($type){ return strtoupper($type); },
			$types
		);

		// Search the records in the zone file for the type(s) specified
		foreach($this->records as $record){
			// If a match is found, return true
			if( in_array($record['type'], $types) ) return true;
		}

		// Otherwise, return false
		return false;
	}


	private function calculateStrlenMaxes(){
		foreach($this->records as $record){
			foreach(['name', 'ttl', 'class', 'type'] as $key){
				$len = strlen($record[$key]);

				if( $len>$this->strlen_maxes[$key] ){
					$this->strlen_maxes[$key] = $len;
				}
			}
		}
	}


	private function pad($input, $max_len){
		$pad = 1; // The number of extra tabs padding
		$chars_per_tab = 8;

		$max_chars = (
			ceil($max_len/$chars_per_tab) + $pad
		) * $chars_per_tab;

		$tabs_needed = ceil(
			(
				$max_chars-strlen($input)
			)/$chars_per_tab
		);

		return $input . str_repeat("\t", $tabs_needed);
	}


	// Add an A record
	public function addA($name, $ipv4_addr, $ttl=NULL){
		$this->addRecord($name, $ttl, 'IN', 'A', $ipv4_addr);
		return $this;
	}


	// Add an AAAA record
	public function addAAAA($name, $ipv6_addr, $ttl=NULL){
		$this->addRecord($name, $ttl, 'IN', 'AAAA', $ipv6_addr);
		return $this;
	}


	// Add an A and AAAA record
	public function addAAAAA($name, $ipv4_addr, $ipv6_addr, $ttl=NULL){
		$this->addA($name, $ipv4_addr, $ttl);
		$this->addAAAA($name, $ipv6_addr, $ttl);
		return $this;
	}


	// Add a CNAME record
	public function addCNAME($name, $cname, $ttl=NULL){
		$this->addRecord($name, $ttl, 'IN', 'CNAME', $cname);
		return $this;
	}


	// Add a TXT record
	public function addTXT($name, $data, $ttl=NULL){
		$this->addRecord($name, $ttl, 'IN', 'TXT', "\"$data\"");
		return $this;
	}


	// Add a SRV record
	// $target must end in a period
	public function addSRV($service, $protocol, $pri, $weight, $port, $target='.', $ttl=NULL){
		// Validate $target
		if( substr($target, -1)!=='.' ){
			throw new Exception('"target" must end in a period');
		}

		$this->addRecord(
			"_$service._$protocol.{$this->domain}",
			$ttl,
			'IN',
			'SRV',
			"$pri $weight $port $target"
		);

		return $this;
	}


	// Add a MX record
	public function addMX($name, $pri, $server, $ttl=NULL){
		$this->addRecord($name, $ttl, 'IN', 'MX', "$pri $server");
		return $this;
	}


	// Add a NS record
	public function addNS($ns, $ttl=NULL){
		$this->addRecord($this->domain, $ttl, 'IN', 'NS', $ns);
		return $this;
	}


	public function addWWW($a=[], $aaaa=[], $subdomains=['www'], $ttl=NULL){
		$a = (array) $a;
		$aaaa = (array) $aaaa;
		$subdomains = (array) $subdomains;

		foreach($a as $ipv4_addr){
			$this->addA($this->domain, $ipv4_addr, $ttl);
			foreach($subdomains as $subdomain) $this->addA($subdomain, $ipv4_addr, $ttl);
		}
		foreach($aaaa as $ipv6_addr){
			$this->addAAAA($this->domain, $ipv6_addr, $ttl);
			foreach($subdomains as $subdomain) $this->addAAAA($subdomain, $ipv6_addr, $ttl);
		}

		return $this;
	}


	public function addFastmail($ttl=NULL){
		// MX records
		$this->addMX($this->domain, 10, 'in1-smtp.messagingengine.com.', $ttl);
		$this->addMX($this->domain, 20, 'in2-smtp.messagingengine.com.', $ttl);

		// DKIM records
		foreach([1, 2, 3] as $i){
			$this->addCNAME("fm$i._domainkey", "fm$i.{$this->domain}dkim.fmhosted.com.", $ttl);
		}

		// SPF include
		$this->spf['_includes'][]='spf.messagingengine.com';

		// A records
		$this->addA('mail', '66.111.4.147');
		$this->addA('mail', '66.111.4.148');

		// SRV records
		$this->addSRV('submission', 'tcp', 0, 1, 587, 'smtp.fastmail.com.', $ttl);
		//$this->addSRV('imap', 'tcp', 0, 0, 0, '.', $ttl);
		$this->addSRV('imaps', 'tcp', 0, 1, 993, 'imap.fastmail.com.', $ttl);
		//$this->addSRV('pop3', 'tcp', 0, 0, 0, '.', $ttl);
		$this->addSRV('pop3s', 'tcp', 10, 1, 995, 'pop.fastmail.com.', $ttl);
		$this->addSRV('jmap', 'tcp', 0, 1, 443, 'jmap.fastmail.com.', $ttl);
		//$this->addSRV('carddav', 'tcp', 0, 0, 0, '.', $ttl);
		$this->addSRV('carddavs', 'tcp', 0, 1, 443, 'carddav.fastmail.com.', $ttl);
		//$this->addSRV('caldav', 'tcp', 0, 0, 0, '.', $ttl);
		$this->addSRV('caldavs', 'tcp', 0, 1, 443, 'caldav.fastmail.com.', $ttl);

		return $this;
	}


	public function addMailgun($dkim_hostname=NULL, $dkim_key=NULL, $subdomain='outgoing-mail', $ttl=NULL){
		// MX records
		$this->addMX($subdomain, 10, 'mxa.mailgun.org.', $ttl);
		$this->addMX($subdomain, 10, 'mxb.mailgun.org.', $ttl);

		// CNAME record
		$this->addCNAME("email.$subdomain", 'mailgun.org.', $ttl);

		// DKIM record
		if(
			!is_null($dkim_hostname)
			&&
			!is_null($dkim_key)
		) $this->addTXT("$dkim_hostname.$subdomain", $dkim_key, $ttl);

		// SPF record/include
		$this->addTXT($subdomain, 'v=spf1 include:mailgun.org ~all', $ttl);
		$this->spf['_includes'][]='mailgun.org';

		return $this;
	}


	/*
	Add a SPF record

	Unlike the other add* functions, addSPF() doesn't immediately add the SPF record to the zone file via $this->records. Instead, it simply stores the params that are sent to it in $this->spf['params']

	The internal helper function addSPFIf() is what actually generates the SPF record and adds it to the zone file. It is automatically called by output()

	Why break things up into two functions like this? Because the SPF record is dynamic, not static, and the other records in the zone file may affect it. So, it's important that the SPF record itself isn't actually generated until all records have been added to the zone file, which is exactly what addSPFIf() does
	*/
	public function addSPF($mx=NULL, $a=NULL, $includes=[], $mode='-all', $ttl=NULL){
		$this->spf['params'] = [
			'mx' => $mx,
			'a' => $a,
			'includes' => $includes,
			'mode' => $mode,
			'ttl' => $ttl
		];

		return $this;
	}


	private function addSPFIf(){
		// If SPF params are not set (because addSPF() was never called), don't add an SPF record to the zone file
		if(
			!isset($this->spf['params'])
			||
			!is_array($this->spf['params'])
		) return;

		// Get SPF variables
		$spf = $this->spf;
		$params = $spf['params'];

		// Get params
		$mx = $params['mx'];
		$a = $params['a'];
		$includes = $params['includes'];
		$mode = $params['mode'];
		$ttl = $params['ttl'];

		// Default values
		$mx = $mx ?? $this->hasRecordType('MX');
		$a = $a ?? $this->hasRecordType(['A', 'AAAA']);
		$mode = $mode ?? '-all';

		// Generate the includes array
		$includes = array_unique(
			array_merge($includes, $spf['_includes'])
		);

		// Convert params to strings for the SPF TXT record
		$mx_str = $mx ? 'mx ':'';
		$a_str = $a ? 'a ':'';
		$includes_str = implode(
			'',
			array_map(
				function($value){ return "include:$value "; },
				$includes
			)
		);

		// Add TXT record
		$this->addTXT($this->domain, "v=spf1 $mx_str$a_str$includes_str$mode", $ttl);
	}


	// Generates the zone file
	public function output(){
		$this->addSPFIf();

		if(
			in_array(
				'--format=json',
				$GLOBALS['argv']
			)
		) return json_encode($this->records);
		else{
			$this->calculateStrlenMaxes();

			$output = <<<OUTPUT
\$ORIGIN {$this->domain}
\$TTL {$this->ttl}
;{$this->domain}

OUTPUT;

			foreach($this->records as $record){
				$output.=$this->pad($record['name'], $this->strlen_maxes['name']);
				$output.=$this->pad($record['ttl'], $this->strlen_maxes['ttl']);
				$output.=$this->pad($record['class'], $this->strlen_maxes['class']);
				$output.=$this->pad($record['type'], $this->strlen_maxes['type']);
				$output.=$record['data'];
				$output.="\n";
			}

			return $output;
		}
	}
}

?>