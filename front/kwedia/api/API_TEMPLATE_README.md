# API Template Guide

This file explains how the `front/API/kwedia/bill_api.php` style API is built so you can reuse the same pattern for other modules.

Use this as a template for modules like:

- returns
- cashier
- stock
- customers
- reports
- any frontend AJAX module

## Goal of this pattern

The main idea is to separate the API into small layers:

1. `*_api.php`
   Entry point and action router

2. `*_service.php`
   Business logic and workflow decisions

3. `*_repository.php`
   Database reads/writes only

4. `*_helpers.php`
   Small reusable helpers

5. optional `*_logger.php`
   Logging and tracing

6. optional `*_schema.php` or schema helpers
   Table/column safety checks if the module creates or evolves tables

This keeps the API easy to grow without putting everything in one file.

## Recommended folder structure

For a module named `example`, use this shape:

```text
front/API/example/
  example_api.php
  example_service.php
  example_repository.php
  example_helpers.php
  example_logger.php
  funcs.php
  API_TEMPLATE_README.md
```

For the current bill module, the real version is:

```text
front/API/kwedia/
  bill_api.php
  bill_service.php
  bill_repository.php
  bill_helpers.php
  bill_logger.php
  funcs.php
```

## What each file should do

### `*_api.php`

This is the controller layer.

Responsibilities:

- start session
- set response headers
- include required files
- read `$_GET`, `$_POST`, or `$_REQUEST`
- detect `action`
- validate session once
- call one service function per action
- return JSON response

This file should **not** contain:

- raw SQL
- large workflows
- repeated validation logic
- HTML rendering

### `*_service.php`

This is the workflow layer.

Responsibilities:

- validate business rules
- combine multiple repository calls
- map module scenarios
- prepare normalized response data
- throw or return business errors

Examples from the bill module:

- get live session context
- add item
- get categories
- get cart
- open or close order
- manage open-bill scenarios

This file should **not** contain:

- big inline SQL queries
- direct `echo json_encode(...)`

### `*_repository.php`

This is the data access layer.

Responsibilities:

- `SELECT`
- `INSERT`
- `UPDATE`
- `DELETE`
- row fetch helpers
- aggregate helpers

This file should be the main place for database access.

Good repository functions are shaped like:

- `repo_get_item($item_id)`
- `repo_get_shift()`
- `repo_insert_sale($data)`
- `repo_fetch_active_counters()`

Keep the names predictable.

### `*_helpers.php`

This is for small shared utilities.

Examples:

- JSON response helper
- session checker
- input cleaner
- status row normalizer
- enum cleanup functions

Good helper functions are:

- small
- stateless
- reusable

### `*_logger.php`

Use this when the module is complex enough to need event tracing.

Good uses:

- API request tracing
- workflow checkpoints
- stock movement debug logs
- print queue debug logs

## Request flow

This is the expected flow for the API:

```text
Browser JS
  -> example_api.php
    -> example_helpers.php session check
    -> switch(action)
    -> example_service.php
      -> example_repository.php
    -> JSON response
```

For this bill module, the flow is:

```text
front/kwedia/js.js
  -> front/API/kwedia/bill_api.php
    -> bill_helpers.php
    -> bill_service.php
      -> bill_repository.php
    -> JSON
```

## How to build a new module

### Step 1: create the API entry file

Example:

```php
<?php
session_start();

header('Content-Type: application/json');

include('../../../inc/connection.php');
require_once('example_helpers.php');
require_once('example_repository.php');
require_once('example_service.php');

$action = trim((string)($_REQUEST['action'] ?? ''));
$session = example_check_session();

if (!$session['ok']) {
    example_json_response(false, $session['message'], ['user_id' => 0], 401);
}

$user_id = (int)$session['user_id'];

switch ($action) {
    case '':
        example_json_response(true, 'Example API is ready.', []);
        break;

    case 'load_items':
        $result = example_service_load_items($user_id);
        example_json_response($result['ok'], $result['message'], [
            'items' => $result['items'] ?? [],
        ], $result['ok'] ? 200 : 400);
        break;

    default:
        example_json_response(false, 'Unknown action.', ['action' => $action], 400);
        break;
}
```

## Step 2: create the helper file

Example:

```php
<?php

if (!function_exists('example_json_response')) {
    function example_json_response($ok, $message, $data = [], $status_code = 200) {
        http_response_code((int)$status_code);
        echo json_encode([
            'ok' => (bool)$ok,
            'message' => (string)$message,
            'data' => is_array($data) ? $data : [],
        ]);
        exit;
    }
}

if (!function_exists('example_check_session')) {
    function example_check_session() {
        $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($user_id < 1) {
            return [
                'ok' => false,
                'message' => 'Session expired. Please login again.',
                'user_id' => 0,
            ];
        }

        return [
            'ok' => true,
            'message' => 'Session is active.',
            'user_id' => $user_id,
        ];
    }
}
```

## Step 3: create the repository file

Example:

```php
<?php

if (!function_exists('example_repo_get_items')) {
    function example_repo_get_items($user_id) {
        global $connection;

        $user_id = (int)$user_id;
        $rows = [];
        $result = mysqli_query($connection, "SELECT id, name FROM kp_example ORDER BY id ASC");

        if (!$result) {
            return $rows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'id' => (int)$row['id'],
                'name' => (string)$row['name'],
            ];
        }

        return $rows;
    }
}
```

## Step 4: create the service file

Example:

```php
<?php

if (!function_exists('example_service_load_items')) {
    function example_service_load_items($user_id) {
        $user_id = (int)$user_id;

        if ($user_id < 1) {
            return [
                'ok' => false,
                'message' => 'Session expired.',
                'items' => [],
            ];
        }

        return [
            'ok' => true,
            'message' => 'Items loaded.',
            'items' => example_repo_get_items($user_id),
        ];
    }
}
```

## Naming convention

Use one prefix per module.

Examples:

- `bill_*` for billing
- `ret_*` for returns
- `cash_*` for cashier
- `cust_*` for customers

Recommended pattern:

- API/controller: `module_api.php`
- service functions: `module_service_*`
- repository functions: `module_repo_*` or `repo_*` if fully isolated
- helper functions: `module_*`

For new modules, prefer a clear prefix so function ownership stays obvious.

## Action design

Each frontend action should map to one clear service function.

Good examples:

- `get_items`
- `get_categories`
- `load_page`
- `load_modal`
- `create_order`
- `update_item`
- `remove_item`
- `cancel_order`

Avoid vague actions like:

- `do_it`
- `save_data`
- `process`

## Response shape

Keep the same response contract everywhere:

```json
{
  "ok": true,
  "message": "Items loaded.",
  "data": {
    "items": []
  }
}
```

Recommended rules:

- `ok` = boolean
- `message` = human-readable status
- `data` = array/object payload

This makes frontend code easier to reuse.

## Error handling

Use these rules:

- session errors -> `401`
- validation errors -> `400`
- unknown action -> `400`
- success -> `200`

In services:

- return structured arrays for expected failures
- use `RuntimeException` only for deeper workflow failures when needed

## What to copy from `bill_api.php`

The important reusable ideas from `bill_api.php` are:

1. one API file that only routes actions
2. one session check at the top
3. one service call per action
4. one JSON response format
5. business workflows moved out of the controller
6. repository functions separated from workflow functions

That is the main template to copy.

## Recommended build checklist

When creating a new API module:

- create `module_api.php`
- create `module_helpers.php`
- create `module_service.php`
- create `module_repository.php`
- define one session check helper
- define one JSON response helper
- define the action switch
- keep SQL in repository only
- keep workflows in service only
- return normalized arrays from service
- keep frontend JS talking only to the API file

## Practical example

If you were creating a customer API, the result could look like:

```text
front/API/customer/
  customer_api.php
  customer_helpers.php
  customer_service.php
  customer_repository.php
```

With actions like:

- `search_customers`
- `load_customer`
- `save_customer`
- `load_customer_sales`

And the frontend page would call only:

```text
front/API/customer/customer_api.php?action=search_customers
```

## Final advice

If you want to reuse this template well, keep these boundaries strict:

- API file = routing
- service file = business decisions
- repository file = database work
- helpers file = shared utilities

That is what makes the bill API reusable as a pattern instead of becoming another large mixed PHP file.
