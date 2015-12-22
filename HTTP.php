<?php

/**
 * Class HTTP Класс для работы с HTTP.
 * 
 * @author Larship (Антон Коваленко).
 */
class HTTP
{
    /**
     * Метод выполняет HTTP-запрос с указанными параметрами.
     * 
     * @param array $_data Массив параметров запроса, который может содержать следующие ключи:
     * <code>
     * ['referer'] - Содержимое заголовка referer запроса
     * ['cookie_file'] - Имя файла, содержащего куки
     * ['cookie_jar'] - Имя файла, в котором будут сохранены куки запроса
     * ['cookies'] - Содержимое заголовка cookie запроса
     * ['method'] - Метод HTTP-запроса. Доступен GET и POST
     * ['data'] - Данные запроса в виде строки name1=val1&...
     * ['no_useragent'] - Параметр, который показывает, нужно ли устанавливать
     * заголовок Useragent в запросе
     * ['useragent'] - Содержимое заголовка useragent запроса
     * ['headers'] - Дополнительные заголовки запроса в виде массива формата
     *      ['Content-type: text/plain', 'Content-length: 100']
     * </code>
     * 
     * @return bool|mixed Содержимое загруженной страницы либо false в случае ошибки.
     */
    public static function curlSend($_data)
    {
        $agentBrowser = array('Firefox', 'Safari', 'Opera', 'Flock', 'Internet Explorer',
            'Ephifany', 'AOL Explorer', 'Seamonkey', 'Konqueror', 'GoogleBot');
        $agentOS = array('Windows 2000', 'Windows NT', 'Windows XP', 'Windows Vista', 'Windows 7',
            'Redhat Linux', 'Ubuntu', 'Fedora', 'FreeBSD', 'OpenBSD', 'OS 10.5');
        $userAgent = $agentBrowser[rand(0, 9)] . '/' . rand(1, 8) . '.' . rand(0, 9) . ' (' . $agentOS[rand(0, 10)] . ' ' . rand(1, 7) . '.' . rand(0, 9) . '; en-US;)';
        
        $curlObj = curl_init();
        curl_setopt($curlObj, CURLOPT_URL, $_data['url']);
        
        if(isset($_data['referer']))
        {
            curl_setopt($curlObj, CURLOPT_REFERER, $_data['referer']);
        }
        
        curl_setopt($curlObj, CURLOPT_HEADER, false);
        
        if(!empty($_data['cookie_file']))
        {
            curl_setopt($curlObj, CURLOPT_COOKIEFILE, $_data['cookie_file']);
        }
        
        if(!empty($_data['cookie_jar']))
        {
            curl_setopt($curlObj, CURLOPT_COOKIEJAR, $_data['cookie_jar']);
        }
        
        if(!empty($_data['cookies']))
        {
            curl_setopt($curlObj, CURLOPT_COOKIE, $_data['cookies']);
        }
        
        if(isset($_data['method']) && $_data['method'] == 'POST')
        {
            curl_setopt($curlObj, CURLOPT_POST, true);
        }
        else
        {
            curl_setopt($curlObj, CURLOPT_HTTPGET, true);
        }
        
        if(isset($_data['data']))
        {
            curl_setopt($curlObj, CURLOPT_POSTFIELDS, $_data['data']);
        }
        
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObj, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlObj, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObj, CURLOPT_TIMEOUT, 30);
        
        if(!isset($_data['no_useragent']))
        {
            if(isset($_data['useragent']))
            {
                curl_setopt($curlObj, CURLOPT_USERAGENT, $_data['useragent']);
            }
            else
            {
                curl_setopt($curlObj, CURLOPT_USERAGENT, $userAgent);
            }
        }
        
        curl_setopt($curlObj, CURLOPT_FRESH_CONNECT, true);
        
        if(!empty($_data['headers']))
        {
            curl_setopt($curlObj, CURLOPT_HTTPHEADER, $_data['headers']);
            curl_setopt($curlObj, CURLOPT_ENCODING, '');
        }
        
        $page = curl_exec($curlObj);
        
        $err = curl_error($curlObj);
        curl_close($curlObj);
        
        if(strlen($err) > 0)
        {
            return false;
        }
        
        return $page;
    }
    
    /**
     * Метод возвращает строку с куками, полученными при запросе указанного URL.
     *
     * @param string $_url Адрес, который требуется запросить.
     * @param string $_type Тип запроса (POST или GET).
     * @param string $_data Строка параметров запроса.
     *
     * @return bool|mixed Строка с куками или false в случае ошибки.
     */
    public static function curlGetCookies($_url, $_type = 'GET', $_data = '')
    {
        $curlObj = curl_init();
        curl_setopt($curlObj, CURLOPT_URL, $_url);
        curl_setopt($curlObj, CURLOPT_HEADER, true);
        
        if($_type === 'POST')
        {
            curl_setopt($curlObj, CURLOPT_POST, true);
        }
        else
        {
            curl_setopt($curlObj, CURLOPT_HTTPGET, true);
        }
        
        if(!empty($_data))
        {
            curl_setopt($curlObj, CURLOPT_POSTFIELDS, $_data);
        }
        
        curl_setopt($curlObj, CURLOPT_NOBODY, true);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObj, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlObj, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObj, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlObj, CURLOPT_FRESH_CONNECT, true);
        
        $page = curl_exec($curlObj);
        
        $err = curl_error($curlObj);
        curl_close($curlObj);
        
        if(strlen($err) > 0)
        {
            return false;
        }
        
        preg_match_all('#Set-Cookie: (.*);#i', $page, $results);
        
        $retArr = [ ];
        
        foreach($results[1] as $result)
        {
            $name = preg_replace('/(.*?)\=.*/i', '$1', $result);
            $retArr[$name] = $result;
        }
        
        return $retArr;
    }
}
