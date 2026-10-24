<?php
declare(strict_types=1);

/* Procedural bridge for the V1 module. It deliberately uses inc/connection.php
 * as the application's database source of truth, while V1 continues to use
 * PDO internally for transactions and prepared statements. */
namespace KTouch\Core\Database { final class PdoFactory { public function create(): \PDO { global $dbhost,$dbuser,$dbpass,$dbname; return new \PDO("mysql:host={$dbhost};dbname={$dbname};charset=utf8mb4",$dbuser,$dbpass,[\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,\PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC]); } } }
namespace KTouch\Core\Logging { final class RequestLogger { public function log(array $event): void { error_log('chaba '.json_encode($event)); } } }
namespace KTouch\Core\Auth { final class AuthContext { public function __construct(private int $id){} public function userId(): int{return $this->id;} } }
namespace KTouch\Core\Http { final class Request { public function __construct(private array $data){} public function data(): array{return $this->data;} } }
namespace KTouch\Core\Router { interface ModuleInterface {} }
namespace KTouch\Core\Exceptions { class ApiException extends \RuntimeException { public function __construct(string $message,int $status=400,public string $errorCode='api'){parent::__construct($message,$status);} public function status():int{return $this->getCode()?:400;} } class ValidationException extends ApiException {} }
namespace {
    require_once dirname(__DIR__,3).'/inc/connection.php';
    $autoload = static function(string $class): void {
        $prefix='KTouch\\Modules\\Chaba\\V1\\';
        if(strncmp($class,$prefix,strlen($prefix))!==0)return;
        $relative=substr($class,strlen($prefix));
        $file=__DIR__.'/'.str_replace('\\','/',$relative).'.php';
        if(is_file($file))require_once $file;
    };
    spl_autoload_register($autoload);
    require_once __DIR__.'/module.php';
    $input=file_get_contents('php://input') ?: '';
    $body=json_decode($input,true); if(!is_array($body))$body=[];
    $data=array_replace($_GET,$body); $action=trim((string)($data['action']??'')); unset($data['action']);
    header('Content-Type: application/json; charset=utf-8');
    try {
        $factory=new \KTouch\Core\Database\PdoFactory(); $pdo=$factory->create();
        $logger=new \KTouch\Core\Logging\RequestLogger();
        \KTouch\Modules\Chaba\V1\Database\Schema::ensure($pdo,$logger);
        $module=(require __DIR__.'/module.php')($factory,$logger);
        $user=(int)($_SESSION['user_id']??$_POST['user_id']??1);
        $result=$module->handle($action,new \KTouch\Core\Http\Request($data),new \KTouch\Core\Auth\AuthContext($user));
        $jsonRequest = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST'
            && stripos((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') !== false;
        if(isset($result['html']) && in_array($action, ['next', 'render'], true) && !$jsonRequest) {
            header('Content-Type: text/html; charset=utf-8'); echo $result['html'];
        } else {
            echo json_encode(['ok'=>true,'data'=>$result],JSON_UNESCAPED_SLASHES);
        }
    } catch(\Throwable $e) { http_response_code($e->getCode()>=400&&$e->getCode()<600?$e->getCode():500); echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); }
}
