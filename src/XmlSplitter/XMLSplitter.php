<?php

namespace XmlSplitter;

use XMLReader;

class XMLSplitter {

    /**
     * @var XMLReader;
     */
    protected $reader;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $outputFolder;

    /**
     * @var string
     */
    protected $nameByTag;

    /**
     * @var string
     */
    protected $nameByAttribute;

    protected $fileCount = 0;

    public function __construct(XMLReader $reader, $file)
    {
        $this->reader = $reader;
        $this->file = $file;
    }

    public function split($tag)
    {
        if (!file_exists($this->file)) {
            var_dump($this->file);
            throw new \Exception('The given XML File does not exist');
        }

        $this->checkAndCreateOutputFolder();
        $this->reader->open($this->file);
        while ($this->rFind($tag)) {
            $this->writeOutput();
        }

    }

    protected function writeOutput()
    {
        $xml = $this->createSimpleXmlElement();

        $filename = $this->getOutputFolder() . '/' . $this->getOutputFileName($xml) . '.xml';
        file_put_contents($filename, $xml->saveXML());
    }

    protected function createSimpleXmlElement()
    {
        $dom = new \DomDocument();
        $n = $dom->importNode($this->reader->expand(), true);
        $dom->appendChild($n);

        return simplexml_import_dom($n);
    }

    protected function getOutputFileName(\SimpleXMLElement $xml)
    {
        $filename = $this->fileCount++;
        if (!is_null($this->getNameByTag()) && is_null($this->getNameByAttribute())) {
            $tag = $this->getNameByTag();
            $filename = (string) $xml->$tag;
        }
        else if (is_null($this->getNameByTag()) && !is_null($this->getNameByAttribute())) {
            $attribute = $this->getNameByAttribute();
            $filename = (string) $xml->attributes()->$attribute;
        }
        else if (!is_null($this->getNameByTag()) && !is_null($this->getNameByAttribute())) {
            $tag = $this->getNameByTag();
            $attribute = $this->getNameByAttribute();
            $filename = (string) $xml->$tag->attributes()->$attribute;
        }

        return $filename;
    }

    /**
     * check if the OutputFolder exists and if not it will create it
     *
     * @return XMLSplitter
     */
    protected function checkAndCreateOutputFolder()
    {
        if (!file_exists($this->getOutputFolder())) {
            mkdir($this->getOutputFolder());
        }


        return $this;
    }

    /**
     * find next start tag with a certain name (performance optimized)
     *
     * @param string $tag
     * @return boolean
     */
    protected  function rFind($tag) {
        $read_success = null;
        while (
            ($read_success = $this->reader->read()) &&
            !(XMLReader::ELEMENT === $this->reader->nodeType && $tag === $this->reader->name)
            && !(XMLReader::NONE === $this->reader->nodeType)
        ) {
            continue;
        };

        return $read_success && $tag === $this->reader->name;
    }

    /**
     * @param string $file
     *
     * @return XMLSplitter
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $outputFolder
     *
     * @return XMLSplitter
     */
    public function setOutputFolder($outputFolder)
    {
        $this->outputFolder = $outputFolder;

        return $this;
    }

    /**
     * @return string
     */
    public function getOutputFolder()
    {
        if (is_null($this->outputFolder)) {
            $this->outputFolder = __DIR__ . '/../../output';
        }

        return $this->outputFolder;
    }

    /**
     * @param \XMLReader $reader
     *
     * @return XMLSplitter
     */
    public function setReader($reader)
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * @return \XMLReader
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @param string $nameByAttribute
     */
    public function setNameByAttribute($nameByAttribute)
    {
        $this->nameByAttribute = $nameByAttribute;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameByAttribute()
    {
        return $this->nameByAttribute;
    }

    /**
     * @param string $nameByTag
     */
    public function setNameByTag($nameByTag)
    {
        $this->nameByTag = $nameByTag;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameByTag()
    {
        return $this->nameByTag;
    }
}
