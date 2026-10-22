<?php
$templateType = 'GOT';
$defaultSections = ['title','header','items'];
$document['document_type'] = $document['document_type'] ?? $templateType;
$document['title'] = $document['title'] ?? 'GOODS ORDER TICKET';
require __DIR__ . '/../template.php';
