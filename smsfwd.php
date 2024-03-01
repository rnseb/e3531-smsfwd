<?php
function request($url, $headers = array(), $method = 'GET', $fields = '') {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);

    if ($method === 'POST') {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = curl_exec($curl);
    curl_close($curl);

    return $data;
}

$data = request('http://192.168.8.1/api/webserver/SesTokInfo');
$xml = new SimpleXMLElement($data);

$headers = array(
    'X-Requested-With: XMLHttpRequest',
    '__RequestVerificationToken: ' . $xml->TokInfo,
    'Cookie: ' . $xml->SesInfo
);

$fields = '<request><PageIndex>1</PageIndex><ReadCount>20</ReadCount><BoxType>1</BoxType><SortType>0</SortType><Ascending>0</Ascending><UnreadPreferred>1</UnreadPreferred></request>';

$data = request('http://192.168.8.1/api/sms/sms-list', $headers, 'POST', $fields);
$xml = new SimpleXMLElement($data);

foreach ($xml->Messages->Message as $message) {
    $headers2 = array(
        'From' => $message->Phone . '<smsfwd@example.com>'
    );
    $message2 = 'SMS from ' . $message->Phone . ' at ' . $message->Date . "\r\n\r\n" . $message->Content;

    if (mail('your@email.com', 'SMS from ' . $message->Phone, $message2, $headers2)) {
        $data = request('http://192.168.8.1/api/webserver/SesTokInfo');
        $xml = new SimpleXMLElement($data);

        $headers = array(
            'X-Requested-With: XMLHttpRequest',
            '__RequestVerificationToken: ' . $xml->TokInfo,
            'Cookie: ' . $xml->SesInfo
        );

        $fields = '<?xml version="1.0" encoding="UTF-8"?><request><Index>' . $message->Index . '</Index></request>';
        api_call('http://192.168.8.1/api/sms/delete-sms', $headers, 'POST', $fields);
    }
}
?>
