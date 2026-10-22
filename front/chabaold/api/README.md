# Front Chaba API

This is the active Chaba render API location.

Use this folder for:

- render data APIs
- ticket item loading APIs
- bill payload APIs
- shared Chaba schema checks
- repository and service logic for render pages

## Files

- `chaba_api.php`
- `chaba_helpers.php`
- `chaba_schema.php`
- `chaba_repository.php`
- `chaba_service.php`

## Example actions

- `front/chaba/api/chaba_api.php?action=ping`
- `front/chaba/api/chaba_api.php?action=sale_context&sale_id=12`
- `front/chaba/api/chaba_api.php?action=bill_payload&sale_id=12`
- `front/chaba/api/chaba_api.php?action=ticket_payload&sale_id=12&ticket_type=kot`

## Goal

The render pages in `front/chaba/` should gradually stop owning direct SQL and instead use this API/service layer.
