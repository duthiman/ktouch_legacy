# Return Approval Modes

This note captures the intended return approval logic for the `front/ret` module so we can implement it in phases later.

## Goal

The return flow should support multiple operational models depending on how the hotel wants returns controlled.

## Mode 1: Direct Counter Approval

This is the current flow.

Flow:
- Waiter raises return
- Return goes directly to the barman or counter operator
- Barman confirms the physical items
- Stock is accepted back into inventory

Behavior:
- No supervisor step
- Fastest flow
- Best when the outlet trusts the counter team to validate returns directly

## Mode 2: Supervisor Then Counter

This adds an approval checkpoint before stock is returned.

Flow:
- Waiter raises return
- Supervisor reviews and approves or rejects the return
- If approved, return goes to the barman or counter operator
- Barman confirms receipt of items
- Stock is accepted back into inventory

Behavior:
- Supervisor controls whether the return is valid
- Counter still controls physical stock receipt
- Good when management wants tighter return accountability

## Mode 3: Returning Officer Flow

This introduces a dedicated return receiving role.

Flow:
- Waiter raises return
- Return is sent to a returning officer
- Returning officer receives all returned alcohol or controlled stock
- Returning officer confirms receipt
- Stock is accepted back into inventory

Optional variant:
- Waiter raises return
- Supervisor first approves
- Then return goes to the returning officer
- Returning officer receives and confirms the stock

Behavior:
- Best for centralized control of alcohol returns
- Useful where the hotel wants one accountable desk or officer for all returns
- Can work with or without supervisor approval

## Proposed Configuration Model

We will likely need a return settings structure that can support:

- `approval_mode`
- `requires_supervisor_approval`
- `final_receiving_role`

Suggested meanings:

- `approval_mode = 1`
  - Direct to counter or barman
- `approval_mode = 2`
  - Supervisor approval, then counter or barman
- `approval_mode = 3`
  - Returning officer receives the stock

Additional flag:

- `requires_supervisor_approval = 1`
  - Used especially with mode 3 when supervisor must approve before the returning officer

## Roles We Need To Support

- Waiter
- Supervisor
- Barman or counter operator
- Returning officer

## Important Design Rule

Approval and physical stock receipt should be treated as separate actions where needed.

Examples:
- A supervisor can approve validity without touching stock
- A barman can receive stock after approval
- A returning officer can receive stock centrally instead of the barman

## Future Build Notes

When we implement this, we should make sure:

- each stage has its own status field or workflow state
- the UI clearly shows who should act next
- rejection is possible at each approval stage where business rules allow
- logs clearly show who raised, approved, rejected, and received the return
- stock only moves back when the final receiving step is completed

## Current Status

Currently implemented logic is closest to:

- direct counter approval
- optional cashier or supervisor-like intermediate approval before counter confirmation

This file is the reference for expanding that logic into the full 3-mode workflow later.
