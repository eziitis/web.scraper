<?php
$customer_id = 1002555;
$customer_type = 4;
$remark = "www.legrand.com";

create_folder('results/legrand');
create_folder('results/legrand/duplicates');
create_folder('results/legrand/empty');

$legrand_file_name = 'data/legrand/contact_information_1.json';
$json = file_get_contents($legrand_file_name);
$json_data = json_decode($json,true);

foreach($json_data as $country_name) {
    foreach($country_name as $item) {
        $shared_phone_number = false;
        $shared_email = false;
        $phone_number_exists = true;
        $email_exists = true;

        $email_file_path = 'data/mid_data/email/' . $item['country'] . '.txt';
        create_if_needed($email_file_path, "Unique email list\n");
        $phone_number_file_path = 'data/mid_data/phone_number/' . $item['country'] . '.txt';
        create_if_needed($phone_number_file_path, "Phone list\n");
        $main_file_path = 'results/combined/' . $item['country'] . '.csv';
        if (!file_exists($main_file_path)) {
            $main_file = fopen($main_file_path, 'w');
            $input_text = "Customer number;Customer name;Customer type;Number;E-mail;Address;Remark;Address row 1;Zip code;City/Province;State/Region;Country;\n";
            fwrite($main_file, $input_text);
        } else {
            $main_file = fopen($main_file_path, 'a');
        }

        $line_text = $customer_id.";";
        $line_text .= check_and_return_legrand_parameter_value($item, 'company_name');
        $line_text .= $customer_type.";";
        $line_text .= check_phone_number($item, $item['country'], $shared_phone_number, $phone_number_exists);
        $line_text .= check_email($item, $item['country'], $shared_phone_number, $phone_number_exists, $shared_email, $email_exists, $customer_id);
        $line_text .= check_and_return_legrand_parameter_value($item, 'website');
        $line_text .= "$remark;";
        $line_text .= check_and_return_legrand_parameter_value($item, 'address');
        $line_text .= ";";
        $line_text .= ";";
        $line_text .= ";";
        $line_text .= $item['country'] . ";";

        if(($shared_phone_number == true && $shared_email == true) || ($shared_phone_number == true && $email_exists == false) || ($shared_email == true && $phone_number_exists == false)) {
            $duplicate_path = 'results/legrand/duplicates/'.$item['country'].'.csv';
            add_empty_or_duplicate($duplicate_path, $line_text . "\n");
        } elseif ($phone_number_exists == true || $email_exists == true) {
            fwrite($main_file, $line_text . "\n");
        } else {
            $path_no_contact = 'results/legrand/empty/'.$item['country'].'.csv';
            add_empty_or_duplicate($path_no_contact, $line_text . "\n");
        }

        fclose($main_file);
        $customer_id++;
    }
}

echo "completed\n";

function create_if_needed($file_path, $first_line_text)
{
    if (!file_exists($file_path)) {
        $file = fopen($file_path, 'w');
        fwrite($file, $first_line_text);
        fclose($file);
    }
    return 0;
}

function add_empty_or_duplicate($file_path, $value)
{
    $mode = !file_exists($file_path) ? 'w' : 'a';
    $file = fopen($file_path, $mode);
    fwrite($file, $value);
    fclose($file);

    return 0;
}

function check_and_return_legrand_parameter_value($item, $type)
{
    if (array_key_exists($type,$item)) {
        return $item[$type] . ";";
    } else {
        return ";";
    }
}

function check_phone_number($item, $country_name, &$shared_phone_number, &$phone_number_exists)
{
    if (!array_key_exists('tel_and_fax', $item)) {
        $phone_number_exists = false;
        return ';';
    } else {
        $value = str_replace(' ', '', $item);
        $value = str_replace('.', '', $value);
        $value = str_replace('+', '', $value);
        $result = '';

        if (str_contains($result, 'Tel')) {
            if (str_contains($result, 'Fax')) {
                $result = str_replace('Tel:','',$value);
                $result = preg_replace('/Fax:[0-9]+/','',$result);
                echo $result . "\n";
            } else {
                $result = str_replace('Tel:','',$value);
                echo $result . "\n";
            }
        }

        if ($result !== '') {
            $file = fopen('data/mid_data/phone_number/' . $country_name . '.txt', 'a+');
            fseek($file, 0);

            $duplicate = false;
            while(!feof($file)) {
                $line = preg_replace("/\n/","",fgets($file));
                if ($line == $result) {
                    $shared_phone_number = true;
                    $duplicate = true;
                }
            }
            if ($duplicate === false) {
                fwrite($file, $result . "\n");
            }
            fclose($file);
        }
        return $result . ";";
    }
}

function check_email($item, $country_name, &$shared_email, &$email_exists)
{
    if (!array_key_exists('email', $item)) {
        $email_exists = false;
        return ';';
    } else {
        $result = $item['email'];

        $file = fopen('data/mid_data/email/' . $country_name . '.txt', 'a+');
        fseek($file, 0);

        $duplicate = false;
        while(!feof($file)) {
            $line = preg_replace("/\n/","",fgets($file));
            if ($line == $result) {
                $shared_phone_number = true;
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

function create_folder($path)
{
    if (!file_exists($path)) {
        mkdir($path);
    }
    return 0;
}
?>