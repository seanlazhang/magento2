<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Pack;

use Magento\Tools\I18n\Dictionary;
use Magento\Tools\I18n\Factory;
use Magento\Tools\I18n\Pack;

/**
 * Pack generator
 */
class Generator
{
    /**
     * Dictionary loader
     *
     * @var \Magento\Tools\I18n\Dictionary\Loader\FileInterface
     */
    protected $dictionaryLoader;

    /**
     * Pack writer
     *
     * @var \Magento\Tools\I18n\Pack\WriterInterface
     */
    protected $packWriter;

    /**
     * Domain abstract factory
     *
     * @var \Magento\Tools\I18n\Factory
     */
    protected $factory;

    /**
     * Loader construct
     *
     * @param \Magento\Tools\I18n\Dictionary\Loader\FileInterface $dictionaryLoader
     * @param \Magento\Tools\I18n\Pack\WriterInterface $packWriter
     * @param \Magento\Tools\I18n\Factory $factory
     */
    public function __construct(
        Dictionary\Loader\FileInterface $dictionaryLoader,
        Pack\WriterInterface $packWriter,
        Factory $factory
    ) {
        $this->dictionaryLoader = $dictionaryLoader;
        $this->packWriter = $packWriter;
        $this->factory = $factory;
    }

    /**
     * Generate language pack
     *
     * @param string $dictionaryPath
     * @param string $packPath
     * @param string $locale
     * @param string $mode One of const of WriterInterface::MODE_
     * @param bool $allowDuplicates
     * @return void
     * @throws \RuntimeException
     */
    public function generate(
        $dictionaryPath,
        $packPath,
        $locale,
        $mode = WriterInterface::MODE_REPLACE,
        $allowDuplicates = false
    ) {
        $locale = $this->factory->createLocale($locale);
        $dictionary = $this->dictionaryLoader->load($dictionaryPath);

        if (!count($dictionary->getPhrases())) {
            throw new \UnexpectedValueException('No phrases have been found by the specified path.');
        }

        if (!$allowDuplicates && ($duplicates = $dictionary->getDuplicates())) {
            throw new \RuntimeException(
                "Duplicated translation is found, but it is not allowed.\n"
                . $this->createDuplicatesPhrasesError($duplicates)
            );
        }

        $this->packWriter->write($dictionary, $packPath, $locale, $mode);
    }

    /**
     * Get duplicates error
     *
     * @param array $duplicates
     * @return string
     */
    protected function createDuplicatesPhrasesError($duplicates)
    {
        $error = '';
        foreach ($duplicates as $phrases) {
            /** @var \Magento\Tools\I18n\Dictionary\Phrase $phrase */
            $phrase = $phrases[0];
            $error .= sprintf(
                "The phrase \"%s\" is translated in %d places.\n",
                $phrase->getPhrase(),
                count($phrases)
            );
        }
        return $error;
    }
}
