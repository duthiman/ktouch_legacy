<?php $templateType='SRT';
$defaultSections=['title','header','items','summary','advertising'];$document['document_type']=$document['document_type']??$templateType;$document['title']=$document['title']??(strtoupper((string)($document['document_type']??''))==='RETURN_RECEIPT'?'RETURN RECEIPT':'STOCK REQUEST TICKET');require __DIR__.'/../template.php';
