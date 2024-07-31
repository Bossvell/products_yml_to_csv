<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// URL YML-файла
$yml_url = 'Ваша ссылка на yml фид';

// Проверка доступности YML-файла
$xml_content = @file_get_contents($yml_url);
if ($xml_content === false) {
    die('Error: Failed to load YML file.');
}

// Парсинг YML-файла
$xml = @simplexml_load_string($xml_content);
if ($xml === false) {
    die('Error: Failed to parse YML file.');
}

// Преобразование в массив для удобства обработки
$json = json_encode($xml);
$array = json_decode($json, true);

// Функция для генерации CSV
function generate_csv($data) {
    $csv_data = array();

    // Заголовки CSV
    $csv_data[] = array('ID', 'Title', 'Description', 'Link', 'Image link', 'Price', 'Availability', 'Brand', 'Condition');

    // Обработка данных YML
    foreach ($data['shop']['offers']['offer'] as $offer) {
        $id = isset($offer['@attributes']['id']) ? $offer['@attributes']['id'] : 'N/A';
        $title = isset($offer['name']) ? $offer['name'] : 'N/A';
        $description = isset($offer['description']) ? $offer['description'] : 'N/A';
        $url = isset($offer['url']) ? $offer['url'] : 'N/A';
        $image = isset($offer['picture']) ? $offer['picture'] : 'N/A';
        $price = isset($offer['price']) ? number_format((float)$offer['price'], 2, '.', '') : 'N/A';
        $currency = isset($offer['currencyId']) ? $offer['currencyId'] : 'N/A';
        $availability = isset($offer['@attributes']['available']) && $offer['@attributes']['available'] == 'true' ? 'in stock' : 'out of stock';
        $brand = isset($offer['vendor']) ? $offer['vendor'] : 'Unknown';
        $condition = 'new'; // Или другое состояние

        // Объединяем цену и валюту
        $price_with_currency = $price . ' ' . $currency;

        $csv_data[] = array($id, $title, $description, $url, $image, $price_with_currency, $availability, $brand, $condition);
    }

    return $csv_data;
}

// Генерация CSV данных
$csv_data = generate_csv($array);

// Имя CSV-файла
$csv_filename = 'product_feed.csv';

// Выводим заголовки перед началом вывода содержимого
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $csv_filename . '"');

// Создаем и открываем CSV-файл в памяти
$fp = fopen('php://output', 'w');
if ($fp === false) {
    die('Error: Failed to create CSV file.');
}

// Добавление BOM для правильного распознавания UTF-8 в Excel
fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));

// Запись данных в CSV
foreach ($csv_data as $row) {
    fputcsv($fp, $row);
}

fclose($fp);
?>
