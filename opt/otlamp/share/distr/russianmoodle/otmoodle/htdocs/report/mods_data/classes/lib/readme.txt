mpdf60/mpdf.php was patched

В функции ImageProcessor/getImage необходимо использовать контекст, чтобы изображения загрузились как следует.
 
$context = stream_context_create([
    'http' => [
        'header' => 'Cookie: ' . $_SERVER['HTTP_COOKIE'] . "\r\n"
    ]
]);

file_get_contents($file, false, $context); 