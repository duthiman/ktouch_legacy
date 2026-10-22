<?php
$templateType = 'BOT';
$defaultSections = ['title','header','items'];
$document['document_type'] = $document['document_type'] ?? $templateType;
$document['title'] = $document['title'] ?? 'BAR ORDER TICKET';
require __DIR__ . '/../template.php';
