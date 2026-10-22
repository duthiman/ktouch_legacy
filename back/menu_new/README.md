# Menu New Workspace

This folder is the isolated workspace for rebuilding menu and raw-material handling without touching the live `gpints/back/menu` module.

## Agreed Direction

- Support multiple item control methods instead of one stock model for all items.
- Keep the current live module untouched while the new logic is designed here.
- Build around hotel restaurant and bar realities.

## Planned Control Methods

1. Direct Recipe
2. Batch Produced
3. Bottle / Tot Control
4. Portioned Store Item
5. Simple Store Issue
6. Direct Sell Item

## Planned Business Areas

1. Sellable Items
2. Purchasable / Raw Materials
3. Batch Items
4. Bottle / Tot Items
5. Attributes
6. Units / Conversions
7. Pricing Rules

## Immediate Next Decision

Agree the official:

- item types
- control methods
- unit model
- pricing behavior
- which fields are required per type

## Agreed Operational Dynamics

1. Some items will use direct recipes.
2. Some alcohol items will use bottle / shot logic with ml and bottle weights.
3. Some foods will be batch-produced and pushed to POS as portions.
4. Some bulk store items will use purchase-to-stock conversions.
5. Some kitchen consumables will only be issued from store without strict recipe tracking.
6. We must support both sellable items and purchasable items.
7. Attributes may affect kitchen instructions and may also affect price.
8. Some items must allow controlled price changes at sale time.
