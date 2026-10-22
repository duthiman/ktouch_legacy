# Chaba Module Doc

## What this module is

`front/chaba` is the POS printing module for K-TOUCH. It handles queued print jobs, printable templates, and the worker UI that keeps a printer station alive.

## Main flow

1. `index.php` starts the print worker UI.
2. The worker picks a counter or printer station.
3. The page polls the shared print queue.
4. Each queued job is mapped to a render page such as `render_bill.php`, `render_kot.php`, or `render_got.php`.
5. The render page loads its payload from the API/service layer.
6. The shared renderer builds the printable HTML.
7. The browser prints the page, or QZ Tray sends it directly to the mapped printer.

## File roles

- `index.php` - worker dashboard and print loop.
- `browser_tab_worker.php` - compatibility entry that reuses `index.php`.
- `funcs.php` - module bootstrap and schema setup.
- `api/chaba_api.php` - JSON router for render and payload endpoints.
- `api/chaba_helpers.php` - shared validation and URL helpers.
- `api/chaba_schema.php` - table and column setup.
- `api/chaba_repository.php` - SQL reads for sales, tickets, company, and print settings.
- `api/chaba_service.php` - business logic that assembles payloads.
- `render_ticket_view.php` - shared layout for ticket-style documents.
- `render_bill.php` - bill template.
- `render_bot.php` - bar order ticket template.
- `render_kot.php` - kitchen order ticket template.
- `render_got.php` - grill order ticket template.
- `render_ret.php` - return receipt template.
- `render_cashier_order.php` - cashier order template.
- `render_req.php`, `render_tots.php`, `render_shift_sum.php`, `render_staff_summary.php`, `render_counter_sum.php`, `render_variance.php`, `render_trans.php`, `render_riko.php` - other printable views used by the worker.

## Data layer

The module does not keep print data inside the views. It uses this chain:

`render page` -> `api/chaba_service.php` -> `api/chaba_repository.php` -> MySQL

That separation is what makes the module reusable.

## Print modes

- Browser mode - opens the render page in a hidden iframe and calls `window.print()`.
- Browser tab worker mode - uses the same page flow, but the tab is treated as the worker.
- QZ Tray mode - connects to QZ Tray and prints directly to the mapped Windows printer.

## Tables this module expects

Typical tables and settings used by the module include:

- `kp_sales`
- `kp_sale_items`
- `kp_sale_print`
- `kp_print_queue`
- `kp_print_ticket_items`
- `kp_settings_print`
- `kp_settings_gen`
- `kp_counters`

The schema helper creates or repairs the print queue and related columns when needed.

## How to build another module like this

1. Create a new folder with the same shape: worker page, `api/`, render pages, and a shared view file.
2. Put database reads in repository functions, not in the render templates.
3. Put assembly logic in service functions so templates stay thin.
4. Add a single JSON router that exposes the payloads your renders need.
5. Keep the worker page small: select station, poll jobs, render, print, repeat.
6. If the new module needs multiple document types, map each type to one render file and one service payload builder.
7. Add schema bootstrap code so the module can repair missing tables or columns on startup.

## Recommended clone pattern

Copy this structure and rename it for the new module:

- `front/<module>/index.php`
- `front/<module>/funcs.php`
- `front/<module>/api/`
- `front/<module>/render_*.php`
- `front/<module>/render_view.php`

## Practical rule

If you are adding a new print type, change the service and repository first, then add the render file last. If you are adding a new module, start with the worker flow and the data layer before styling the page.