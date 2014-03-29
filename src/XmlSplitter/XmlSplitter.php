<?php

namespace XmlSplitter;

use XMLReader;

class XmlSplitter {

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

    /**
     * @var int
     */
    protected $fileCount = 1;

    /**
     * @param XMLReader $reader
     * @param string    $file
     */
    public function __construct(XMLReader $reader, $file)
    {
        $this->reader = $reader;
        $this->setFile($file);

        $filename =  basename($this->getFile(), '.xml');
        $this->outputFolder = sys_get_temp_dir() . '/xml_splitter_output/' . $filename;
    }

    /**
     * @param string $tag
     * @throws \Exception
     */
    public function split($tag)
    {
        if (!file_exists($this->file)) {
            throw new \Exception('The given XML File does not exist');
        }

        $this->checkAndCreateOutputFolder();
        $this->reader->open($this->file);
        while ($this->rFind($tag)) {
            $this->writeOutput();
        }

    }

    /**
     * write the output
     */
    protected function writeOutput()
    {
        $xml = $this->createSimpleXmlElement();

        $filename = $this->getOutputFolder() . '/' . $this->getOutputFileName($xml) . '.xml';
        file_put_contents($filename, $xml->saveXML());
    }

    /**
     * create a SimpleXMLElement with the value of the current position of the XMLReader
     *
     * @return \SimpleXMLElement
     */
    protected function createSimpleXmlElement()
    {
        $dom = new \DomDocument();
        $n = $dom->importNode($this->reader->expand(), true);
        $dom->appendChild($n);

        return simplexml_import_dom($n);
    }

    /**
     * return the output file name
     *
     * @param \SimpleXMLElement $xml
     * @return string
     */
    protected function getOutputFileName(\SimpleXMLElement $xml)
    {
        $filename = (string) $this->getFileCount();
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

        $this->increaseFileCount();

        return $filename;
    }

    /**
     * check if the OutputFolder exists and if not it will create it
     *
     * @return XmlSplitter
     */
    protected function checkAndCreateOutputFolder()
    {
        if (!file_exists($this->getOutputFolder())) {
            mkdir($this->getOutputFolder(), 0755, true);
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
     * @return XmlSplitter
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
     * @return XmlSplitter
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
        return $this->outputFolder;
    }

    /**
     * @param \XMLReader $reader
     *
     * @return XmlSplitter
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
     *
     * @return XmlSplitter
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
     *
     * @return XmlSplitter
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

    /**
     * @param int $fileCount
     *
     * @return XmlSplitter
     */
    public function increaseFileCount($add = 1)
    {
        $this->fileCount = $this->fileCount + $add;

        return $this;
    }

    /**
     * @return int
     */
    public function getFileCount()
    {
        return $this->fileCount;
    }
}
