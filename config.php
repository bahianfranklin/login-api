<?php
    // API Base URL
    $baseUrl = "https://api.mandbox.com/apitest/v1/contact.php";

    // Universal cURL request function
    function requestData($url, $data = []) {
        $ch = curl_init($url);
        $postData = http_build_query($data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
?>
