<?php
$templateType = 'CCT';
$defaultSections = ['title','header','summary'];
$document['document_type'] = $document['document_type'] ?? $templateType;
$document['title'] = $document['title'] ?? 'CASHIER CLEARANCE TICKET';
require __DIR__ . '/../template.php';
