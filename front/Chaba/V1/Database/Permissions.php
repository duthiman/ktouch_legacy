<?php
namespace KTouch\Modules\Chaba\V1\Database;
use PDO;
final class Permissions {
    public static function migrate(PDO $pdo): void {
        $pdo->exec("INSERT IGNORE INTO kp_permission_groups(group_key,group_name,is_active,created_at,updated_at) VALUES('chaba','Chaba Print Queue',1,NOW(),NOW())");
        $group=(int)$pdo->query("SELECT group_id FROM kp_permission_groups WHERE group_key='chaba'")->fetchColumn();
        $insert=$pdo->prepare('INSERT IGNORE INTO kp_permissions(group_id,permission_key,permission_name,is_active,created_at,updated_at) VALUES(?,?,?,1,NOW(),NOW())');
        foreach(require dirname(__DIR__).'/routes.php' as $action) $insert->execute([$group,'chaba.'.$action,'Chaba '.str_replace('.',' ',$action)]);
        $pdo->exec("INSERT IGNORE INTO kp_role_permissions(role_id,permission_id,is_allowed,created_at,updated_at) SELECT r.role_id,p.permission_id,1,NOW(),NOW() FROM kp_roles r JOIN kp_permissions p ON p.permission_key LIKE 'chaba.%' WHERE r.role_name='superadmin'");
    }
}
