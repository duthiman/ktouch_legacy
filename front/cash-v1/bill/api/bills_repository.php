<?php

if (!function_exists('bills_repository_boot')) {
    function bills_repository_boot($connection) {
        require_once __DIR__ . '/bills_schema.php';
        bills_schema_boot($connection);
        return true;
    }
}

if (!function_exists('bills_repo_get_shift')) {
    function bills_repo_get_shift() {
        if (function_exists('get_column')) {
            $shift = get_column('kp_shift', 'shift', "1");
            return $shift ? (string)$shift : '';
        }

        return '';
    }
}

if (!function_exists('bills_repo_fetch_unpaid_users')) {
    function bills_repo_fetch_unpaid_users($connection, $shift = '') {
        $shift = trim((string)$shift);
        if ($shift === '') {
            $shift = bills_repo_get_shift();
        }

        if ($shift === '') {
            return [];
        }

        $shiftEsc = mysqli_real_escape_string($connection, $shift);
        $sql = "SELECT s.user_id,
                       COALESCE(u.username, CONCAT('User #', s.user_id)) AS username,
                       COUNT(s.sale_id) AS unpaid_count,
                       SUM(s.amount) AS unpaid_total
                FROM kp_sales s
                LEFT JOIN kp_users u ON u.user_id = s.user_id
                WHERE s.shift = '$shiftEsc'
                  AND s.pay_status = 2
                GROUP BY s.user_id, u.username
                ORDER BY unpaid_total DESC, username ASC";

        $result = mysqli_query($connection, $sql);
        $rows = [];

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = [
                    'user_id' => (int)($row['user_id'] ?? 0),
                    'username' => (string)($row['username'] ?? ''),
                    'unpaid_count' => (int)($row['unpaid_count'] ?? 0),
                    'unpaid_total' => (float)($row['unpaid_total'] ?? 0),
                ];
            }
        }

        return $rows;
    }
}

if (!function_exists('bills_repo_fetch_unpaid_sales')) {
    function bills_repo_fetch_unpaid_sales($connection, $user_id = 0, $shift = '') {
        $user_id = (int)$user_id;
        $shift = trim((string)$shift);
        if ($shift === '') {
            $shift = bills_repo_get_shift();
        }

        if ($shift === '') {
            return [];
        }

        $shiftEsc = mysqli_real_escape_string($connection, $shift);
        $userFilter = $user_id > 0 ? " AND s.user_id = '$user_id'" : '';
        $sql = "SELECT s.sale_id,
                       s.user_id,
                       COALESCE(u.username, CONCAT('User #', s.user_id)) AS username,
                       s.amount,
                       s.shift,
                       s.pay_status,
                       s.bill_status,
                       s.previous_balance,
                       s.current_total,
                       s.current_balance,
                       s.parent,
                       s.cust_id,
                       s.ord_date
                FROM kp_sales s
                LEFT JOIN kp_users u ON u.user_id = s.user_id
                WHERE s.shift = '$shiftEsc'
                  AND s.pay_status = 2{$userFilter}
                ORDER BY s.user_id ASC, s.sale_id ASC";

        $result = mysqli_query($connection, $sql);
        $rows = [];

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = [
                    'sale_id' => (int)($row['sale_id'] ?? 0),
                    'user_id' => (int)($row['user_id'] ?? 0),
                    'username' => (string)($row['username'] ?? ''),
                    'amount' => (float)($row['amount'] ?? 0),
                    'shift' => (string)($row['shift'] ?? ''),
                    'pay_status' => (int)($row['pay_status'] ?? 0),
                    'bill_status' => (int)($row['bill_status'] ?? 0),
                    'previous_balance' => (float)($row['previous_balance'] ?? 0),
                    'current_total' => (float)($row['current_total'] ?? 0),
                    'current_balance' => (float)($row['current_balance'] ?? 0),
                    'parent' => (int)($row['parent'] ?? 0),
                    'cust_id' => (int)($row['cust_id'] ?? 0),
                    'ord_date' => (string)($row['ord_date'] ?? ''),
                ];
            }
        }

        return $rows;
    }
}

if (!function_exists('bills_repo_fetch_unpaid_user')) {
    function bills_repo_fetch_unpaid_user($connection, $user_id = 0, $shift = '') {
        $user_id = (int)$user_id;
        $shift = trim((string)$shift);

        if ($user_id < 1) {
            return null;
        }

        $users = bills_repo_fetch_unpaid_users($connection, $shift);
        $sales = bills_repo_fetch_unpaid_sales($connection, $user_id, $shift);

        $userRow = null;
        foreach ($users as $row) {
            if ((int)($row['user_id'] ?? 0) === $user_id) {
                $userRow = $row;
                break;
            }
        }

        if (!$userRow) {
            $shiftValue = $shift !== '' ? $shift : bills_repo_get_shift();
            $userResult = mysqli_query(
                $connection,
                "SELECT COALESCE(username, CONCAT('User #', user_id)) AS username
                 FROM kp_users
                 WHERE user_id='$user_id'
                 LIMIT 1"
            );
            $userInfo = $userResult ? mysqli_fetch_assoc($userResult) : null;

            $userRow = [
                'user_id' => $user_id,
                'username' => (string)($userInfo['username'] ?? 'User #' . $user_id),
                'unpaid_count' => count($sales),
                'unpaid_total' => array_sum(array_map(function ($row) {
                    return (float)($row['amount'] ?? 0);
                }, $sales)),
            ];

            return [
                'user' => $userRow,
                'sales' => $sales,
                'shift' => $shiftValue,
            ];
        }

        return [
            'user' => $userRow,
            'sales' => $sales,
            'shift' => $shift !== '' ? $shift : bills_repo_get_shift(),
        ];
    }
}
