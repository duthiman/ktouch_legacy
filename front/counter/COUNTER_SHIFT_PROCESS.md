# Counter and Shift Process

## 1. Opening a counter

A user may open a counter when:

```text
job = 2
drole = 1 or drole = 3
an available counter exists
```

Department mapping:

```text
drole = 1 -> non-kitchen counters
drole = 3 -> kitchen counters where dpt = 2
```

The server also checks that no active shift currently has an open counter:

```sql
counter_status = 1
```

If an open counter exists, opening is rejected and the user is shown the names
of the counters that must be closed.

An opened counter receives:

```text
counter_status = 1
take_status     = 3
close_status    = 3
```

## 2. Closing a counter

Before closing, the system processes closing stock and counter history. It
checks for pending returns, open bills according to the configured rule, stock
closing quantities, counter history, and user shift history.

If another counter is still open, only the selected counter is closed:

```text
counter_status = 2
take_status    = 2
close_status   = 3
```

This means the counter is closed, but the shift remains active because another
counter is still open.

## 3. Closing the shift

After closing the selected counter, the system counts counters where:

```sql
counter_status = 1
```

If the count is exactly `1` before closing, the selected counter is the last
open counter. The system then closes the whole shift.

All counters are set to:

```text
counter_status = 2
take_status    = 2
close_status   = 2
```

All users are reset:

```text
job            = 2
shift_status   = 2
counter        = 0
counter_status = 2
cleared        = 1
```

The day shift is closed:

```text
kp_day_shift.status = 1
```

The closing user and closing order number are recorded, and the active
`kp_shift` record is cleared.

## 4. Status meanings

```text
counter_status = 1 -> counter is open
counter_status = 2 -> counter is closed
```

```text
take_status = 2 -> counter take is closed/ready
take_status = 3 -> counter take is active
```

```text
close_status = 2 -> fully closed and ready
close_status = 3 -> counter/shift process is active or pending final closure
```

## 5. Core rules

```text
Do not open a counter while another counter is open in the active shift.
```

```text
Close the shift only when the counter being closed is the last open counter.
```

```text
Use counter_status to count currently open counters.
```

```text
Use close_status = 2 to confirm all counters are fully closed before a new
opening cycle.
```
