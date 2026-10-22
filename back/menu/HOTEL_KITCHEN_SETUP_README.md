# Hotel Kitchen Setup Guide

## Purpose

This document explains a recommended setup for using the menu module in a hotel or large kitchen environment where:

- food is often prepared in bulk
- stock control matters
- buffet and a la carte can both exist
- some items are raw materials
- some items are prep items
- some items are final selling items

The goal is to make the system easier to maintain, easier to report on, and more accurate for stock usage in kitchen production.

This is a working guide. We can refine it as we build.

## Main Idea

For a hotel kitchen, the cleanest setup is:

1. `Attributes` for grouping and reporting
2. `Recipe` for ingredient consumption
3. `Portioning` for converting bulk production into servings

These three should not be mixed up.

## What Each Feature Should Do

### Attributes

Attributes should be used for classification only.

They help answer questions like:

- Is this a breakfast item?
- Is this used in buffet?
- Is this from hot kitchen or bakery?
- Is this vegetarian or halal?
- Is this for banquet or room service?

Attributes should not be the thing that controls ingredient deduction.

Recommended attribute uses:

- meal period
- service type
- kitchen section
- dietary tag
- event type

Examples:

- `Breakfast`
- `Lunch`
- `Dinner`
- `Buffet`
- `A la carte`
- `Banquet`
- `Room Service`
- `Hot Kitchen`
- `Cold Kitchen`
- `Bakery`
- `Pastry`
- `Vegetarian`
- `Halal`
- `Spicy`

### Recipe

Recipe should control what ingredients are consumed when an item is produced or sold.

This is the most important stock-control feature for a hotel kitchen.

Recipe is best used at more than one level:

- raw ingredient level
- prep level
- final dish level

Examples of recipe-controlled items:

- soup base
- stew base
- pilau mix
- dough
- cake batter
- marinated chicken
- sauce
- buffet tray
- plated meal

If a kitchen prepares something from ingredients, that item should usually have a recipe.

### Portioning

Portioning should be used when one production batch becomes many servings.

This is ideal for:

- pots
- trays
- pans
- cakes
- buffet dishes
- bulk cooked meats

Examples:

- `Soup Pot` -> `40 bowls`
- `Pilau Pot` -> `25 plates`
- `Cake Whole` -> `16 slices`
- `Lasagna Tray` -> `12 portions`
- `Beef Stew Pot` -> `30 bowls`

Portioning helps the kitchen move from production quantity to sale quantity.

## Recommended Hotel Item Structure

The cleanest structure is to separate items into levels.

### Level 1: Raw Materials

These are the items purchased and stocked.

Examples:

- Rice
- Beef
- Chicken
- Tomatoes
- Onions
- Flour
- Sugar
- Cooking Oil
- Milk
- Eggs
- Spices

These should usually be inventory stock items.

### Level 2: Prep Items

These are intermediate kitchen items made from raw materials.

Examples:

- Pilau Base
- Beef Stew Base
- Tomato Sauce
- Soup Base
- Marinated Chicken
- Cake Batter
- Chapati Dough

These usually need recipes.

This level is important in hotels because a lot of kitchen work happens before the final dish is sold.

### Level 3: Bulk Production Items

These are bigger finished batches prepared in the kitchen.

Examples:

- Pilau Pot
- Beef Stew Pot
- Vegetable Soup Pot
- Buffet Rice Tray
- Lasagna Tray
- Whole Cake

These usually need:

- recipe
- portioning

This is the strongest setup for bulk cooking.

### Level 4: Final Selling Items

These are what the guest actually orders or receives.

Examples:

- Pilau Plate
- Beef Stew Bowl
- Soup Cup
- Cake Slice
- Buffet Serving
- Room Service Breakfast Plate

These may or may not need direct recipe handling depending on how we decide to model the final flow later.

## Recommended Category Layout

A useful category design for a hotel setup is:

- `Raw Materials`
- `Prep Items`
- `Bulk Kitchen Production`
- `Buffet Items`
- `A la carte Items`
- `Bakery Production`
- `Pastry Production`
- `Beverages`
- `Room Service Items`

This keeps reports and kitchen workflows easier to understand.

## Best Practice Flow

The recommended kitchen flow is:

1. Create raw stock items
2. Create prep items with recipes
3. Create bulk production items with recipes
4. Apply portioning to bulk production items
5. Create final selling items for service
6. Use attributes to classify the final and production items

That gives:

- better stock deduction
- clearer production logic
- easier reporting
- easier maintenance later

## Example Setup

### Example 1: Pilau

Raw materials:

- Rice
- Cooking Oil
- Onions
- Spices
- Stock

Prep item:

- Pilau Base

Bulk item:

- Pilau Pot

Final item:

- Pilau Plate

Suggested logic:

- `Pilau Base` has recipe from raw materials
- `Pilau Pot` uses recipe or production structure from base
- `Pilau Pot` has portioning, for example `25 portions`
- `Pilau Plate` is the serving item

### Example 2: Beef Stew

Raw materials:

- Beef
- Tomatoes
- Onions
- Oil
- Spices

Prep item:

- Beef Stew Base

Bulk item:

- Beef Stew Pot

Final item:

- Beef Stew Bowl

Suggested logic:

- `Beef Stew Base` uses recipe
- `Beef Stew Pot` is bulk cooked production
- `Beef Stew Pot` has portioning, for example `30 bowls`
- `Beef Stew Bowl` is the serving item

### Example 3: Soup

Raw materials:

- Bones or vegetables
- Water
- Seasoning
- Cream or milk if needed

Prep item:

- Soup Base

Bulk item:

- Soup Pot

Final item:

- Soup Cup
- Soup Bowl

Suggested logic:

- `Soup Base` has recipe
- `Soup Pot` has portioning
- final servings come from the bulk item

### Example 4: Bakery

Raw materials:

- Flour
- Sugar
- Eggs
- Butter
- Milk

Prep item:

- Cake Batter

Bulk item:

- Whole Cake

Final item:

- Cake Slice

Suggested logic:

- `Cake Batter` has recipe
- `Whole Cake` is the production unit
- `Whole Cake` has portioning, for example `16 slices`
- `Cake Slice` is the selling item

## Attribute Suggestions For Hotels

A good first set of hotel attributes could be:

### Service Type

- Buffet
- A la carte
- Banquet
- Room Service
- Conference

### Meal Period

- Breakfast
- Lunch
- Dinner
- All Day

### Kitchen Section

- Hot Kitchen
- Cold Kitchen
- Bakery
- Pastry
- Butchery

### Dietary / Menu Tags

- Vegetarian
- Vegan
- Halal
- Gluten Free
- Spicy

## What To Avoid

### Do not use attributes instead of recipe

Attributes are for labeling and reporting.
They should not replace ingredient deduction logic.

### Do not only set recipes on final plated items

In hotels, production usually happens in batches first.

If we only recipe final plates and ignore batch production, stock can become inaccurate.

### Do not skip portioning on bulk items

If the kitchen produces in pots, trays, or cakes, portioning is important.

Without it:

- yield tracking becomes weak
- usage accuracy drops
- buffet and bulk production become harder to control

## Recommended Starting Build Plan

When we begin building, a safe first rollout would be:

1. Define categories
2. Define core hotel attributes
3. Create raw material items
4. Create a few prep items
5. Create a few bulk items
6. Add recipes to those bulk items
7. Add portioning to those bulk items
8. Create final selling items
9. Test stock flow with 2 to 3 real kitchen examples

## Good First Test Cases

These are strong starter examples for testing:

### Test Case 1

- Pilau Pot
- Pilau Plate

### Test Case 2

- Beef Stew Pot
- Beef Stew Bowl

### Test Case 3

- Soup Pot
- Soup Cup

### Test Case 4

- Whole Cake
- Cake Slice

These examples are simple, common, and very useful for proving the setup.

## Suggested Decision Direction

If this were my recommendation for a hotel using this system, I would advise:

- use `attributes` only for grouping and reporting
- use `recipe` on prep and production items
- use `portioning` on all batch-produced items
- separate raw, prep, bulk, and selling items clearly

This gives the best long-term maintenance and the best chance of accurate kitchen stock.

## Next Steps Later

Later, we can refine this document by deciding:

- whether final selling items deduct directly or through production items
- how buffet portions should behave
- how banquet production should be tracked
- how bakery yields should be handled
- whether some prep items should be hidden from regular sale
- how kitchen transfers or internal production should be recorded

## Working Note

This README is the starting blueprint only.

We should expect to change:

- names
- category layout
- exact recipe flow
- portion strategy
- reporting tags

as we move forward and test with real hotel workflows.
