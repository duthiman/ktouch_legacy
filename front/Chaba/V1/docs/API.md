# Chaba print queue

Send authenticated requests to the existing API with `"module": "chaba"` (no trailing slash). Module files live in `modules/Chaba/V1/`.

## Actions

| Action | Data | Result |
| --- | --- | --- |
| `schema.ensure` | `{}` | Schema success, or an API error on failure |
| `enqueue` | Metadata and complete `payload` object or `payload_json` string | `queue_id`, pending status |
| `list` | Optional `status` (default 0), `cntr_id` | Latest 100 matching job headers; status 1 reads archive |
| `get` | `queue_id` | Job and decoded document, active or archived |
| `render` | `queue_id` | Job, document, and escaped standalone HTML |
| `claim` | Optional `cntr_id` | Oldest pending `job`, or null |
| `complete` | `queue_id`, `print_attempts` from claim | Transactionally archived successful job |
| `fail` | `queue_id`, `print_attempts`, `last_error` | Failed job retained in active queue |
| `retry` | `queue_id` | Failed job returned to pending |
| `reprint` | `source_queue_id` from successful archive | New pending job and reprint audit |

Example enqueue:

```json
{
  "module": "chaba",
  "action": "enqueue",
  "data": {
    "sale_id": 7387,
    "ref_id": 7387,
    "print_type": "BOT",
    "label": "Bar order",
    "cntr_id": 2,
    "shift": "2026-09-14",
    "payload": {
      "header": {"user_id": 26},
      "items": [{"item_id": 14, "name": "Tea", "qty": 2, "price": 350,
                 "department": 1, "counter": 2, "stock_counter": 2, "attributes": []}],
      "users": [],
      "payments": [],
      "payload_version": 1
    }
  }
}
```

The server derives `requested_by` from authentication, overwrites queue identity fields in the header, and calculates each item total and `summary.item_count` (sum of quantities) / `summary.amount`. Include printable names and all required details at enqueue time. Summary documents without item lines must provide complete summary content with numeric `summary.item_count` and `summary.amount`. Extra document sections are preserved for RET, REQ, KUG, staff/shift summaries and variance documents. Invalid JSON, empty/reference-only documents and unsupported versions are rejected.

`route_path` and `route_query` are optional metadata. Chaba does not execute routes or fetch URLs. The generic HTML renderer includes the document's sections; a printer-specific renderer can use the returned frozen `document` instead. This API queues documents and acknowledges results; a separate printer worker must send them to physical printers. It does not automatically enqueue jobs for Sales actions.

## Lifecycle and reliability

Statuses: 0 pending, 1 successfully printed, 2 permanently failed until manually retried, 3 claimed/printing. Claim increments attempts. Complete/fail must return that exact attempt number. A stale worker cannot complete or fail a later retry. Attempts are limited to 255 by the required schema.

Completion updates status, inserts the archive, then deletes the active row in one transaction. Conflicts roll back without losing the active job. Repeated acknowledgement of the same successful attempt is accepted. The archive receives only successful jobs through this API. Reprints copy the exact snapshot bytes, store the new requester on the queue row, and record `kp_print_reprints` atomically; the original snapshot header remains unchanged.

Pending and failed rows are independent of shift close and remain available afterward. Chaba does not automatically expire claims: if a worker crashes, an authorized operator can mark the known attempt failed and retry after checking printer output. Physical printing and the database acknowledgement are separate operations, so a lost acknowledgement may require operator review to avoid duplicate paper output.

Existing `kp_print_ticket_items` is left untouched. Only jobs with empty snapshots use legacy ticket items by `batch_id = queue_id`, with inventory names as a compatibility fallback. Missing legacy data yields a clear error. A legacy job without a snapshot cannot be reprinted through the snapshot-copy action; enqueue a complete document first.

## Schema and permissions

Every Chaba request checks/heals the queue, archive and reprint audit schema under a database-scoped advisory lock. Missing columns/indexes are added without dropping or renaming existing fields. Existing column types are not silently changed. Failed CREATE/ALTER/index operations are recorded in PHP's error log and application `chaba_schema_error` records; the API fails clearly instead of processing a job on incomplete schema. Explicit archive column lists support independently healed column order.

Chaba actions are public and do not require a JWT because Chaba is used by the print worker. Protect the endpoint at the network or reverse-proxy level if public internet access is possible. With automatic schema migration disabled, install `Chaba\V1\Database\Permissions::migrate` through your deployment process before using the API. Queue schema healing itself intentionally runs on every request regardless of Sales/Shift schema settings.

## Verification

Run `php tests/Chaba/run.php`. Tests use connection-local temporary MySQL tables and preserve application records. Coverage includes partial-schema repair, repeated healing, snapshot validation/calculation/escaping, routing, claim state, stale attempts, rollback on archive failure, completion acknowledgement, reprint audit, and repair after an injected DDL failure. The injected failure intentionally prints one schema error to the test output.
