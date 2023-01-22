<?php

    //selfcheck

    $countries_list = scandir('results/se');
    unset($countries_list[0]);
    unset($countries_list[1]);
    unset($countries_list[2]);
    unset($countries_list[4]);
    print_r($countries_list);
    $phone_list = [];

    foreach ($countries_list as $item) {

        $file_path_name = 'results/se/'.$item;
        $main_file = fopen($file_path_name, 'r');

        $file_rewrite_path = 'results/se/aa_check/'.$item;
        $rewrite_file = fopen($file_rewrite_path, 'w');

        $line = fgets($main_file);
        fwrite($rewrite_file, $line);


        $line = fgets($main_file);
        echo $line;

        while(!feof($main_file)) {
            $reg_ex = '//';
            preg_match($reg_ex, $line, $mid_elem);
            array_push($phone_list,$mid_elem);
            //$line = fgets();
        }

    }

?>