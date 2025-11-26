<?php

namespace DazzaDev\DgtCr;

use DazzaDev\DgtCr\Exceptions\DocumentException;
use DazzaDev\DgtCr\Traits\File;
use DazzaDev\DgtCrSender\Sender;
use DazzaDev\DgtCrSigner\Signer;

class Client
{
    use File;

    /**
     * Is test mode
     */
    private bool $isTest = false;

    /**
     * Document type (temporary storage)
     */
    private string $documentType;

    /**
     * Signed document
     */
    protected string $signedDocument;

    /**
     * Document instance
     */
    protected ?Document $document = null;

    /**
     * Signer
     */
    protected ?Signer $signer = null;

    /**
     * Certificate
     */
    protected array $certificate;

    /**
     * Sender
     */
    protected Sender $sender;

    /**
     * Auth Token
     */
    protected array $authToken = [];

    /**
     * Bearer Token
     */
    protected ?string $bearerToken = null;

    /**
     * Refresh Token
     */
    protected ?string $refreshToken = null;

    /**
     * Callback URL
     */
    protected ?string $callbackUrl = null;

    /**
     * Issuer
     */
    protected array $issuer = [];

    /**
     * Receiver
     */
    protected array $receiver = [];

    /**
     * Constructor
     */
    public function __construct(bool $test = false)
    {
        $this->isTest = $test;

        // Initialize Sender
        $this->sender = new Sender;

        // Set test mode
        if ($this->isTest) {
            $this->sender->setTestMode(true);
        }
    }

    /**
     * Set the environment to test mode
     */
    public function setTestMode(bool $isTest = true): self
    {
        $this->isTest = $isTest;

        return $this;
    }

    /**
     * Is test mode
     */
    protected function isTestMode(): bool
    {
        return $this->isTest;
    }

    /**
     * Set callback url
     */
    public function setCallbackUrl(?string $callbackUrl): self
    {
        $this->callbackUrl = $callbackUrl;

        // Set callback url in sender
        $this->sender->setCallbackUrl($this->callbackUrl);

        return $this;
    }

    /**
     * Get callback url
     */
    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }

    /**
     * Set issuer
     */
    public function setIssuer(array $issuer): self
    {
        $this->issuer = $issuer;

        // Set issuer in sender
        $this->sender->setIssuer($this->issuer);

        return $this;
    }

    /**
     * Get issuer
     */
    public function getIssuer(): array
    {
        return $this->issuer;
    }

    /**
     * Set receiver
     */
    public function setReceiver(array $receiver): self
    {
        $this->receiver = $receiver;

        // Set receiver in sender
        $this->sender->setReceiver($this->receiver);

        return $this;
    }

    /**
     * Get receiver
     */
    public function getReceiver(): array
    {
        return $this->receiver;
    }

    /**
     * Set credentials
     */
    public function setCredentials(array $credentials): void
    {
        // Authenticate client
        $this->authToken = $this->sender->auth(
            username: $credentials['username'],
            password: $credentials['password']
        );

        $this->setBearerToken($this->authToken['access_token']);
        $this->setRefreshToken($this->authToken['refresh_token']);
    }

    /**
     * Get bearer token
     */
    public function getBearerToken(): ?string
    {
        return $this->bearerToken;
    }

    /**
     * Set bearer token
     */
    public function setBearerToken(?string $bearerToken): self
    {
        $this->bearerToken = $bearerToken;

        return $this;
    }

    /**
     * Get refresh token
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Set refresh token
     */
    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Set certificate
     */
    public function setCertificate(array $certificate): void
    {
        $this->certificate = $certificate;

        // Set Signer
        $this->signer = new Signer(
            certificatePath: $this->certificate['path'],
            certificatePassword: $this->certificate['password']
        );
    }

    /**
     * Get document type
     */
    public function getDocumentType(): string
    {
        return $this->document?->getDocumentType();
    }

    /**
     * Set document type
     */
    public function setDocumentType(string $documentType): void
    {
        $this->documentType = $documentType;
    }

    /**
     * Get document key
     */
    public function getDocumentKey(): string
    {
        return $this->document?->getDocumentKey();
    }

    /**
     * Get document issue date
     */
    public function getIssueDate(): string
    {
        return $this->document?->getIssueDate();
    }

    /**
     * Get document file name
     */
    public function getDocumentFileName(): string
    {
        return $this->document?->getDocumentFileName();
    }

    /**
     * Set document data
     */
    public function setDocumentData(array $documentData): void
    {
        $this->document = new Document(
            $this->documentType,
            $documentData
        );
    }

    /**
     * Get documents
     */
    public function getDocuments(int $offset = 0, int $limit = 50): array
    {
        return $this->sender->getDocuments(
            offset: $offset,
            limit: $limit
        );
    }

    /**
     * Get document by key
     */
    public function getDocumentByKey(string $documentKey): array
    {
        $document = $this->sender->getDocument(
            documentKey: $documentKey
        );

        return $document;
    }

    /**
     * Sign document
     */
    public function signDocument(): string
    {
        if (! $this->document) {
            throw new DocumentException('Documento no establecido. Llama a setDocumentData() primero.');
        }

        if (! $this->signer) {
            throw new DocumentException('Certificado no establecido. Llama a setCertificate() primero.');
        }

        // Validate file path
        $this->validateFilePath();

        // Document XML
        $xml = $this->document->getDocumentXml();

        // Sign document
        $this->signedDocument = $this->signer->loadXML($xml)->sign();

        // Save document
        $this->saveFile(
            $this->documentType,
            $this->getDocumentFileName(),
            $this->signedDocument
        );

        return $this->signedDocument;
    }

    /**
     * Send document
     */
    public function sendDocument(): array
    {
        if (! $this->document) {
            throw new DocumentException('Document not set. Call setDocumentData() first.');
        }

        // Sign document
        $this->signDocument();

        // Send document
        return $this->sender->send(
            $this->documentType,
            $this->getDocumentKey(),
            $this->getIssueDate(),
            base64_encode($this->signedDocument)
        );
    }

    /**
     * Check document status
     */
    public function checkStatus(string $documentKey): array
    {
        $document = $this->sender->checkStatus(
            documentKey: $documentKey
        );

        return $document;
    }

    /**
     * Check document status with retry
     */
    public function checkStatusWithRetry(string $documentKey, int $maxAttempts = 3, int $delaySeconds = 3): array
    {
        $lastStatus = null;
        $lastDocumentKey = null;
        $lastDate = null;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $documentStatus = $this->sender->checkStatus(
                documentKey: $documentKey
            );

            // Check document status
            $status = $documentStatus['ind-estado'] ?? null;
            $documentKey = $documentStatus['clave'] ?? null;
            $date = $documentStatus['fecha'] ?? null;

            // If document is rejected or accepted, return immediately
            if ($status === 'rechazado' || $status === 'aceptado') {
                $responseXml = isset($documentStatus['respuesta-xml']) ? base64_decode($documentStatus['respuesta-xml']) : null;

                $this->saveFile(
                    $this->documentType,
                    $this->getDocumentFileName().'_respuesta',
                    $responseXml
                );

                return [
                    'success' => $status === 'aceptado',
                    'status' => $status,
                    'document_key' => $documentKey,
                    'date' => $date,
                    'response_xml' => $responseXml,
                    'messages' => $this->extractMessages($responseXml),
                ];
            }

            if ($status !== 'recibido' && $status !== 'procesando') {
                return [
                    'success' => false,
                    'status' => $status ?? '',
                    'document_key' => $documentKey,
                    'date' => $date,
                    'response_xml' => null,
                    'messages' => null,
                ];
            }

            $lastStatus = $status;
            $lastDocumentKey = $documentKey;
            $lastDate = $date;

            if ($attempt < $maxAttempts - 1) {
                sleep($delaySeconds);
            }
        }

        return [
            'success' => false,
            'status' => $lastStatus ?? '',
            'document_key' => $lastDocumentKey,
            'date' => $lastDate,
            'response_xml' => null,
            'messages' => null,
        ];
    }

    /**
     * Extract messages from response XML
     */
    public function extractMessages(string $responseXml): ?string
    {
        $xml = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);
        $namespaces = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('mh', $namespaces['']);

        // Extract DetalleMensaje
        $messages = $xml->xpath('//mh:DetalleMensaje')[0] ?? null;

        return html_entity_decode((string) $messages);
    }
}
