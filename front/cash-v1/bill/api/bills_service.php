<?php

if (!function_exists('bills_service_boot')) {
    function bills_service_boot($connection) {
        require_once __DIR__ . '/bills_helpers.php';
        require_once __DIR__ . '/bills_logger.php';
        require_once __DIR__ . '/bills_repository.php';

        bills_repository_boot($connection);
        return true;
    }
}

if (!function_exists('bills_service_get_unpaid_users')) {
    function bills_service_get_unpaid_users($connection, $user_id = 0, $shift = '') {
        $user_id = (int)$user_id;
        $shift = trim((string)$shift);

        if ($shift === '' && function_exists('bills_repo_get_shift')) {
            $shift = bills_repo_get_shift();
        }

        $users = bills_repo_fetch_unpaid_users($connection, $shift);
        $sales = bills_repo_fetch_unpaid_sales($connection, 0, $shift);

        $userLookup = [];
        foreach ($users as $row) {
            $userLookup[(int)$row['user_id']] = $row;
        }

        $detailedUsers = [];
        foreach ($sales as $sale) {
            $saleUserId = (int)($sale['user_id'] ?? 0);
            if (!isset($detailedUsers[$saleUserId])) {
                $source = $userLookup[$saleUserId] ?? [
                    'user_id' => $saleUserId,
                    'username' => (string)($sale['username'] ?? ''),
                    'unpaid_count' => 0,
                    'unpaid_total' => 0,
                ];

                $detailedUsers[$saleUserId] = [
                    'user_id' => (int)($source['user_id'] ?? 0),
                    'username' => (string)($source['username'] ?? ''),
                    'unpaid_count' => (int)($source['unpaid_count'] ?? 0),
                    'unpaid_total' => (float)($source['unpaid_total'] ?? 0),
                    'sales' => [],
                ];
            }

            $detailedUsers[$saleUserId]['sales'][] = $sale;
        }

        $usersList = array_values($detailedUsers ?: array_map(function ($row) {
            return [
                'user_id' => (int)($row['user_id'] ?? 0),
                'username' => (string)($row['username'] ?? ''),
                'unpaid_count' => (int)($row['unpaid_count'] ?? 0),
                'unpaid_total' => (float)($row['unpaid_total'] ?? 0),
                'sales' => [],
            ];
        }, $users));

        $summary = [
            'shift' => $shift,
            'users_count' => count($users),
            'sales_count' => count($sales),
            'total_unpaid' => array_sum(array_map(function ($row) {
                return (float)($row['unpaid_total'] ?? 0);
            }, $users)),
        ];

        return [
            'ok' => true,
            'message' => 'Unpaid users loaded.',
            'shift' => $shift,
            'summary' => $summary,
            'users' => $usersList,
            'unpaid_sales' => $sales,
        ];
    }
}

if (!function_exists('bills_service_get_unpaid_user')) {
    function bills_service_get_unpaid_user($connection, $user_id = 0, $shift = '', $target_user_id = 0) {
        $user_id = (int)$user_id;
        $target_user_id = (int)$target_user_id;
        $shift = trim((string)$shift);

        if ($target_user_id < 1) {
            return [
                'ok' => false,
                'message' => 'Invalid user selected.',
                'user' => null,
                'sales' => [],
                'shift' => $shift,
            ];
        }

        $result = bills_repo_fetch_unpaid_user($connection, $target_user_id, $shift);
        if (!$result) {
            return [
                'ok' => false,
                'message' => 'No unpaid bills found for the selected user.',
                'user' => null,
                'sales' => [],
                'shift' => $shift,
            ];
        }

        return [
            'ok' => true,
            'message' => 'Unpaid bills loaded.',
            'user' => $result['user'] ?? null,
            'sales' => $result['sales'] ?? [],
            'shift' => $result['shift'] ?? $shift,
        ];
    }
}
