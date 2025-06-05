<?php
header("Access-Control-Allow-Origin: *");
// 句子包版本
$version = "1.0.399";

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

$sentenceUrls = [
    'a' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/a.json",
    'b' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/b.json",
    'c' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/c.json",
    'd' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/d.json",
    'e' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/e.json",
    'f' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/f.json",
    'g' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/g.json",
    'h' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/h.json",
    'i' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/i.json",
    'j' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/j.json",
    'k' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/k.json",
    'l' => "https://cdn.jsdelivr.net/gh/hitokoto-osc/sentences-bundle@$version/sentences/l.json",
];

$c          = isset($_GET['c'])         ? strtolower($_GET['c']) : null;
$type       = isset($_GET['type'])      ? strtolower($_GET['type']) : null;
$max_length = isset($_GET['max_length'])? intval($_GET['max_length']) : null;
$min_length = isset($_GET['min_length'])? intval($_GET['min_length']) : null;

function getCachedJson($letter, $url, $cacheDir) {
    global $version;
    $cacheFile = $cacheDir . '/' . $version . '_' . $letter . '.json';
    if (file_exists($cacheFile)) {
        $data = file_get_contents($cacheFile);
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
                        curl_close($ch);
            return '';
        }
        curl_close($ch);
        file_put_contents($cacheFile, $data);
    }
    return $data;
}

$allSentences = []; 
if ($c === null && $type === "all") {
    foreach ($sentenceUrls as $letter => $url) {
        $jsonData = getCachedJson($letter, $url, $cacheDir);
        if ($jsonData) {
            $data = json_decode($jsonData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $allSentences = array_merge($allSentences, $data);
            }
        }
    }
    if (empty($allSentences)) {
        echo "无法解析句子或句子为空";
        exit;
    }
} else {
    if ($c !== null && array_key_exists($c, $sentenceUrls)) {
        $keyLetter = $c;
    } else {
        $keys = array_keys($sentenceUrls);
        $keyLetter = $keys[array_rand($keys)];
    }
    $selectedUrl = $sentenceUrls[$keyLetter];
    $jsonData = getCachedJson($keyLetter, $selectedUrl, $cacheDir);
    $allSentences = json_decode($jsonData, true);
}

$filteredSentences = array_filter($allSentences, function($sentence) use ($max_length, $min_length) {
    if (!isset($sentence['length'])) {
        return true;
    }
    $len = intval($sentence['length']);
    if ($max_length !== null && $len > $max_length) {
        return false;
    }
    if ($min_length !== null && $len < $min_length) {
        return false;
    }
    return true;
});

if (empty($filteredSentences)) {
    echo "没有符合长度要求的句子";
    exit;
}

if ($type === "all") {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_values($filteredSentences), JSON_UNESCAPED_UNICODE);
    exit;
}

$randomSentence = $filteredSentences[array_rand($filteredSentences)];

if ($type === "json") {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($randomSentence, JSON_UNESCAPED_UNICODE);
} else {
    echo $randomSentence['hitokoto'];
}
?>
