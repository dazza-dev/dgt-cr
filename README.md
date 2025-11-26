# DGT Costa Rica 🇨🇷

Paquete para generar, firmar y enviar documentos electrónicos (Factura, Nota crédito, Nota débito y Tiquete Electrónico) al Ministerio de Hacienda (Costa Rica).

## Instalación

```bash
composer require dazza-dev/dgt-cr
```

## Configuración

```php
use DazzaDev\DgtCr\Client;

$client = new Client(test: true); // true or false

// Configurar el certificado y la clave privada
$client->setCertificate([
    'path' => __DIR__ . '/certificado.p12',
    'password' => 'clave_certificado',
]);

// Configurar las credenciales
$client->setCredentials([
    'username' => 'usuario',
    'password' => 'clave_api',
]);

// Configurar call back url
$client->setCallbackUrl('https://tu-dominio.com/callback');

// Ruta donde se guardarán los archivos xml
$client->setFilePath(__DIR__ . '/documentos');
```

## Uso

### Configurar el emisor y receptor

Antes de enviar un documento, debes configurar el emisor y receptor. Esto se puede hacer con los métodos `setIssuer` y `setReceiver`.

```php
// Emisor
$client->setIssuer([
    'identification_type' => '02',
    'identification_number' => 'identificacion_emisor',
]);

// Receptor
$client->setReceiver([
    'identification_type' => '02',
    'identification_number' => 'identificacion_receptor',
]);
```

### Enviar un documento electrónico

Para enviar un documento electrónico como Factura, Nota crédito, Nota débito o Tiquete Electrónico. primero debes pasar la estructura de datos que puedes encontrar en: [dazza-dev/dgt-xml-generator](https://github.com/dazza-dev/dgt-xml-generator).

### Ejemplo de uso (Factura)

```php
// Usar el valor en inglés de la tabla
$client->setDocumentType('invoice');

// Datos del documento
$client->setDocumentData($documentData);

// Enviar el documento
$document = $client->sendDocument();
```

### Tipos de documentos disponibles

| Documento           | Valor              |
| ------------------- | ------------------ |
| Factura             | `invoice`          |
| Nota de crédito     | `credit-note`      |
| Nota de débito      | `debit-note`       |
| Tiquete Electrónico | `ticket`           |
| Mensaje Receptor    | `receiver-message` |

### Consultar estado del documento enviado

Después de enviar un documento, puedes consultar su estado usando el método `checkStatus`:

```php
$documentStatus = $client->checkStatus(
    documentKey: $clave
);
```

### Buscar un documento

Para buscar un documento debemos pasar la clave del documento que se obtiene al enviar el documento.

```php
$document = $client->getDocument(
    documentKey: $clave
);
```

### Obtener lista de documentos

Para obtener una lista de documentos electrónicos que se han enviado, puedes usar el método `getDocuments`.

```php
$documents = $client->getDocuments(
    offset: 0,
    limit: 50
);
```

### Obtener los listados

El Ministerio de hacienda de Costa Rica tiene una lista de códigos que este paquete te pone a disposición para facilitar el trabajo de consultar esto en el anexo técnico:

```php
use DazzaDev\DgtCr\Listing;

// Obtener los listados disponibles
$listings = Listing::getListings();

// Consultar los datos de un listado por tipo
$listingByType = Listing::getListing('tipos-comprobante');
```

## Contribuciones

Contribuciones son bienvenidas. Si encuentras algún error o tienes ideas para mejoras, por favor abre un issue o envía un pull request. Asegúrate de seguir las guías de contribución.

## Autor

DGT Costa Rica fue creado por [DAZZA](https://github.com/dazza-dev).

## Licencia

Este proyecto está licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).
