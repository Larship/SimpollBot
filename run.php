<?php

/**
 * Голосовальщик для сервиса опросов simpoll.ru
 *
 * @author Larship (Антон Коваленко)
 */

require 'HTTP.php';

const SIMPOLL_URL = 'http://simpoll.ru';
const COOKIES_FILE = './cookies.txt';

if(count($argv) != 4) {
    echo 'Неправильное количество аргументов (допустимо 3)' . PHP_EOL;
    return;
}

$surveyId = $argv[1];
$answerId = $argv[2];
$answersCount = (int) $argv[3];

$body = HTTP::curlGetCookies(SIMPOLL_URL . '/run/survey/' . $surveyId);

for($i = 0; $i < $answersCount; $i++)
{
    $body = HTTP::curlSend([
        'url' => SIMPOLL_URL . '/run/survey/' . $surveyId,
        'referrer' => 'https://rm.7733.ru/sd_requests/61520',
        'cookie_file' => COOKIES_FILE,
        'cookie_jar' => COOKIES_FILE,
    ]);
    
    $questionId = preg_replace('/.*toggleFreeRadio\((\d*)\).*/si', '$1', $body);
    
    HTTP::curlSend([
        'url' => SIMPOLL_URL . '/run/post/',
        'referrer' => SIMPOLL_URL . '/run/survey/' . $surveyId,
        'cookie_file' => COOKIES_FILE,
        'cookie_jar' => COOKIES_FILE,
        'method' => 'POST',
        'data' => "survey_key=$surveyId&page=1&quest[$questionId]=$answerId",
    ]);
    
    file_put_contents(COOKIES_FILE, '');
    
    echo 'Проголосовали ' . ($i + 1) . ' раз' . PHP_EOL;
    
    sleep(5);
}
