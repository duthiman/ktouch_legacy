# Front Folder

The `front/` folder is the browser-facing operational side of the system.

This is where staff log in, land on the dashboard, start or continue orders, clear bills, process returns, work with counters, and view day-to-day sales activity.

In short:

- `front/` contains screens
- `front/API/` contains AJAX/JSON endpoints used by those screens
- feature folders contain business workflows for a specific area
- shared root files handle login, session state, and reusable frontend helpers

## What the front flow looks like

The core flow is easier to understand if we follow the entry point:

1. `front/index.php`
   Shows the staff login page with the keypad UI.

2. `front/kuingira.php`
   Receives the passcode via `$.post(...)`, checks the user in `kp_users`, opens the personal shift if needed, checks whether the main shift is open, checks whether counters are ready, and decides where the user should go next.

3. `front/funcs.php`
   Provides the shared helper logic used during login and routing, including:
   - session setup
   - main shift lookup
   - personal shift state
   - open-counter checks
   - counter stock-take checks
   - role-based counter-opening permissions
   - front workstation/session (`thesion`) initialization

4. `front/dash/dash.php` + `front/dash/dash.js`
   Loads the landing dashboard shell, then uses AJAX to build the real dashboard from APIs and HTML templates.

5. From the dashboard, users branch into workflows such as:
   - place order
   - continue bill
   - view orders
   - returns
   - clear bill
   - stock
   - open or close counters

6. Order/bill screens such as `front/kwedia/index.php`
   Use the current front session state (`kp_thesion_front`, open-sale state, counter state) to determine which bill/counter the user is actively working on.

7. AJAX APIs such as `front/API/kwedia/bill_api.php`
   Receive action names like `initialise`, `add_item`, `get_categories`, `open_order`, `close_order`, and route them into service/repository/helper files.

## Important root files

### `front/index.php`

Frontend login page.

What it does:

- renders the keypad/passcode login screen
- posts the passcode to `kuingira.php`
- displays server response messages inside the page

### `front/kuingira.php`

This is the real login gate and routing decision file.

Main logic:

- checks whether the passcode is empty
- looks up the user by PIN
- stores `user_id`
- updates user login status
- checks whether the role can open counters
- checks whether the waiter should be blocked until counters are ready
- checks whether the main shift is open
- checks whether the user's own shift is open
- decides whether the user should:
  - open their shift first
  - go to `dash/dash.php`
  - go to `take/init_take.php`
  - wait for counters or stock take to finish

This file is the main "traffic controller" for front-office access.

### `front/funcs.php`

Shared frontend operational helpers.

Important functions here include:

- `session(...)`
- `main_shift()`
- `user_shift(...)`
- `open_user_shift(...)`
- `open_counters()`
- `counter_take()`
- `front_role_can_open_counters(...)`
- `front_role_pending_counter_count(...)`
- `front_waiter_blocked_by_counter_setup()`
- `thesion_init(...)`

This file captures a lot of the system's front-office rules around who can work, when they can work, and whether counters are ready.

### `front/session_status.php`

Very small JSON endpoint used to confirm whether a frontend user session is still active.

Useful for:

- session heartbeat checks
- auto-logout or stale session detection
- AJAX-driven screens that need to know if the user is still authenticated

## Main folders and what they do

The exact folder list is business-heavy, but the folders generally follow a "workflow by feature" pattern.

### `front/API/`

AJAX and JSON endpoints for the frontend.

This is where browser pages call into PHP without doing full page reloads.

One confirmed example is `front/API/kwedia/bill_api.php`, which acts as an action router for bill/order activity.

Its current responsibilities include:

- session validation
- reading request `action`
- calling service-layer functions
- returning JSON responses

Confirmed bill actions include:

- `initialise`
- `add_item`
- `get_items`
- `get_categories`
- `get_subcategories`
- `get_order_details`
- `get_current_cart`
- `get_scenario`
- `get_live_context`
- `load_submit_confirm_modal`
- `load_open_bill_mode_modal`
- `load_open_bill_modal`
- `load_open_continue_type_modal`
- `load_open_counter_modal`
- `load_open_identifier_modal`
- `save_open_identifier`
- `load_open_sales_list`
- `load_open_page`
- `load_counter_print_modal`
- `open_order`
- `close_order`
- `delete_item`
- `cancel_order`

That tells us the frontend is moving toward a clearer controller/service/repository split in at least part of the system.

### `front/assets/`

Static frontend assets.

Usually contains:

- CSS
- JavaScript
- images
- fonts
- vendor plugins

This folder should stay mostly presentation-focused, not business-logic focused.

### `front/dash/`

The dashboard or landing area after login.

Confirmed behavior from `dash.php` and `dash.js`:

- loads a lightweight HTML shell first
- fetches dashboard data from `../API/dash/dash.php`
- fetches HTML templates from `front/dash/templates/`
- renders different actions depending on user/shift/counter state
- opens modals for:
  - open counter
  - counter select
  - change password
  - close counter
  - merge request review

This folder is the operational hub that routes users into the rest of the front-office system.

### `front/kwedia/`

Main order-taking / billing workspace.

Confirmed behavior from `kwedia/index.php`:

- requires an authenticated user session
- restores or resumes the active sale context when `sale_id` is provided
- reads active sale/counter/status from `kp_thesion_front`
- redirects back to dashboard if no active order session exists
- renders category/menu/order panels
- supports both desktop and mobile layouts
- lets the user:
  - refresh
  - toggle ordering mode
  - view current cart/order
  - cancel order
  - open order mode
  - close order

This is one of the most important business folders in `front/`.

### `front/open/`

Open-bill and bill continuation workflows.

Confirmed example: `front/open/counter_modal.php`

What that modal shows about the folder's purpose:

- loads live open-bill counter choices
- shows bill-family context
- handles parent/child bill relationships
- supports fresh-open versus continuation flows
- allows continuing a bill from a selected counter
- can jump into bill closing flow when needed

This suggests `front/open/` is responsible for "resume or continue an existing bill" workflows, especially when family/grouped bills exist.

### `front/view/`

Viewing historical or active sales/bills.

The dashboard links directly to `../view/my_sales.php`, which strongly suggests this folder is for:

- order history
- bill inspection
- already-saved sales views
- related actions such as returns, voids, or merge handling

`dash.js` also loads merge-request modals from this area, so `view/` is not only read-only display; it also seems to host follow-up actions tied to viewed sales.

### `front/counter/`

Counter selection and counter state workflows.

The dashboard opens modals from:

- `../counter/open.php`
- `../counter/counter_select.php`
- `../counter/close_modal.php`

That means this folder likely handles:

- opening counters
- selecting a counter before starting an order
- validating whether a counter can be used
- closing counters at end-of-shift or end-of-session

### `front/cash/`

Cashier bill-clearing area.

The dashboard routes managers/cashiers here through `../cash/`.

Likely responsibilities:

- clearing or settling bills
- receiving payment
- handling paid/unpaid bill states
- cashier-specific summaries or bill completion actions

### `front/ret/`

Returns workflow.

The dashboard links to `../ret/appr/returns.php`, so this area likely handles:

- initiating returns
- listing return items
- approving/rejecting return actions
- possibly return printing or audit steps

### `front/stock/`

Stock-facing operational pages.

The dashboard routes managers to `../stock/store_dash.php`, which suggests:

- stock dashboard views
- stock movement or usage views
- store/counter stock workflows

### `front/take/`

Stock take / initialization flow.

This folder is directly referenced from login routing:

- users can be redirected to `take/init_take.php`

That means `take/` is part of the operational readiness workflow, especially when counters need stock-take action before normal selling can continue.

### Other feature folders

The remaining folders in `front/` appear to follow the same pattern: each one represents a business module, department, or workflow slice.

Examples from the current structure draft include:

- `chaba/`
- `claims/`
- `cust/`
- `kpesa/`
- `kit/`
- `kug/`
- `rec/`
- `riko/`
- `shift/`
- `split/`
- `staff/`
- `tot/`
- `trans/`
- `gwitia/`

Without reading each module individually, the safest interpretation is:

- each folder owns a distinct operational workflow
- pages inside that folder usually render UI plus call local or shared APIs
- cross-cutting state is often stored in session plus sales/counter tables in the database

## The main logic pattern used in `front/`

Across the confirmed files, the architecture looks like this:

1. Page shell in PHP
   A page like `index.php`, `dash.php`, or `kwedia/index.php` renders the main HTML frame.

2. Browser-side JS loads or drives state
   jQuery is heavily used for `$.post`, `$.ajax`, `$.getJSON`, modal loading, and live UI refresh.

3. API endpoint receives action
   Files in `front/API/...` read `action` values from request data.

4. Service/helper layer does business work
   Example: `bill_api.php` delegates to helper/service/repository files instead of placing everything inline.

5. Database-backed operational state decides the flow
   Repeated concepts include:
   - user session state
   - personal shift state
   - main shift state
   - counter open/take status
   - current sale ID
   - active counter ID
   - current front workstation state (`kp_thesion_front`)
   - open bill / parent-child bill relationships

6. Frontend redirects or updates the modal/page
   Depending on outcome, the UI either:
   - redirects to another page
   - updates a modal
   - shows an alert
   - reloads the current workflow

## Practical mental model

If you need to reason about the `front/` folder quickly, this mental model is useful:

- `front/index.php` = login screen
- `front/kuingira.php` = login decision engine
- `front/dash/` = operational landing hub
- `front/counter/` = choose/open/close counter
- `front/kwedia/` = active order-taking workspace
- `front/open/` = continue existing/open bills
- `front/view/` = inspect sales and related follow-up actions
- `front/cash/` = cashier bill clearing
- `front/ret/` = returns
- `front/take/` = stock-take/readiness flow
- `front/API/` = AJAX layer behind those screens

## Short summary

`front/` is not just "the UI folder".

It is the live operational application for staff:

- authenticate staff
- confirm shift and counter readiness
- route people into the correct work area
- manage the active bill/session context
- let users place, continue, close, view, and clear bills
- support returns, counters, and stock-related operational steps

The most important logic theme in this folder is operational state management: user, shift, counter, and sale context determine what a staff member is allowed to do next.
