<?php
    require 'config.php';

    $id = $_GET['id'] ?? null;
    if ($id) {
        $url = $baseUrl . "?action=delete";
        $data = ["record_id" => $id];
        requestData($url, $data);
    }
    header("Location: view.php");
    exit;
?>

<!--?php
require 'config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: view.php");
    exit;
}

// ✅ Fetch record details from API
$url = $baseUrl . "?action=view&id=" . $id;
$result = requestData($url);
$result = preg_replace('/^[^\{]+/', '', $result);
$result = preg_replace('/[^\}]+$/', '', $result);
$data = json_decode($result, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ User confirmed delete
    $url = $baseUrl . "?action=delete";
    $dataDelete = ["record_id" => $id];
    requestData($url, $dataDelete);

    header("Location: view.php?msg=deleted");
    exit;
}
?>