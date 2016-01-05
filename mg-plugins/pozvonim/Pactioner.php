<?php

/**
 * Класс Pactioner наследник стандарного Actioner
 * Предназначен для выполнения действий,  AJAX запросов плагина
 *
 * @author Mazx <mazx@ya.ru>
 */
class Pactioner extends Actioner
{
    const APPS_HOST = 'http://appspozvonim.com';
    const INSTALL_URL = '/moguta/install';
    const RESTORE_URL = '/moguta/restore';
    const LOGIN_URL = '/moguta/login';

    private $pluginName = 'pozvonim';
    private $apiError;

    /**
     * @return boolean
     */
    public function restoreTokenToEmail()
    {
        if (isset($_POST['data']['email'])) {
            $email = $_POST['data']['email'];
            if ($email = $this->validEmail($email)) {
                @$data = unserialize(stripslashes(MG::getOption('pozvonimOption')));
                $data = is_array($data) ? $data : array();
                $data['email'] = $email;
                MG::setOption(array('option' => 'pozvonimOption', 'value' => addslashes(serialize($data))));
                if ($data = $this->restoreToken($email)) {
                    $this->messageSucces = $this->lang['TOKEN_SENT_TO_EMAIL'] . $email;
                    return true;
                }
            }
        }
        $this->messageError = $this->apiError ? $this->apiError : $this->lang['TOKEN_RESTORE_ERROR'];
        return false;
    }

    /**
     * Сохраняет  опции плагина
     *
     * @return boolean
     */
    public function saveBaseOption()
    {
        $this->messageSucces = $this->lang['SAVE_BASE'];
        $this->messageError = $this->lang['NOT_SAVE_BASE'];
        if (!empty($_POST['data'])) {

            @$oldData = unserialize(stripslashes(MG::getOption('pozvonimOption')));
            $oldData = is_array($oldData) ? $oldData : array();
            $data = $_POST['data'];
            if (isset($data['reset'])) {
                $data = unserialize(stripslashes(MG::getOption('pozvonimOption')));
                if (is_array($data)) {
                    unset($data['id']);
                    MG::setOption(array('option' => 'pozvonimOption', 'value' => addslashes(serialize($data))));
                }
                $this->removeShortCode();
                $this->messageSucces = 'reload';
                return true;
            }
            if (isset($data['code']) && !empty($data['code'])) {
                $data['token'] = $oldData['token'];
                if (!preg_match('/\/([a-z0-9]{32})\/connect/iu', $data['code'], $code)) {
                    $this->messageError = $this->lang['BAD_CODE'];
                    return false;
                }
                $code = $code[1];
                $data['key'] = $code;
                MG::setOption(array('option' => 'pozvonimOption', 'value' => addslashes(serialize($data))));
                $this->messageSucces = $this->lang['CODE_SAVED'];
                $this->installShortCode();
                return true;
            }

            if ($data = $this->valid($data)) {
                if (isset($data['code'])) {
                    unset($data['code']);
                }
                MG::setOption(array('option' => 'pozvonimOption', 'value' => addslashes(serialize($data))));

                if ($data = $this->register($data)) {
                    if (isset($data['code'])) {
                        unset($data['code']);
                    }

                    MG::setOption(array('option' => 'pozvonimOption', 'value' => addslashes(serialize($data))));
                    $this->messageSucces = 'reload';
                } else {
                    $this->messageError = $this->apiError ? $this->apiError : $this->lang['EMAIL_EXIST'];
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

    public function register($data)
    {
        $data['token'] = !empty($data['token']) ? $data['token'] : md5(uniqid('', true));
        $curl = new PCurl();
        $data['locale'] = substr(MG::getSetting('languageLocale'), 0, 2);
        try {
            $curl->get(static::APPS_HOST . static::INSTALL_URL, $data + array('locale' => $this->getLocale()));
            $result = trim($curl->response);
            if ($result) {
                $result = @json_decode(trim($result), true);

                if (is_array($result)) {
                    if ($result['status'] != 1) {
                        $this->apiError = $result['message'];
                        return false;
                    } else {
                        $data['key'] = $result['key'];
                        $data['id'] = $result['id'];
                        $this->installShortCode();
                        return $data;
                    }
                }
            }
        } catch (Exception $e) {

        }

        $this->messageError = $this->lang['API_ERROR'];
        return false;
    }

    /**
     *
     */
    public function installShortCode()
    {
        $result = DB::query("SELECT `option`, `value` FROM `" . PREFIX . "setting`  WHERE `option`='templateName' ");
        $row = DB::fetchAssoc($result);
        $template = __DIR__ . '/../../mg-templates/' . $row['value'] . '/template.php';
        if (file_exists($template)) {
            $content = file_get_contents($template);
            if (!preg_match('/\[pozvonim\]/i', $content)) {
                $content = preg_replace('/<\/body>/i', "[pozvonim]\n</body>", $content);
                file_put_contents($template, $content);
            }
        }
    }

    /**
     *
     */
    public function removeShortCode()
    {
        $result = DB::query("SELECT `option`, `value` FROM `" . PREFIX . "setting`  WHERE `option`='templateName' ");
        $row = DB::fetchAssoc($result);
        $template = __DIR__ . '/../../mg-templates/' . $row['value'] . '/template.php';
        if (file_exists($template)) {
            $content = file_get_contents($template);
            if (preg_match('/\[pozvonim\]/i', $content)) {
                $content = str_replace("[pozvonim]", "", $content);
                file_put_contents($template, $content);
            }
        }
    }

    /**
     * @param $email
     * @return bool
     */
    public function restoreToken($email)
    {
        $curl = new PCurl();
        try {
            $curl->get(static::APPS_HOST . static::RESTORE_URL, array('email' => $email, 'locale' => $this->getLocale()));
            $result = trim($curl->response);
            if ($result) {
                $result = @json_decode(trim($result), true);
                if (is_array($result)) {
                    if ($result['status'] != 1) {
                        $this->apiError = $result['message'];
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        } catch (Exception $e) {

        }
        return false;
    }

    public function getLocale()
    {
        return substr(MG::getSetting('languageLocale'), 0, 2);
    }

    /**
     * @param $data
     * @return bool
     */
    public function valid($data)
    {
        foreach (array('phone', 'email', 'host') as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->messageError = $this->lang['FIELD_' . strtoupper($field) . '_REQUIRED'];
                return false;
            } else {
                $data[$field] = trim($data[$field]);
            }
        }

        if (isset($data['token']) && !empty($data['token'])) {
            if (mb_strlen($data['token']) != 32) {
                $this->messageError = $this->lang['BAD_TOKEN'];
                return false;
            }
        }

        if (!preg_match('/^\+[0-9]{9,20}$/', $data['phone'])) {
            $this->messageError = $this->lang['BAD_PHONE'];
            return false;
        }

        $this->validEmail($data['email']);

        if (!preg_match('/\b([a-z0-9а-я]+(-[a-z0-9а-я]+)*\.)+[a-zрф]{2,}\b/ix', $data['host'])) {
            $this->messageError = $this->lang['BAD_HOST'];
            return false;
        }

        return $data;
    }

    /**
     * @param $email
     * @return bool|string
     */
    public function validEmail($email)
    {
        $email = trim($email);
        if (!isset($email) || empty($email)) {
            $this->messageError = $this->lang['FIELD_EMAIL_REQUIRED'];
            return false;
        }

        if (!preg_match('/[A-Z0-9._%+-]+@[A-Z0-9а-я.-]+\.[A-Zрф]{2,6}/ui', $email)) {
            $this->messageError = $this->lang['BAD_EMAIL'];
            return false;
        }

        return $email;
    }

}

class PCurl
{
    const AUTH_BASIC = CURLAUTH_BASIC;
    const AUTH_DIGEST = CURLAUTH_DIGEST;
    const AUTH_GSSNEGOTIATE = CURLAUTH_GSSNEGOTIATE;
    const AUTH_NTLM = CURLAUTH_NTLM;
    const AUTH_ANY = CURLAUTH_ANY;
    const AUTH_ANYSAFE = CURLAUTH_ANYSAFE;

    const USER_AGENT = 'PHP Curl/1.1 (+https://github.com/mod-php/curl)';

    private $_cookies = array();
    private $_headers = array();

    public $curl;

    public $error = false;
    public $error_code = 0;
    public $error_message = null;

    public $curl_error = false;
    public $curl_error_code = 0;
    public $curl_error_message = null;

    public $http_error = false;
    public $http_status_code = 0;
    public $http_error_message = null;

    public $request_headers = null;
    public $response_headers = null;
    public $response = null;

    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new \ErrorException('cURL library is not loaded');
        }

        $this->init();
    }

    /**
     * Выполняет GET запрос
     *
     * @param       $url
     * @param array $data
     */
    public function get($url, $data = array())
    {
        if (count($data) > 0) {
            $this->setopt(CURLOPT_URL, $url . '?' . http_build_query($data));
        } else {
            $this->setopt(CURLOPT_URL, $url);
        }
        $this->setopt(CURLOPT_HTTPGET, true);
        $this->_exec();
    }

    /**
     * Выполняет POST запрос
     *
     * @param       $url
     * @param array $data
     */
    public function post($url, $data = array())
    {
        $this->setopt(CURLOPT_URL, $url);
        $this->setopt(CURLOPT_POST, true);
        if (is_array($data) || is_object($data)) {
            $data = http_build_query($data);
        }
        $this->setopt(CURLOPT_POSTFIELDS, $data);
        $this->_exec();
    }

    /**
     * Выполняет PATCH запрос
     *
     * @param       $url
     * @param array $data
     */
    public function patch($url, $data = array())
    {
        $this->setopt(CURLOPT_URL, $url);
        $this->setopt(CURLOPT_CUSTOMREQUEST, 'PATCH');
        $this->setopt(CURLOPT_POSTFIELDS, http_build_query($data));
        $this->_exec();
    }

    /**
     * Выполняет DELETE запрос
     *
     * @param       $url
     * @param array $data
     */
    public function delete($url, $data = array())
    {
        $this->setopt(CURLOPT_URL, $url);
        $this->setopt(CURLOPT_POSTFIELDS, http_build_query($data));
        $this->setopt(CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->_exec();
    }

    /**
     * Basic авторизация
     *
     * @param $username
     * @param $password
     */
    public function setBasicAuthentication($username, $password)
    {
        $this->setHttpAuth(self::AUTH_BASIC);
        $this->setopt(CURLOPT_USERPWD, $username . ':' . $password);
    }

    protected function setHttpAuth($httpauth)
    {
        $this->setOpt(CURLOPT_HTTPAUTH, $httpauth);
    }

    public function setHeader($key, $value)
    {
        $this->_headers[$key] = $key . ': ' . $value;
        $this->setopt(CURLOPT_HTTPHEADER, array_values($this->_headers));
    }

    public function setUserAgent($user_agent)
    {
        $this->setopt(CURLOPT_USERAGENT, $user_agent);
    }

    public function setReferrer($referrer)
    {
        $this->setopt(CURLOPT_REFERER, $referrer);
    }

    public function setCookie($key, $value)
    {
        $this->_cookies[$key] = $value;
        $this->setopt(CURLOPT_COOKIE, http_build_query($this->_cookies, '', '; '));
    }

    public function setOpt($option, $value)
    {

        return curl_setopt($this->curl, $option, $value);
    }

    public function verbose($on = true)
    {
        $this->setopt(CURLOPT_VERBOSE, $on);
    }

    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    public function reset()
    {
        $this->close();
        $this->_cookies = array();
        $this->_headers = array();
        $this->error = false;
        $this->error_code = 0;
        $this->error_message = null;
        $this->curl_error = false;
        $this->curl_error_code = 0;
        $this->curl_error_message = null;
        $this->http_error = false;
        $this->http_status_code = 0;
        $this->http_error_message = null;
        $this->request_headers = null;
        $this->response_headers = null;
        $this->response = null;
        $this->init();
    }

    private function _exec()
    {
        $this->response = curl_exec($this->curl);
        $this->curl_error_code = curl_errno($this->curl);
        $this->curl_error_message = curl_error($this->curl);
        $this->curl_error = !($this->curl_error_code === 0);
        $this->http_status_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $this->http_error = in_array(floor($this->http_status_code / 100), array(4, 5));
        $this->error = $this->curl_error || $this->http_error;
        $this->error_code = $this->error ? ($this->curl_error ? $this->curl_error_code : $this->http_status_code) : 0;

        $this->request_headers = preg_split('/\r\n/', curl_getinfo($this->curl, CURLINFO_HEADER_OUT), null, PREG_SPLIT_NO_EMPTY);
        $this->response_headers = '';
        if (!(strpos($this->response, "\r\n\r\n") === false)) {
            list($response_header, $this->response) = explode("\r\n\r\n", $this->response, 2);
            if ($response_header === 'HTTP/1.1 100 Continue') {
                list($response_header, $this->response) = explode("\r\n\r\n", $this->response, 2);
            }
            $this->response_headers = preg_split('/\r\n/', $response_header, null, PREG_SPLIT_NO_EMPTY);
        }

        $this->http_error_message = $this->error ? (isset($this->response_headers['0']) ? $this->response_headers['0'] : '') : '';
        $this->error_message = $this->curl_error ? $this->curl_error_message : $this->http_error_message;

        return $this->error_code;
    }

    public function __destruct()
    {
        $this->close();
    }

    private function init()
    {
        $this->curl = curl_init();
        $this->setUserAgent(self::USER_AGENT);
        $this->setopt(CURLINFO_HEADER_OUT, true);
        $this->setopt(CURLOPT_HEADER, true);
        $this->setopt(CURLOPT_RETURNTRANSFER, true);
    }
}