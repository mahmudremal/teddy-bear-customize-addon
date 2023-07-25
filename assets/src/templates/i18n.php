<?php
function scan_directory_for_i18n_data($directory) {
    $allI18ns = array();

    // Scan the directory for files
    $files = scandir($directory);

    // Loop through the files
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && is_file($directory . '/' . $file)) {
            // Read the file contents
            $contents = file_get_contents($directory . '/' . $file);

            // Regular expression to extract i18n entries
            $pattern = '/(\w+)\.i18n\?\.(\w+)\?\?\'(.*?)\'\;/';
            // Use preg_match_all to find all matches
            preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER);

            // Loop through the matches and build the allI18ns array
            // print_r($matches);
            foreach ($matches as $match) {
                $objectKey = $match[2];
                $translationValue = $match[3];

                $translationValue = "__('" . addslashes($translationValue) . "', 'teddybearsprompts')"; // Replace 'mydomain' with your actual domain
                $allI18ns[$objectKey] = $translationValue;
            }
        }
    }

    return $allI18ns;
}

// Replace 'your_directory_path' with the actual directory path you want to scan
$directory = 'C:/xampp/htdocs/teddy-bear/wp-content/plugins/teddy-bear-customize-addon/assets/src/templates/scan/frontend'; // frontend
$result = scan_directory_for_i18n_data($directory);

// Output the result
// echo "var allI18ns = " . json_encode($result) . ";";
// print_r( $result );
foreach($result as $key => $text) {
    echo "\r\n\t'".$key."' => ".str_replace('\\\\\\', '\\', $text).",";
}
?>
