<?php
    
    $num = 0;
    //update customer_id and customer_type

    $customer_id = 1002626;
    $customer_type = 4;
    $remark = "www.se.com";
    $countries_list = scandir('data/se/countries');
    unset($countries_list[0]);
    unset($countries_list[1]);

    $weird_counter = 0;
    
    foreach ($countries_list as $item) 
    {
        $counter = 1;
        $elem_list = scandir('data/se/countries/'.$item);
        unset($elem_list[0]);
        unset($elem_list[1]);
        if (file_exists('results/index/phone/'.$item.'.txt')) {
            unlink('results/index/phone/'.$item.'.txt');
        }
        if (file_exists('results/index/web/'.$item.'.txt')) {
            unlink('results/index/web/'.$item.'.txt');
        }
        
        $text_file_name = 'results/combined_filtered/'.$item.'.txt';
        $main_file = fopen($text_file_name, 'w');
        $input_text = "Customer number;Customer name;Customer type;Number;E-mail;Address;Remark;Address row 1;Zip code;City/Province;State/Region;Country;\n";
        fwrite($main_file, $input_text);

        $duplicate_path = 'results/duplicates/'.$item.'.txt';
        $duplicate_file = fopen($duplicate_path, 'w');
        fwrite($duplicate_file, $input_text);

        $path_web = 'results/index/web/'.$item.'.txt';
        $web_file_inital = fopen($path_web, 'w');
        fwrite($web_file_inital, "Web list\n");
        fclose($web_file_inital);
        $path_phone = 'results/index/phone/'.$item.'.txt';
        $phone_file_initial = fopen($path_phone, 'w');
        fwrite($phone_file_initial, "Phone list\n");
        fclose($phone_file_initial);

        $path_no_contact = 'results/empty/'.$item.'.txt';
        $empty_file = fopen($path_no_contact, 'w');
        fwrite($empty_file, $input_text);

        foreach ($elem_list as $elem)
        {
            $duplicate_phone_check = false;
            $duplicate_web_check = false;
            $phone_check = true;
            $email_check = true;

            $file_name = 'data/se/countries/'.$item.'/' .$elem;
            $json = file_get_contents($file_name);
            $json_data = json_decode($json,true);

            $input_text = "";
            $input_text .=$customer_id.";";

            if ($json_data['companyName']!=null) {
                $contact_info = $json_data['companyName'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;
            
            $input_text .=$customer_type.";";

            if ($json_data['partnerDetails']['partnerContact']['phone']!=null) {
                $contact_info = $json_data['partnerDetails']['partnerContact']['phone'];
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

            if ($json_data['partnerDetails']['partnerContact']['email']!=null) {
                $contact_info = $json_data['partnerDetails']['partnerContact']['email'];
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
                $contact_info = ";";
                $email_check = false;
            }
            $input_text .=$contact_info;

            if ($json_data['webSite']!=null) {
                $contact_info = $json_data['webSite'];
                $contact_info=str_replace('http://','',$contact_info);
                $contact_info=str_replace(' ','',$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            $input_text .=$remark.";";
            
            if ($json_data['address1']!=null) {
                $contact_info = $json_data['address1'];
                $contact_info=preg_replace("/\n/","",$contact_info);
                $contact_info=str_replace('  ','',$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($json_data['zipCode']!=null) {
                $contact_info = $json_data['zipCode'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($json_data['city']!=null) {
                $contact_info = $json_data['city'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($json_data['administrativeRegion']!=null) {
                $contact_info = $json_data['administrativeRegion'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;
           
            if ($counter == count($elem_list)) {

                if ($json_data['country']!=null) {
                    $json_data['country'] . ";";
                } else {
                    $contact_info = ";";
                }
            } else {

                if ($json_data['country']!=null) {
                    $contact_info = $json_data['country'] . ";\n";
                } else {
                    $contact_info = ";\n";
                }
            }     
            
            $input_text .=$contact_info;

            if(($duplicate_phone_check == true && $duplicate_web_check == true) || ($duplicate_phone_check == true && $email_check == false) || ($duplicate_web_check == true && $phone_check == false)) {
                fwrite($duplicate_file, $input_text);
            } elseif ($phone_check == true || $email_check == true) {
                fwrite($main_file, $input_text);
            } else {
                fwrite($empty_file, $input_text);
            }
            $customer_id++;
            $counter++;
        }

        fclose($main_file);
        fclose($duplicate_file);
        fclose($empty_file);

        echo 'worked';
    }

    //print_r($elem_list);
       

?>