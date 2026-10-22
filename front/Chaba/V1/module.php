<?php
use KTouch\Core\Database\PdoFactory;
use KTouch\Core\Logging\RequestLogger;
use KTouch\Modules\Chaba\V1\ChabaModule;
use KTouch\Modules\Chaba\V1\Services\ChabaService;
use KTouch\Modules\Chaba\V1\Repositories\ChabaRepository;
return static function(PdoFactory $factory,RequestLogger $logger): ChabaModule {
    return new ChabaModule(new ChabaService(new ChabaRepository($factory,$logger)));
};
