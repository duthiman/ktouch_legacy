<?php
namespace KTouch\Modules\Chaba\V1;
use KTouch\Core\Auth\AuthContext;
use KTouch\Core\Http\Request;
use KTouch\Core\Router\ModuleInterface;
use KTouch\Modules\Chaba\V1\Services\ChabaService;
final class ChabaModule implements ModuleInterface {
    public function __construct(private ChabaService $service) {}
    public function actions(): array {return require __DIR__.'/routes.php';}
    public function handle(string $action,Request $request,AuthContext $context): array {return $this->service->handle($action,$request->data(),$context);}
}
