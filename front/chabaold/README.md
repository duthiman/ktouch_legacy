## `front/chaba/index.php`

This page is a print worker dashboard for the K-TOUCH POS system. It is meant to stay open on a machine that handles printing for one counter, or in QZ Tray mode, for all mapped printers.

### What it does

- Starts a PHP session and loads the database connection, shared helper functions, and QZ Tray support.
- Detects the print mode from system settings, with an optional `mode_override` query parameter.
- Changes its title and behavior depending on the mode:
  - Standard browser printing
  - Browser tab worker mode
  - QZ Tray worker mode
- Loads saved thermal-fit settings such as paper width, margin, content scale, and divider style.

### How the page works

1. When the page opens, the user selects a counter / printer station.
2. After starting, the page begins polling the shared print queue every 3 seconds.
3. It fetches pending jobs from `../../gen/print_api.php`.
4. For each job, it decides which print template URL to load based on the job type.
5. It prints the job in one of two ways:
   - Browser mode: loads the print page into a hidden iframe and calls `window.print()`.
   - QZ Tray mode: loads the HTML, applies thermal-fit rules, and sends the job directly to the mapped Windows printer through QZ Tray.
6. It updates the on-screen log and status as each job is processed.

### Extra tools built into the page

- A live status indicator showing whether the worker is idle, busy, or in error.
- A “Change Counter” action to stop polling and pick another printer station.
- QZ security bootstrapping using certificate and signature endpoints from `../../qz/security.php`.
- A print-fit settings modal where the operator can save:
  - paper width
  - margin
  - scale
  - receipt divider symbol
  - receipt divider count
- A preview/test-print tool that can open a print template, preview it in an iframe, and send a test page to a selected mapped printer.
- A warning if the user tries to close the tab while the worker is active.

### Related files in this folder

- `render_bot.php`
  Renders a BOT (Bar Order Ticket) print page for a sale. It pulls ticket payload data from the Chaba service layer and passes that data into the shared ticket renderer.

- `render_ret.php`
  Renders a return receipt for a sale. It loads sale data, company print settings, receipt styling, return items, totals, and optionally triggers browser printing with `auto_print=1`.

### In simple terms

This folder supports the POS printing flow. `index.php` is the worker/control page, while files like `render_bot.php` and `render_ret.php` are actual printable templates that the worker opens and prints.
