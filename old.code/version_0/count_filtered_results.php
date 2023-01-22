<?php
    
$file_list = scandir('results/combined_filtered');
unset($file_list[0]);
unset($file_list[1]);
unset($file_list[2]);
unset($file_list[3]);
//print_r($file_list);
$counter = 0;

foreach ($file_list as $item) {
    $file_path = 'results/combined_filtered/'.$item;
    $main_file = fopen($file_path, 'r');

    while(!feof($main_file)) {
        $file_line = fgets($main_file);
        if ($file_line != '' || preg_match('/Customer number/',$file_line) != true) {
            $counter++;
        }
    }

    fclose($main_file);
}

echo $counter;

?>