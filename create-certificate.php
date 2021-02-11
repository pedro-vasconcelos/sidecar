<?php


/*
// CreateCa
$caPemPath = __DIR__.'/certs2/RequestCASelfSigned.pem';
$caKeyPath = __DIR__.'/certs2/RequestCASelfSigned.key';
$caSrlPath = __DIR__.'/certs2/RequestCASelfSigned.srl';

$oName = 'Request CA Self Signed Organization';
$cName = 'Request CA Self Signed CN';

$cmd = sprintf('sudo security delete-certificate -c "%s" /Library/Keychains/System.keychain',$cName);
shell_exec($cmd);

$cmd = sprintf('openssl req -new -newkey rsa:2048 -sha256 -days 365 -nodes -x509 -subj "/C=PT/ST=Lisbon/O=%s/localityName=Lisbon/commonName=%s/organizationalUnitName=Developers/emailAddress=%s/" -keyout "%s" -out "%s"',
        $oName, 
        $cName, 
        'root.certificate@request.pt', 
        $caKeyPath, 
        $caPemPath
        );
shell_exec($cmd);  

$cmd = sprintf('sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain "%s"', $caPemPath );
shell_exec($cmd);  
*/
// Cerificate

$url = 'navigatortogether.com.test';

$config = str_replace(
  'DOMAIN', 
  $url, 
  file_get_contents(__DIR__.'/certs2/ssl.conf')
);

file_put_contents(__DIR__.'/certs2/'.$url.'.conf', $config);

$keyPath = __DIR__.'/certs2/'.$url.'.key';
$cmd = sprintf('openssl genrsa -out "%s" 2048', $keyPath);
shell_exec($cmd);


// createSigningRequest
$cmd = sprintf(
  'openssl req -new -key "%s" -out "%s" -subj "/C=PT/ST=Lisbon/O=Request/localityName=Lisbon/commonName=%s/organizationalUnitName=Development/emailAddress=%s%s/" -config "%s"',
  __DIR__.'/certs2/'.$url.'.key', 
  __DIR__.'/certs2/'.$url.'.csr', 
  $url, 
  $url, 
  '@request.test', 
  __DIR__.'/certs2/'.$url.'.conf'
);
shell_exec($cmd);  

$caSrlParam = '-CAserial "' . $caSrlPath . '"';
if (! file_exists($caSrlPath)) {
    $caSrlParam .= ' -CAcreateserial';
}
$cmd = sprintf(
    'openssl x509 -req -sha256 -days 365 -CA "%s" -CAkey "%s" %s -in "%s" -out "%s" -extensions v3_req -extfile "%s"',
    $caPemPath, 
    $caKeyPath, 
    $caSrlParam, 
    __DIR__.'/certs2/'.$url.'.csr', 
    __DIR__.'/certs2/'.$url.'.crt', 
    __DIR__.'/certs2/'.$url.'.conf'
);

shell_exec($cmd);  

// $cmd = sprintf('sudo security delete-certificate -c "%s" /Library/Keychains/System.keychain',
// $url);
// shell_exec($cmd);


// $cmd = sprintf(
//     'sudo security add-trusted-cert -d -r trustAsRoot -k /Library/Keychains/System.keychain "%s"', 
//     __DIR__.'/certs2/'.$url.'.crt'
// );
// shell_exec($cmd);  
