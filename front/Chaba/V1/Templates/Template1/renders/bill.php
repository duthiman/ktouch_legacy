<?php

$templateType = 'BILL';

$defaultSections = [
    'title',
    'logo',
    'header',
    'items',
    'tax',
    'thank_you',
    'paybill', 
    'advertising',
];

$document['document_type'] = $document['document_type'] ?? $templateType;
$document['title'] = $document['title'] ?? 'BILL';

require __DIR__ . '/../template.php';
