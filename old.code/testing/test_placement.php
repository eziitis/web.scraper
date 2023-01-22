<?php

$folder_paths = [
    'results',
    'data',
    'data/mid_data',
    'data/mid_data/email',
    'data/mid_data/phone_number',
];
foreach ($folder_paths as $path) {
    if (!file_exists($path)) {
        mkdir($path);
    }
}
$customer_id = 1;

$file_name = 'test_placement.json';
$json = file_get_contents($file_name);
$json_data = json_decode($json,true);

foreach($json_data['Items'] as $item)
{
    $shared_phone_number = false;
    $shared_email = false;
    $phone_number_exists = true;
    $email_exists = true;

    if ($item['Address']['AddressCountry'] !== null) {
        $country_name = strtolower($item['Address']['AddressCountry']);
    } else {
        $country_name = 'unknown';
    }

    $line_text = $customer_id . ';';
    $line_text .= check_and_return_parameter_value($item['Name'], 'name');
    $line_text .= '4;';
    $line_text .= check_phone_number_or_email($item['Contact']['Telephone'], 'telephone', $country_name, $shared_phone_number, $phone_number_exists, $shared_email, $email_exists);
    $line_text .= check_phone_number_or_email($item['Contact']['Email'], 'email', $country_name, $shared_phone_number, $phone_number_exists, $shared_email, $email_exists);
    $line_text .= check_and_return_parameter_value($item['Contact']['Url'], 'url');
    $line_text .= 'www.abb.com;';
    $line_text .= check_and_return_parameter_value($item['Address']['StreetAddress'], 'address');
    $line_text .= check_and_return_parameter_value($item['Address']['PostalCode'], 'postal_code');
    $line_text .= check_and_return_parameter_value($item['Address']['AddressLocality'], 'address_locality');
    $line_text .= check_and_return_parameter_value($item['Address']['AddressRegion'], 'address_region');
    $line_text .= check_and_return_parameter_value($item['Address']['AddressCountry'], 'country');

    test_placement($shared_phone_number, $shared_email, $email_exists, $phone_number_exists, $line_text);

    $customer_id++;
}



function test_placement($shared_phone_number, $shared_email, $email_exists, $phone_number_exists, $line_text)
{
    if(($shared_phone_number && $shared_email) || ($shared_phone_number && !$email_exists) || ($shared_email && !$phone_number_exists)) {
        $duplicate_path = 'results/duplicate.csv';
        add_empty_or_duplicate($duplicate_path, $line_text . "\n");

    } elseif ($phone_number_exists || $email_exists) {
        $main_file_path = 'results/valid.csv';
        if (!file_exists($main_file_path)) {
            $main_file = fopen($main_file_path, 'w');
        } else {
            $main_file = fopen($main_file_path, 'a');
        }

        fwrite($main_file, $line_text . "\n");
        fclose($main_file);
    } else {
        $path_empty = 'results/empty.csv';
        add_empty_or_duplicate($path_empty, $line_text . "\n");
    }
}

function add_empty_or_duplicate($file_path, $value)
{
    $mode = !file_exists($file_path) ? 'w' : 'a';
    echo ($file_path . "\n");
    $file = fopen($file_path, $mode);
    fwrite($file, $value);
    fclose($file);
}

function check_phone_number_or_email($item, $type, $country_name, &$shared_phone_number, &$phone_number_exists, &$shared_email, &$email_exists)
{
    if ($item === null) {
        if ($type === 'telephone') {
            $phone_number_exists = false;
        } elseif ($type === 'email') {
            $email_exists = false;
        }
        return ';';
    } else {
        if ($type === 'telephone') {
            $file_path = 'data/mid_data/phone_number/'.$country_name.'.txt';
        } else {
            $file_path = 'data/mid_data/email/'.$country_name.'.txt';
        }

        $result = str_replace(' ','',$item);

        $file = fopen($file_path, 'a+');
        fseek($file, 0);

        $duplicate = false;
        while(!feof($file)) { // feof - norāda uz faila beigām
            $line = preg_replace("/\n/","",fgets($file));
            if ($line == $result) {
                if ($type === 'telephone') {
                    $shared_phone_number = true;
                } elseif ($type === 'email') {
                    $shared_email = true;
                }
                $duplicate = true;
            }
        }
        if ($duplicate === false) {
            fwrite($file, $result . "\n");
        }
        fclose($file);

        return $result . ";";
    }
}

function check_and_return_parameter_value($item, $type)
{
    if ($item === null) {
        $result = ';';
    } else {
        if ($type === 'postal_code' || $type === 'address_locality' || $type === 'address_region'  || $type === 'country') {
            $result = "$item;";
        } else {
            switch ($type) {
                case 'url':
                    $result = $item;
                    $result = str_replace('http://','',$result);
                    $result = str_replace('https://','',$result);
                    $result = str_replace(' ','',$result);
                    $result .= ";";
                    break;
                case 'address':
                    $result = $item;
                    $result = str_replace("\n",'',$result);
                    $result .= ";";
                    break;
                case 'name':
                    $result = $item;
                    $result = str_replace(";",'',$result);
                    $result .= ";";
                    break;
                default:
                    $result = ";";
                    break;
            }
        }
    }

    return $result;
}
