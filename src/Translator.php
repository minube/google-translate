<?php
namespace Minube\GoogleTranslate;

use Net_Http_Client;

class Translator
{
    const PLURAL_SEPARATOR = '|';

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var ParametersEscaper
     */
    protected $parametersEscaper;

    /**
     * @var Net_Http_Client
     */
    protected $client;

    /**
     * @var string
     */
    private $environment;

    /**
     * Class constructor
     * @param string $apiKey
     * @param ParametersEscaper $parametersEscaper
     */
    public function __construct($apiKey, ParametersEscaper $parametersEscaper)
    {
        $this->apiKey = $apiKey;
        $this->parametersEscaper = $parametersEscaper;

        $this->client = new Net_Http_Client();
        $this->client->setHeader('X-HTTP-Method-Override', 'GET');
    }

    /**
     * Translate string
     * @param string $message
     * @param string $langFrom
     * @param string $langTo
     * @return string
     */
    public function translate($message, $langFrom, $langTo)
    {
        if ($this->isPluralization($message)) {
            $parts = explode(self::PLURAL_SEPARATOR, $message);
            $pluralizationMessages = array();

            foreach ($parts as $part) {
                if (preg_match('/(^\w+\:)(.*?$)/', $part, $matches)) {
                    $pluralizationMessages[] = $matches[1] . $this->translateString($matches[2], $langFrom, $langTo);
                } else {
                    $pluralizationMessages[] = $this->translateString($part, $langFrom, $langTo);
                }
            }

            return implode(self::PLURAL_SEPARATOR, $pluralizationMessages);
        } else {
            return $this->translateString($message, $langFrom, $langTo);
        }
    }

    /**
     * @param string $string
     * @param string $langFrom
     * @param string $langTo
     * @return string translated from langFrom to langTo
     */
    public function translateString($string, $langFrom, $langTo)
    {
        $stringEscaped = $this->parametersEscaper->escapeParameters($string);

        $postBody = array(
            'key' => $this->apiKey,
            'q' => $stringEscaped,
            'source' => $langFrom,
            'target' => $langTo,
        );

        $this->client->post('https://www.googleapis.com/language/translate/v2', $postBody);
        $response = $this->client->getResponse();

        $responseArray = json_decode($response->getBody(), true);
        $translatedString = $responseArray['data']['translations']['0']['translatedText'];

        $string = html_entity_decode($translatedString);
        $string = str_replace('&#39;', '"', $string);
        $string = $this->parametersEscaper->unEscapeParameters($string);

        return $string;
    }

    /**
     * Check if is plural (separated by |)
     * @param string $string
     * @return bool
     */
    protected function isPluralization($string)
    {
        return count(explode(self::PLURAL_SEPARATOR, $string)) > 1 ? true : false;
    }
}
