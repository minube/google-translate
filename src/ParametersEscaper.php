<?php
namespace Minube\GoogleTranslate;

class ParametersEscaper
{
    /** @var \ArrayObject */
    protected $parametersArray;

    /** @var  \ArrayIterator */
    protected $iterator;

    /**
     * Escape parameters
     * @param string $string
     * @return mixed
     */
    public function escapeParameters($string)
    {
        $this->parametersArray = new \ArrayObject();

        return preg_replace_callback(
            "|%[\S]*%|",
            array($this, 'escapeParametersCallback'),
            $string);
    }

    /**
     * Unescape parameters
     * @param string $string
     * @return mixed
     * @throws \Exception
     */
    public function unEscapeParameters($string)
    {
        if (!$this->parametersArray) {
            throw new \Exception('You try unescape string that not be escaped');
        }

        $this->iterator = $this->parametersArray->getIterator();

        return preg_replace_callback(
            "|NotTranslatedString|",
            array($this, 'unEscapeParametersCallback'),
            $string);
    }

    /**
     * @param array $matches
     * @return string
     */
    private function escapeParametersCallback($matches)
    {
        $this->parametersArray->append($matches['0']);

        return 'NotTranslatedString';
    }

    /**
     * @param array $matches
     * @return mixed
     */
    private function unEscapeParametersCallback($matches)
    {
        $value = $this->iterator->current();
        $this->iterator->next();

        return $value;
    }
}
