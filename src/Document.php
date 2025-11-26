<?php

namespace DazzaDev\DgtCr;

use DazzaDev\DgtXmlGenerator\Factories\DocumentBuilderFactory;
use DOMDocument;

class Document
{
    /**
     * Document type
     */
    private string $documentType;

    /**
     * Document data
     */
    private array $documentData;

    /**
     * Document instance
     */
    private mixed $document;

    /**
     * Document XML
     */
    private DOMDocument $documentXml;

    /**
     * Constructor
     */
    public function __construct(string $documentType, array $documentData)
    {
        $this->setDocumentType($documentType);
        $this->setDocumentData($documentData);
        $this->buildDocument();
    }

    /**
     * Get document type
     */
    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    /**
     * Set document type
     */
    private function setDocumentType(string $documentType): void
    {
        $this->documentType = $documentType;
    }

    /**
     * Get document data
     */
    public function getDocumentData(): array
    {
        return $this->documentData;
    }

    /**
     * Set document data
     */
    private function setDocumentData(array $documentData): void
    {
        $this->documentData = $documentData;
    }

    /**
     * Build document using DocumentBuilderFactory
     */
    private function buildDocument(): void
    {
        $builder = DocumentBuilderFactory::create(
            $this->documentType,
            $this->documentData
        );

        $this->document = $builder->getDocument();
        $this->documentXml = $builder->getXml();
    }

    /**
     * Get document instance
     */
    public function getDocument(): mixed
    {
        return $this->document;
    }

    /**
     * Get document key
     */
    public function getDocumentKey(): string
    {
        return $this->document->getDocumentKey();
    }

    /**
     * Get document issue date
     */
    public function getIssueDate(): string
    {
        return $this->document->getDate();
    }

    /**
     * Get document file name
     */
    public function getDocumentFileName(): string
    {
        $fileName = $this->getDocumentKey();

        // Add sequential number for receiver messages
        if ($this->documentType === 'receiver-message') {
            $fileName .= '-'.$this->document->getReceiver()?->getSequentialNumber();
        }

        return $fileName;
    }

    /**
     * Get document XML
     */
    public function getDocumentXml(): DOMDocument
    {
        return $this->documentXml;
    }
}
