<?php
    
    $num = 0;
    //update customer_id and customer_type
    $customer_id = 999559;
    $customer_type = 4;

    do 
    {
        $file_name = 'data/abb/'. $num . '.json';
        $json = file_get_contents($file_name);
        $json_data = json_decode($json,true);
        
        $remark = "www.abb.com";
        $counter = 1;
        
        foreach($json_data['Items'] as $item) 
        {
            $duplicate_phone_check = false;
            $duplicate_web_check = false;
            $phone_check = true;
            $email_check = true;

            $country_name = strtolower($item['Address']['AddressCountry']);
        
            $path_web = 'results/index/web/'.$country_name.'.txt';
            if(!file_exists($path_web)) {
                $web_file_inital = fopen($path_web, 'w');
                fwrite($web_file_inital, "Web list\n");
                fclose($web_file_inital);
            }
        
            $path_phone = 'results/index/phone/'.$country_name.'.txt';
            if (!file_exists($path_phone)) {
                $phone_file_initial = fopen($path_phone, 'w');
                fwrite($phone_file_initial, "Phone list\n");
                fclose($phone_file_initial);
            }
        
            $text_file_name = 'results/combined_filtered/'. $country_name .'.txt';
            if (!file_exists($text_file_name)) {
                $main_file = fopen($text_file_name, 'w');
                $input_text = "Customer number;Customer name;Customer type;Number;E-mail;Address;Remark;Address row 1;Zip code;City/Province;State/Region;Country;\n";
                fwrite($main_file, $input_text);
            } else {
                $main_file = fopen($text_file_name, 'a');
    
            }
            $input_text = "";
            $input_text .=$customer_id.";";

            if ($item['Name']!=null) {
                $contact_info = $item['Name'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            $input_text .=$customer_type.";";

            if ($item['Contact']['Telephone']!=null) {
                $contact_info = $item['Contact']['Telephone'];
                $contact_info=str_replace(' ','',$contact_info);

                $phone_file = fopen($path_phone, 'a+');
                fseek($phone_file, 0);

                while(!feof($phone_file)) {
                    $test_line = preg_replace("/\n/","",fgets($phone_file));
                    if ($test_line == $contact_info) {
                        $duplicate_phone_check = true;
                    }
                }
                if ($duplicate_phone_check == false) {
                    fwrite($phone_file,$contact_info."\n");
                    fclose($phone_file);
                } else {
                    echo $contact_info."\n";
                    fclose($phone_file);
                }

                $contact_info .= ";";
            } else {
                $phone_check = false;
                $contact_info = ";";
            }
            $input_text .=$contact_info;  

            if ($item['Contact']['Email']!=null) {
                $contact_info = $item['Contact']['Email'];
                $contact_info=str_replace(' ','',$contact_info);

                $web_file = fopen($path_web, 'a+');
                fseek($web_file, 0);

                while(!feof($web_file)) {
                    $test_line = preg_replace("/\n/","",fgets($web_file));
                    if ($test_line == $contact_info) {
                        $duplicate_web_check = true;
                    }
                }
                if ($duplicate_web_check == false) {
                    fwrite($web_file,$contact_info."\n");
                    fclose($web_file);
                } else {
                    echo $contact_info."\n";
                    fclose($web_file);
                }

                $contact_info .= ";";
            } else {
                $email_check = false;
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($item['Contact']['Url']!=null) {
                $contact_info = $item['Contact']['Url'];
                $contact_info=str_replace('http://','',$contact_info);
                $contact_info=str_replace(' ','',$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            $input_text .=$remark.";";

            if ($item['Address']['StreetAddress']!=null) {
                $contact_info = $item['Address']['StreetAddress'];
                $contact_info = preg_replace("/\n/","",$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($item['Address']['PostalCode']!=null) {
                $contact_info = $item['Address']['PostalCode'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($item['Address']['AddressLocality']!=null) {
                $contact_info = $item['Address']['AddressLocality'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($item['Address']['AddressRegion']!=null) {
                $contact_info = $item['Address']['AddressRegion'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($item['Address']['AddressCountry']!=null) {
                $contact_info = $country_name . ";\n";
            } else {
                $contact_info = ";\n";
            }
 
            
            $input_text .=$contact_info;
        
            if(($duplicate_phone_check == true && $duplicate_web_check == true) || ($duplicate_phone_check == true && $email_check == false) || ($duplicate_web_check == true && $phone_check == false)) {
                $duplicate_path = 'results/duplicates/'.$country_name.'.txt';
                if (!file_exists($duplicate_path)) {
                    $duplicate_file = fopen($duplicate_path, 'w');
                    fwrite($duplicate_file, $input_text);
                    fclose($duplicate_file);
                } else {
                    $duplicate_file = fopen($duplicate_path, 'a');
                    fwrite($duplicate_file, $input_text);
                    fclose($duplicate_file);
                }
            } elseif ($phone_check == true || $email_check == true) {
                fwrite($main_file, $input_text);
            } else {
                $path_no_contact = 'results/empty/'.$country_name.'.txt';
                if (!file_exists($path_no_contact)) {
                    $empty_file = fopen($path_no_contact, 'w');
                    fwrite($empty_file, $input_text); 
                    fclose($empty_file);               
                } else {
                    $empty_file = fopen($path_no_contact, 'a');
                    fwrite($empty_file, $input_text); 
                    fclose($empty_file); 
                }
            }

            fclose($main_file);
            $customer_id++;
            $counter++;
        }

        $num++;
    } while($num < 60);

    echo $num.' Worked fine'."\n";

?>