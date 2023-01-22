<?php
    
$num = 0;
//update customer_id and customer_type

$customer_id = 1002626;
$customer_type = 4;
$remark = "www.se.com";

create_folder('results/se');
create_folder('results/se/duplicates');
create_folder('results/se/empty');

$countries_ids_list = scandir('data/se');
unset($countries_ids_list[0]);
unset($countries_ids_list[1]);

foreach ($countries_ids_list as $country_code) {
    $country_name = get_country_name($country_code);
    if ($country_name !== null && $country_name !== '') {
        $country_json_file_list = scandir('data/se/' . $country_code);
        unset($country_json_file_list[0]);
        unset($country_json_file_list[1]);

        foreach ($country_json_file_list as $country_json_file_name) {
            $shared_phone_number = false;
            $shared_email = false;
            $phone_number_exists = true;
            $email_exists = true;

            $file_name = 'data/se/'. $country_code . '/' . $country_json_file_name;
            $json = file_get_contents($file_name);
            $json_data = json_decode($json,true);

            $line_text = $customer_id . ';';
            $line_text .= check_writable_data($json_data[0], 'companyName');
            $line_text .= $customer_type . ';';
            $line_text .= check_se_contacts($json_data[0], $shared_email, $email_exists, $shared_phone_number, $phone_number_exists, $country_name, 'phone');
            $line_text .= check_se_contacts($json_data[0], $shared_email, $email_exists, $shared_phone_number, $phone_number_exists, $country_name, 'email');
            $line_text .= check_writable_data($json_data[0], 'webSite');
            $line_text .= $remark . ';';
            $line_text .= check_writable_data($json_data[0], 'address1');
            $line_text .= check_writable_data($json_data[0], 'zipCode');
            $line_text .= check_writable_data($json_data[0], 'city');
            $line_text .= check_writable_data($json_data[0], 'administrativeRegion');
            $line_text .= $country_name . ';';

            if(($shared_phone_number && $shared_email) || ($shared_phone_number && !$email_exists) || ($shared_email && !$phone_number_exists)) {
                $duplicate_path = 'results/se/duplicates/' . $country_name . '.csv';
                add_empty_or_duplicate($duplicate_path, $line_text . "\n");

            } elseif ($phone_number_exists || $email_exists) {
                $main_file_path = 'results/combined/'. $country_name .'.csv';
                if (!file_exists($main_file_path)) {
                    $main_file = fopen($main_file_path, 'w');
                    $header_text = "Customer number;Customer name;Customer type;Number;E-mail;Address;Remark;Address row 1;Zip code;City/Province;State/Region;Country;\n";
                    fwrite($main_file, $header_text);
                } else {
                    $main_file = fopen($main_file_path, 'a');
                }

                fwrite($main_file, $line_text . "\n");
                fclose($main_file);
            } else {
                $path_empty = 'results/se/empty/'.$country_name.'.csv';
                add_empty_or_duplicate($path_empty, $line_text . "\n");
            }

            $customer_id++;
        }
    }
}

echo "completed\n";

function get_country_name($country_code) {
    $country_name = '';
    $counter = 0;
    $file_name = 'data/se/'. $country_code . '/contact_information_0.json';
    $json = file_get_contents($file_name);
    $json_data = json_decode($json,true);
    if(array_key_exists(0, $json_data)) {
        do {
            if ($json_data[$counter]['country'] !== null) {
                $country_name = $json_data[$counter]['country'];
                $country_name = str_replace(' ','',$country_name);
                $country_name = str_replace('-','_',$country_name);
                $country_name = strtolower($country_name);
            }
            $counter++;
        } while($country_name === '' && $counter < 50);

        return $country_name;
    } else {
        return null;
    }
}

function create_if_needed($file_path, $first_line_text)
{
    if (!file_exists($file_path)) {
        $file = fopen($file_path, 'w');
        fwrite($file, $first_line_text);
        fclose($file);
    }
    return 0;
}

function check_writable_data($json_data, $type) {
    if ($json_data[$type] === null) {
        return ";";
    } else {
        switch ($type) {
            case 'companyName':
                return $json_data[$type] . ";";
            case 'webSite':
                $result = $json_data[$type];
                $result=str_replace('http://','',$result);
                $result=str_replace(' ','',$result);
                return $result .= ";";
            case 'address1':
                $result = $json_data[$type];
                $result = preg_replace("/\n/","",$result);
                $result = str_replace('  ','',$result);
                return $result .= ";";
            case 'zipCode':
                return $json_data[$type] . ";";
            case 'city':
                return $json_data[$type] . ";";
            case 'administrativeRegion':
                return $json_data[$type] . ";";
        }
    }
}



function add_empty_or_duplicate($file_path, $value)
{
    $mode = !file_exists($file_path) ? 'w' : 'a';
    $file = fopen($file_path, $mode);
    fwrite($file, $value);
    fclose($file);

    return 0;
}

function check_se_contacts($json_data, &$shared_email, &$email_exists, &$shared_phone_number, &$phone_number_exists, $country_name, $type)
{
    if ($type === 'phone') {
        $result = $json_data['partnerDetails']['partnerContact']['phone'];
    } else {
        $result = $json_data['partnerDetails']['partnerContact']['email'];
    }
    if ($result != null) {
        $result = str_replace(' ','',$result);

        if ($type === 'phone') {
            $file_path = 'data/mid_data/phone_number/'.$country_name.'.txt';
        } else {
            $file_path = 'data/mid_data/email/'.$country_name.'.txt';
        }

        $file = fopen($file_path, 'a+');
        fseek($file, 0);

        while(!feof($file)) {
            $test_line = preg_replace("/\n/","",fgets($file));
            if ($test_line == $result) {
                if ($type === 'phone') {
                    $shared_phone_number = true;
                } else {
                    $shared_email = true;
                }
            }
        }
        if (($type === 'email' && !$shared_email) || ($type === 'phone' && !$shared_phone_number)) {
            fwrite($file,$result."\n");
        }
        fclose($file);

        return $result .= ";";
    } else {
        if ($type === 'phone') {
            $phone_number_exists = false;
        } else {
            $email_exists = false;
        }
        return ";";
    }
}

function create_folder($path)
{
    if (!file_exists($path)) {
        mkdir($path);
    }
    return 0;
}
?>