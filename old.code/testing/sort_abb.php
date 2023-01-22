<?php

//need cleaner to remove existing files, do full cleanup
clean_all_existing_result_files_and_mid_files('results');
clean_all_existing_result_files_and_mid_files('data/mid_data');

$error = '';

$customer_id = 999559;
$customer_type = 4;
$remark = "www.abb.com";

$file_list = scandir('data/abb');
$numeric_file_list = array_map('clean_json_name', $file_list);
$last_file_number = max($numeric_file_list);

create_folder('data/mid_data');
create_folder('data/mid_data/email');
create_folder('data/mid_data/phone_number');
create_folder('results');
create_folder('results/combined');
create_folder('results/abb');
create_folder('results/abb/duplicates');
create_folder('results/abb/empty');

for ($counter = 1; $counter <= $last_file_number; $counter++)
{
    $file_name = 'data/abb/'. $counter . '.json';
    $json = file_get_contents($file_name);
    $json_data = json_decode($json,true);

    foreach($json_data['Items'] as $item)
    {
        $shared_phone_number = false;
        $shared_email = false;
        $phone_number_exists = true;
        $email_exists = true;

        // if ($customer_id === 999559) {
        //     echo '999559' . "\n";
        // }

        //unknown country

        $country_name = strtolower($item['Address']['AddressCountry']);
        $country_name = str_replace(' ', '_', $country_name);
        $email_file_path = 'data/mid_data/email/'.$country_name.'.txt';
        create_if_needed($email_file_path, "Unique email list\n");
        $phone_number_file_path = 'data/mid_data/phone_number/'.$country_name.'.txt';
        create_if_needed($phone_number_file_path, "Phone list\n");

        $main_file_path = 'results/combined/'. $country_name .'.csv';
        if (!file_exists($main_file_path)) {
            $main_file = fopen($main_file_path, 'w');
            $header_text = "Customer number;Customer name;Customer type;Number;E-mail;Address;Remark;Address row 1;Zip code;City/Province;State/Region;Country;\n";
            fwrite($main_file, $header_text);
        } else {
            $main_file = fopen($main_file_path, 'a');
        }

        $line_text = $customer_id.";";
        $line_text .= check_and_return_parameter_value($item['Name'], 'name');
        $line_text .= $customer_type.";";
        $line_text .= check_phone_number_or_email($item['Contact']['Telephone'], 'telephone', $country_name, $shared_phone_number, $phone_number_exists, $shared_email, $email_exists, $customer_id);
        $line_text .= check_phone_number_or_email($item['Contact']['Email'], 'email', $country_name, $shared_phone_number, $phone_number_exists, $shared_email, $email_exists, $customer_id);
        $line_text .= check_and_return_parameter_value($item['Contact']['Url'], 'url');
        $line_text .= "$remark;";
        $line_text .= check_and_return_parameter_value($item['Address']['StreetAddress'], 'address');
        $line_text .= check_and_return_parameter_value($item['Address']['PostalCode'], 'postal_code');
        $line_text .= check_and_return_parameter_value($item['Address']['AddressLocality'], 'address_locality');
        $line_text .= check_and_return_parameter_value($item['Address']['AddressRegion'], 'address_region');
        $line_text .= check_and_return_parameter_value($item['Address']['AddressCountry'], 'country');

        // if ($customer_id === 999559) {
        //     echo $shared_phone_number . "\n";
        //     echo $shared_email . "\n";
        //     echo $email_exists . "\n";
        //     echo $phone_number_exists . "\n";
        // }

        if(($shared_phone_number == true && $shared_email == true) || ($shared_phone_number == true && $email_exists == false) || ($shared_email == true && $phone_number_exists == false)) {
            $duplicate_path = 'results/abb/duplicates/'.$country_name.'.csv';
            add_empty_or_duplicate($duplicate_path, $line_text . "\n");
        } elseif ($phone_number_exists == true || $email_exists == true) {
            fwrite($main_file, $line_text . "\n");
        } else {
            $path_no_contact = 'results/abb/empty/'.$country_name.'.csv';
            add_empty_or_duplicate($path_no_contact, $line_text . "\n");
        }

        fclose($main_file);
        $customer_id++;
    }
}

function clean_json_name($name)
{
    $name = str_replace('.json','',$name);
    return (int)$name;
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
function create_folder($path)
{
    if (!file_exists($path)) {
        mkdir($path);
    }
    return 0;
}

function check_and_return_parameter_value($item, $type)
{
    if ($item === null) {
        $result = ';';
    } else {
        if ($type === 'name' || $type === 'postal_code' || $type === 'address_locality' || $type === 'address_region'  || $type === 'country') {
            $result = "$item;";
        } else {
            switch ($type) {
                case 'url':
                    if ($item !== null) {
                        $result = $item;
                        $result = str_replace('http://','',$result);
                        $result = str_replace(' ','',$result);
                        $result .= ";";
                    }
                    break;
                case 'address':
                    if ($item !== null) {
                        $result = $item;
                        $result = str_replace("\n",'',$result);
                        $result .= ";";
                    }
                    break;
            }
        }
    }

    return $result;
}

//need to remove customer_id, was for testing

function check_phone_number_or_email($item, $type, $country_name, &$shared_phone_number, &$phone_number_exists, &$shared_email, &$email_exists, $customer_id)
{

     if ($customer_id === 999559) {
         echo $shared_phone_number . "\n";
         echo $shared_email . "\n";
         echo $email_exists . "\n";
         echo $phone_number_exists . "\n";
     }

    if ($item === null) {
        if ($type === 'telephone') {
            $phone_number_exists = false;
        } elseif ($type === 'email') {
            $$email_exists = false;
        }
        return ';';
    } else {
        if ($type === 'telephone') {
            $file_path = 'data/mid_data/phone_number/'.$country_name.'.txt';
        } elseif ($type === 'email') {
            $file_path = 'data/mid_data/email/'.$country_name.'.txt';
        }

        $result = str_replace(' ','',$item);

        $file = fopen($file_path, 'a+');
        fseek($file, 0);

        $duplicate = false;
        while(!feof($file)) {
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

function add_empty_or_duplicate($file_path, $value)
{
    $mode = !file_exists($file_path) ? 'w' : 'a';
    $file = fopen($file_path, $mode);
    fwrite($file, $value);
    fclose($file);

    return 0;
}

echo "completed\n";

function clean_all_existing_result_files_and_mid_files(string $file_path):void
{
    if (is_dir($file_path)) {
        $item_list = scandir($file_path);
        if (count($item_list) === 2) {
            rmdir($file_path);
        } else {
            unset($item_list[0]);
            unset($item_list[1]);
            foreach ($item_list as $item) {
                clean_all_existing_result_files_and_mid_files($file_path . '/' . $item);
            }
        }
    } else {
        unlink($file_path);
    }
}

?>