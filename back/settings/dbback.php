<?php
include("../../inc/connection.php");
include("../clude/funcs.php");

// --- Configuration ---
define('BACKUP_DIR',    realpath(__DIR__ . '/../../assets/back/db') . DIRECTORY_SEPARATOR);
define('MYSQLDUMP_BIN', 'C:/Program Files/Ampps/mysql/bin/mysqldump.exe');
define('MAX_SLOTS',     5);

/**
 * Create a database backup using mysqldump and maintain a rolling 5-slot rotation.
 * Returns an array ['ok' => bool, 'msg' => string].
 */
function do_backup() {
    global $connection;

    if (!is_dir(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 0755, true);
    }

    // Count existing slots
    $cnt_res = mysqli_query($connection, "SELECT COUNT(*) AS c FROM kp_backdb");
    $cnt     = (int) mysqli_fetch_assoc($cnt_res)['c'];

    $now_label = date('YMd_His');                  // e.g. 2026Apr07_143022
    $now_dt    = date('Y-m-d H:i:s');

    if ($cnt < MAX_SLOTS) {
        // Fill a new slot  (slot index = cnt + 1)
        $slot        = $cnt + 1;
        $filename    = 'backup_slot_' . $slot . '.sql';
        $filepath    = BACKUP_DIR . $filename;
        $overwrite   = false;
    } else {
        // Overwrite the oldest slot
        $old_res   = mysqli_query($connection,
            "SELECT back_id, backupfilename FROM kp_backdb ORDER BY backupfiledate ASC LIMIT 1");
        $old_row   = mysqli_fetch_assoc($old_res);
        $slot      = $old_row['back_id'];
        $filename  = $old_row['backupfilename'];  // reuse the same slot filename
        $filepath  = BACKUP_DIR . $filename;
        $overwrite = true;

        // Delete the old physical file if present
        if (file_exists($filepath)) {
            @unlink($filepath);
        }
    }

    // Run mysqldump
    global $dbhost, $dbuser, $dbpass, $dbname;
    $host = $dbhost ?? 'localhost';
    $user = $dbuser ?? 'root';
    $pass = $dbpass ?? 'mysql';
    $db   = $dbname ?? 'ktouch_gpints';

    // Use a temporary options file so the password is never on the command line
    $optfile = tempnam(sys_get_temp_dir(), 'mysqldump_');
    file_put_contents($optfile,
        "[mysqldump]\nuser={$user}\npassword={$pass}\nhost={$host}\n");

    $cmd     = '"' . MYSQLDUMP_BIN . '"'
             . ' --defaults-extra-file="' . $optfile . '"'
             . ' --single-transaction --routines --triggers'
             . ' ' . escapeshellarg($db)
             . ' > "' . $filepath . '" 2>&1';

    $output  = null;
    $retcode = null;
    exec($cmd, $output, $retcode);
    @unlink($optfile);

    if ($retcode !== 0 || !file_exists($filepath) || filesize($filepath) < 100) {
        return ['ok' => false, 'msg' => 'mysqldump failed. ' . implode(' ', (array)$output)];
    }

    // Persist to kp_backdb
    $user_id  = $_SESSION['mwene'] ?? 0;
    $ip       = mysqli_real_escape_string($connection, $_SERVER['REMOTE_ADDR'] ?? '');
    $filename = mysqli_real_escape_string($connection, $filename);
    $now_dt   = mysqli_real_escape_string($connection, $now_dt);

    if ($overwrite) {
        mysqli_query($connection,
            "UPDATE kp_backdb
             SET backupfilename='{$filename}', backupfiledate='{$now_dt}',
                 username='{$user_id}', ipaddress='{$ip}'
             WHERE back_id={$slot}");
    } else {
        mysqli_query($connection,
            "INSERT INTO kp_backdb (backupfilename, backupfiledate, username, ipaddress)
             VALUES ('{$filename}', '{$now_dt}', '{$user_id}', '{$ip}')");
    }

    // Prune any DB rows beyond MAX_SLOTS (safety net)
    mysqli_query($connection,
        "DELETE FROM kp_backdb
         WHERE back_id NOT IN (
             SELECT back_id FROM (
                 SELECT back_id FROM kp_backdb ORDER BY backupfiledate DESC LIMIT " . MAX_SLOTS . "
             ) t
         )");

    return ['ok' => true, 'msg' => "Backup saved as {$filename}"];
}

$result = do_backup();
echo $result['ok'] ? 'OK' : 'ERROR: ' . htmlspecialchars($result['msg']);


//http://davidwalsh.name/backup-mysqli-database-php
// date_default_timezone_set('Asia/Calcutta'); 

// //Values From db_connect.php file.
// $localhost = $hostname;
// $dbusername = $hostlogin;
// $password = $hostpassword;
// $databasename = $databasename;

// backup_tables($localhost, $dbusername, $password, $databasename);

// /* backup the db OR just a table */
// function backup_tables($host,$user,$pass,$name,$tables = '*')
// {
// 	$backuptime = date('YMd_His');
// 	$backupdatabasefiletime = date('Y-m-d H:i:s');
// 	$username = $_SESSION["username"];
// 	$ipaddress = $_SERVER["REMOTE_ADDR"];
	
// 	$backupdatabasefilename = 'DB_Backup_'.$backuptime.'.sql';
// 	$backupdatabasefiletime =  $backupdatabasefiletime;
	
// 	$query1db = "insert into master_backupdatabase (backupfilename, backupfiledate, username, ipaddress) 
// 	values ('$backupdatabasefilename', '$backupdatabasefiletime', '$username', '$ipaddress')";
	
// 	$link = mysqli_connect($host,$user,$pass);
// 	mysqli_select_db($link,$name);

// 	$exec1db = mysqli_query($link,$query1db) or die ("Error in Query1db".mysqli_error($link));

// 	$return = '';
	
	
// 	//get all of the tables
// 	if($tables == '*')
// 	{
// 		$tables = array();
// 		$result = mysqli_query($link, 'SHOW TABLES');
// 		while($row = mysqli_fetch_row($result))
// 		{
// 			$tables[] = $row[0];
// 		}
// 	}
// 	else
// 	{
// 		$tables = is_array($tables) ? $tables : explode(',',$tables);
// 	}
	
// 	//cycle through
// 	foreach($tables as $table)
// 	{
// 		$result = mysqli_query($link, 'SELECT * FROM '.$table);
// 		$num_fields = mysqli_num_fields($result);
		
// 		//$return.= 'DROP TABLE '.$table.';';
// 		$row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE '.$table));
// 		$return.= "\n\n".$row2[1].";\n\n";
		
// 		for ($i = 0; $i < $num_fields; $i++) 
// 		{
// 			while($row = mysqli_fetch_row($result))
// 			{
// 				$return.= 'INSERT INTO '.$table.' VALUES(';
// 				for($j=0; $j<$num_fields; $j++) 
// 				{
// 					$row[$j] = addslashes($row[$j]);
					
// 					//Slash after and before double quote is compulsory.
// 					$patterns = "/\n/";
// 					$replacements = "/\\n/";
// 					$string = $row[$j]; 
					
// 					//$row[$j] = preg_replace("\n","\\n",$row[$j]); 
// 					$row[$j] = preg_replace($patterns, $replacements, $string);
					
// 					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
// 					if ($j<($num_fields-1)) { $return.= ','; }
// 				}
// 				$return.= ");\n";
// 			}
// 		}
// 		$return.="\n\n\n";
// 	}
	
// 	//$backuptime = date('YMd_His');
// 	//$backupdatabasefiletime = date('Y-m-d H:i:s');
// 	//$username = $_SESSION["username"];
// 	//$ipaddress = $_SERVER["REMOTE_ADDR"];

// 	//save file
// 	//$handle = fopen('db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
	
// 	$handle = fopen('zbackupdatabasefiles/DB_Backup_'.$backuptime.'.sql','w+'); //z given to list folders at the end.
// 	fwrite($handle,$return);
// 	fclose($handle);
	
// 	//$backupdatabasefilename = 'DB_Backup_'.$backuptime.'.sql';
// 	//$backupdatabasefiletime =  $backupdatabasefiletime;
	
// 	//$query1db = "insert into master_backupdatabase (backupfilename, backupfiledate, username, ipaddress) 
// 	//values ('$backupdatabasefilename', '$backupdatabasefiletime', '$username', '$ipaddress')";
// 	//$exec1db = mysqli_query($link, $query1db) or die ("Error in Query1db".mysqli_error());
	
// }


backup_tables();

?>