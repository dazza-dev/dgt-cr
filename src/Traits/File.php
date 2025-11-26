<?php

namespace DazzaDev\DgtCr\Traits;

use DazzaDev\DgtCr\Exceptions\FileException;

trait File
{
    /**
     * File path
     */
    protected ?string $filePath = null;

    /**
     * File name
     */
    protected ?string $fileName = null;

    /**
     * File path
     */
    protected function validateFilePath()
    {
        if (is_null($this->filePath)) {
            throw new FileException('File path is not set');
        }
    }

    /**
     * Set file path
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    /**
     * Get file path
     */
    public function getFilePath()
    {
        $this->validateFilePath();

        return $this->filePath;
    }

    /**
     * Set file name
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * Get file name
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Save file
     */
    protected function saveFile(string $folder, string $fileName, string $fileContent): string
    {
        // Create directories
        $this->createDirectories();

        // Set file name
        $this->setFileName($fileName.'.xml');

        // Save signed XML document
        $filePath = $this->getFilePath().'/'.$folder.'/'.$this->getFileName();
        $file = file_put_contents($filePath, $fileContent);

        if (! $file) {
            throw new FileException('Error saving file: '.$filePath);
        }

        return $file;
    }

    /**
     * Create directories
     */
    protected function createDirectories()
    {
        $filePath = $this->getFilePath();

        // Create base directory if it doesn't exist
        if (! file_exists($filePath)) {
            mkdir($filePath, 0777, true);
            mkdir($filePath.'/invoice', 0777, true);
            mkdir($filePath.'/ticket', 0777, true);
            mkdir($filePath.'/credit-note', 0777, true);
            mkdir($filePath.'/debit-note', 0777, true);
            mkdir($filePath.'/receiver-message', 0777, true);
        }
    }
}
